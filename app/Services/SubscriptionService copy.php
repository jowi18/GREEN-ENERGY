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

    // ──────────────────────────────────────────────────────────────
    // CHECKOUT INITIATION
    // ──────────────────────────────────────────────────────────────

    /**
     * Initiate a PayPal checkout for the selected plan.
     *
     * If the plan has a paypal_plan_id it uses the Subscriptions API
     * (recurring). Otherwise it falls back to a one-time Order (simpler,
     * no recurring billing — useful during sandbox testing).
     *
     * Returns the PayPal approval URL to redirect the vendor to.
     */
    public function initiateCheckout(Vendor $vendor, SubscriptionPlan $plan): string
    {
        $returnUrl = route('vendor.subscription.success');
        $cancelUrl = route('vendor.subscription.cancel');

        // ── Create a pending Subscription record first ─────────────
        $subscription = Subscription::create([
            'vendor_id'            => $vendor->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'pending',
            'currency'             => $plan->currency,
        ]);

        try {
            if ($plan->paypal_plan_id) {
                // ── Recurring subscription via PayPal Billing Plans ──
                $result = $this->payPal->createSubscription(
                    $plan->paypal_plan_id,
                    $returnUrl . '?subscription_db_id=' . $subscription->id,
                    $cancelUrl  . '?subscription_db_id=' . $subscription->id,
                );

                $subscription->update([
                    'paypal_subscription_id' => $result['subscription_id'],
                ]);

                return $result['approval_url'];

            } else {
                // ── One-time order (sandbox / manual billing) ────────
                $result = $this->payPal->createOrder(
                    amount:      $plan->price,
                    description: $plan->name . ' Vendor Subscription — ' . config('app.name'),
                    returnUrl:   $returnUrl . '?subscription_db_id=' . $subscription->id,
                    cancelUrl:   $cancelUrl  . '?subscription_db_id=' . $subscription->id,
                    currency:    $plan->currency,
                );

                $subscription->update([
                    'paypal_order_id' => $result['order_id'],
                ]);

                return $result['approval_url'];
            }

        } catch (\Throwable $e) {
            // Clean up the pending record on failure
            $subscription->update(['status' => 'failed']);
            Log::error('SubscriptionService::initiateCheckout failed', [
                'vendor_id' => $vendor->id,
                'plan_id'   => $plan->id,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // ──────────────────────────────────────────────────────────────
    // PAYMENT CAPTURE  (PayPal redirect return)
    // ──────────────────────────────────────────────────────────────

    /**
     * Handle the PayPal return after vendor approves payment.
     *
     * For one-time orders:  captures the payment via token + PayerID.
     * For subscriptions:    the subscription is already active server-side;
     *                        we just verify and activate locally.
     *
     * @param  Vendor  $vendor
     * @param  string|null $token    PayPal order token (one-time orders)
     * @param  string|null $payerId  PayPal payer ID
     * @param  int|null    $dbId     Our internal Subscription.id
     * @return Subscription
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

        $plan = $subscription->plan;

        return DB::transaction(function () use ($vendor, $subscription, $plan, $token, $payerId) {

            if ($subscription->paypal_subscription_id) {
                // ── Recurring: verify subscription is ACTIVE in PayPal ──
                $ppSub = $this->payPal->getSubscription($subscription->paypal_subscription_id);

                if (($ppSub['status'] ?? '') !== 'ACTIVE') {
                    $subscription->update(['status' => 'failed', 'paypal_response' => $ppSub]);
                    throw new \RuntimeException('PayPal subscription is not active. Status: ' . ($ppSub['status'] ?? 'unknown'));
                }

                $this->activateSubscription($subscription, $plan, [
                    'paypal_response' => $ppSub,
                    'paypal_payer_id' => $payerId,
                    'amount_paid'     => $plan->price,
                ]);

            } else {
                // ── One-time order: capture the payment ─────────────────
                $orderId = $subscription->paypal_order_id ?? $token;

                if (! $orderId) {
                    throw new \RuntimeException('No PayPal order ID found for capture.');
                }

                $capture = $this->payPal->captureOrder($orderId);

                $this->activateSubscription($subscription, $plan, [
                    'paypal_response' => $capture,
                    'paypal_payer_id' => $this->payPal->getPayerId($capture),
                    'amount_paid'     => $this->payPal->getCapturedAmount($capture),
                ]);
            }

            // Activate the vendor's account
            $vendor->update(['status' => 'active']);

            // Notify the vendor
            $vendor->user->notify(new SubscriptionActivatedNotification($subscription));

            Log::info('Subscription activated', [
                'vendor_id'       => $vendor->id,
                'subscription_id' => $subscription->id,
                'plan'            => $plan->name,
                'expires_at'      => $subscription->fresh()->expires_at,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Persist the subscription as active and set expiry dates.
     */
    private function activateSubscription(
        Subscription     $subscription,
        SubscriptionPlan $plan,
        array            $extra = []
    ): void {
        $startsAt  = now();
        $expiresAt = $startsAt->copy()->addDays($plan->duration_days);

        $subscription->update(array_merge([
            'status'           => 'active',
            'starts_at'        => $startsAt,
            'expires_at'       => $expiresAt,
            'last_renewed_at'  => $startsAt,
            'next_renewal_at'  => $expiresAt,
            'auto_renew'       => true,
        ], $extra));
    }

    // ──────────────────────────────────────────────────────────────
    // RENEWAL
    // ──────────────────────────────────────────────────────────────

    /**
     * Renew an existing subscription by one billing cycle.
     * Called from a PayPal webhook or the CheckExpiredSubscriptions command.
     */
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

            // Ensure vendor stays active
            $subscription->vendor->update(['status' => 'active']);

            return $subscription->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────
    // CANCELLATION
    // ──────────────────────────────────────────────────────────────

    /**
     * Cancel a vendor's active subscription.
     * Keeps the subscription active until its expiry date (grace period).
     */
    public function cancel(Subscription $subscription, string $reason = 'Cancelled by vendor'): void
    {
        // Cancel in PayPal if it's a recurring subscription
        if ($subscription->paypal_subscription_id) {
            try {
                $this->payPal->cancelSubscription($subscription->paypal_subscription_id, $reason);
            } catch (\Throwable $e) {
                Log::warning('PayPal subscription cancel failed (local cancel proceeds)', [
                    'subscription_id' => $subscription->id,
                    'error'           => $e->getMessage(),
                ]);
            }
        }

        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew'   => false,
        ]);

        Log::info('Subscription cancelled', ['subscription_id' => $subscription->id]);
    }

    // ──────────────────────────────────────────────────────────────
    // EXPIRY CHECK  (called by CheckExpiredSubscriptions command)
    // ──────────────────────────────────────────────────────────────

    /**
     * Expire all subscriptions whose expires_at is in the past
     * and update vendor status accordingly.
     *
     * @return int  Number of subscriptions expired
     */
    public function expireStale(): int
    {
        $expired = Subscription::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $subscription) {
            DB::transaction(function () use ($subscription) {
                $subscription->update(['status' => 'expired']);

                // Move vendor back to subscription_required
                $subscription->vendor->update(['status' => 'subscription_required']);
            });
        }

        if ($expired->count()) {
            Log::info("SubscriptionService: expired {$expired->count()} subscription(s).");
        }

        return $expired->count();
    }

    // ──────────────────────────────────────────────────────────────
    // PLAN PROVISIONING  (admin utility)
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a PayPal Billing Plan for a SubscriptionPlan and store its ID.
     * Run once from an Artisan command or admin action.
     */
    public function provisionPayPalPlan(SubscriptionPlan $plan): string
    {
        $intervalMap = [
            'monthly'   => ['MONTH', 1],
            'quarterly' => ['MONTH', 3],
            'annual'    => ['YEAR',  1],
        ];

        [$interval, $count] = $intervalMap[$plan->billing_cycle] ?? ['MONTH', 1];

        $planId = $this->payPal->createBillingPlan(
            name:          $plan->name . ' — ' . config('app.name'),
            description:   $plan->description ?? $plan->name . ' vendor subscription',
            amount:        $plan->price,
            interval:      $interval,
            intervalCount: $count,
            currency:      $plan->currency,
        );

        $plan->update(['paypal_plan_id' => $planId]);

        Log::info("PayPal plan created for [{$plan->name}]: {$planId}");

        return $planId;
    }
}
