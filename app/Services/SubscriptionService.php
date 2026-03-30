<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Notifications\SubscriptionActivatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function __construct(
        private readonly PayPalService $payPal
    ) {}

    // ──────────────────────────────────────────────────────────────────────
    // CHECKOUT INITIATION
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Create a pending Subscription record, call PayPal, return the
     * approval URL to redirect the vendor to.
     */
    public function initiateCheckout(Vendor $vendor, SubscriptionPlan $plan): string
    {
        $returnUrl    = route('vendor.subscription.success');
        $cancelUrl    = route('vendor.subscription.cancel');

        // Create pending record BEFORE calling PayPal so we have an ID
        $subscription = Subscription::create([
            'vendor_id'            => $vendor->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'pending',
            'currency'             => $plan->currency,
        ]);

        $returnWithId = $returnUrl . '?subscription_db_id=' . $subscription->id;
        $cancelWithId = $cancelUrl  . '?subscription_db_id=' . $subscription->id;

        // ── Recurring subscription (plan has a PayPal plan ID) ─────────────
        if ($plan->paypal_plan_id) {
            $result = $this->payPal->createSubscription(
                $plan->paypal_plan_id,
                $returnWithId,
                $cancelWithId,
            );

            if (! $result['success']) {
                $subscription->update(['status' => 'failed']);
                throw new \RuntimeException($result['message'] ?? 'PayPal subscription creation failed.');
            }

            $subscription->update(['paypal_subscription_id' => $result['subscription_id']]);
            return $result['approval_url'];
        }

        // ── One-time order (no plan ID — sandbox / testing) ────────────────
        $result = $this->payPal->createPayment(
            amount:      $plan->price,
            currency:    $plan->currency,
            description: $plan->name . ' Vendor Subscription — ' . config('app.name'),
            returnUrl:   $returnWithId,
            cancelUrl:   $cancelWithId,
        );

        if (! $result['success']) {
            $subscription->update(['status' => 'failed']);
            throw new \RuntimeException($result['message'] ?? 'PayPal order creation failed.');
        }

        $subscription->update(['paypal_order_id' => $result['order_id']]);
        return $result['approval_url'];
    }

    // ──────────────────────────────────────────────────────────────────────
    // PAYMENT CAPTURE  (called on PayPal return URL)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Handle the PayPal return after vendor approves payment.
     *
     * KEY DESIGN: PayPal API calls are done FIRST, OUTSIDE any DB transaction.
     * Only the local DB writes are wrapped in a transaction.
     * This prevents the DB connection from timing out while waiting for PayPal.
     */
    public function capturePayment(
        Vendor  $vendor,
        ?string $token,
        ?string $payerId,
        ?int    $dbId = null
    ): Subscription {
        // Locate the pending subscription record
        $subscription = $dbId
            ? Subscription::findOrFail($dbId)
            : Subscription::where('vendor_id', $vendor->id)
                ->where('status', 'pending')
                ->latest()
                ->firstOrFail();

        // Guard: already activated (user refreshed the success page)
        if ($subscription->status === 'active') {
            return $subscription;
        }

        $plan = $subscription->plan;

        // ── STEP 1: Call PayPal OUTSIDE the transaction ────────────────────
        $captureData = [];

        if ($subscription->paypal_subscription_id) {
            // Recurring: verify the subscription is ACTIVE in PayPal
            $result = $this->payPal->getSubscription($subscription->paypal_subscription_id);

            if (! $result['success']) {
                $subscription->update(['status' => 'failed']);
                throw new \RuntimeException($result['message'] ?? 'Could not verify PayPal subscription.');
            }

            $ppSub = $result['subscription'];

            if (($ppSub['status'] ?? '') !== 'ACTIVE') {
                $subscription->update(['status' => 'failed', 'paypal_response' => $ppSub]);
                throw new \RuntimeException(
                    'PayPal subscription not yet active. Status: ' . ($ppSub['status'] ?? 'unknown') .
                    '. Please wait a moment and refresh.'
                );
            }

            $captureData = [
                'paypal_response' => $ppSub,
                'paypal_payer_id' => $payerId,
                'amount_paid'     => $plan->price,
            ];

        } else {
            // One-time order: capture the payment
            $orderId = $subscription->paypal_order_id ?? $token;

            if (! $orderId) {
                throw new \RuntimeException('No PayPal order ID found. Please contact support.');
            }

            $result = $this->payPal->capturePayment($orderId);

            if (! $result['success']) {
                $subscription->update(['status' => 'failed']);
                throw new \RuntimeException($result['message'] ?? 'PayPal payment capture failed.');
            }

            $captureData = [
                'paypal_response' => $result['order'] ?? [],
                'paypal_payer_id' => $this->payPal->getPayerId($result),
                'amount_paid'     => $this->payPal->getCapturedAmount($result),
            ];
        }

        // ── STEP 2: Persist everything in a DB transaction (no HTTP calls here)
        return DB::transaction(function () use ($vendor, $subscription, $plan, $captureData) {

            // Set subscription active
            $startsAt  = now();
            $expiresAt = $startsAt->copy()->addDays($plan->duration_days);

            $subscription->update(array_merge($captureData, [
                'status'          => 'active',
                'starts_at'       => $startsAt,
                'expires_at'      => $expiresAt,
                'last_renewed_at' => $startsAt,
                'next_renewal_at' => $expiresAt,
                'auto_renew'      => true,
            ]));

            // Activate vendor account — use fresh() to avoid stale model cache
            $vendor->fresh()->update(['status' => 'active']);

            // Reload so middleware gets fresh data on redirect
            $vendor->refresh();

            // Notify vendor
            $vendor->user->notify(new SubscriptionActivatedNotification($subscription));

            Log::info('Subscription activated', [
                'vendor_id'       => $vendor->id,
                'subscription_id' => $subscription->id,
                'plan'            => $plan->name,
                'expires_at'      => $expiresAt,
            ]);

            return $subscription->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // RENEWAL
    // ──────────────────────────────────────────────────────────────────────

    public function renew(Subscription $subscription): Subscription
    {
        $plan = $subscription->plan;

        return DB::transaction(function () use ($subscription, $plan) {
            $now       = now();
            $expiresAt = $now->copy()->addDays($plan->duration_days);

            $subscription->update([
                'status'          => 'active',
                'starts_at'       => $now,
                'expires_at'      => $expiresAt,
                'last_renewed_at' => $now,
                'next_renewal_at' => $expiresAt,
            ]);

            $subscription->vendor->update(['status' => 'active']);

            return $subscription->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // CANCELLATION
    // ──────────────────────────────────────────────────────────────────────

    public function cancel(Subscription $subscription, string $reason = 'Cancelled by vendor'): void
    {
        if ($subscription->paypal_subscription_id) {
            $result = $this->payPal->cancelSubscription(
                $subscription->paypal_subscription_id,
                $reason
            );

            if (! $result['success']) {
                Log::warning('PayPal cancel failed (proceeding with local cancel)', [
                    'subscription_id' => $subscription->id,
                    'message'         => $result['message'] ?? 'unknown',
                ]);
            }
        }

        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew'   => false,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // EXPIRY CHECK
    // ──────────────────────────────────────────────────────────────────────

    public function expireStale(): int
    {
        $expired = Subscription::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $subscription) {
            DB::transaction(function () use ($subscription) {
                $subscription->update(['status' => 'expired']);
                $subscription->vendor->update(['status' => 'subscription_required']);
            });
        }

        if ($expired->count()) {
            Log::info("SubscriptionService: expired {$expired->count()} subscription(s).");
        }

        return $expired->count();
    }

    // ──────────────────────────────────────────────────────────────────────
    // PLAN PROVISIONING
    // ──────────────────────────────────────────────────────────────────────

    public function provisionPayPalPlan(SubscriptionPlan $plan): string
    {
        $intervalMap = [
            'monthly'   => ['MONTH', 1],
            'quarterly' => ['MONTH', 3],
            'annual'    => ['YEAR',  1],
        ];

        [$interval, $count] = $intervalMap[$plan->billing_cycle] ?? ['MONTH', 1];

        $result = $this->payPal->createBillingPlan(
            name:          $plan->name . ' — ' . config('app.name'),
            description:   $plan->description ?? $plan->name . ' vendor subscription',
            amount:        $plan->price,
            currency:      $plan->currency,
            interval:      $interval,
            intervalCount: $count,
        );

        if (! $result['success']) {
            throw new \RuntimeException($result['message'] ?? 'Failed to provision PayPal plan.');
        }

        $plan->update(['paypal_plan_id' => $result['plan_id']]);

        return $result['plan_id'];
    }
}
