<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private ?PayPalClient $client = null;

    /**
     * Lazy-initialize the PayPal client only when an API call is needed.
     * Avoids crashing during artisan commands (db:seed, migrate, queue, etc.)
     * that have nothing to do with PayPal.
     */
    private function client(): PayPalClient
    {
        if ($this->client === null) {
            $this->client = new PayPalClient;
            $this->client->setApiCredentials(config('paypal'));

            $token = $this->client->getAccessToken();

            if (empty($token['access_token'])) {
                throw new \RuntimeException(
                    'PayPal authentication failed. Check PAYPAL_MODE, ' .
                    'PAYPAL_SANDBOX_CLIENT_ID and PAYPAL_SANDBOX_CLIENT_SECRET in your .env.'
                );
            }

            $this->client->setAccessToken($token);
        }

        return $this->client;
    }

    // ──────────────────────────────────────────────────────────────
    // ONE-TIME ORDERS  (used for subscription payments + POS PayPal)
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a PayPal order and return the approval URL.
     *
     * @param  float   $amount       Total in USD (or configured currency)
     * @param  string  $description  Line-item description shown in PayPal
     * @param  string  $returnUrl    Where PayPal sends the buyer after approval
     * @param  string  $cancelUrl    Where PayPal sends the buyer on cancel
     * @return array{order_id: string, approval_url: string}
     */
    public function createOrder(
        float  $amount,
        string $description,
        string $returnUrl,
        string $cancelUrl,
        string $currency = 'USD'
    ): array {
        $data = [
            'intent'              => 'CAPTURE',
            'application_context' => [
                'return_url'          => $returnUrl,
                'cancel_url'          => $cancelUrl,
                'brand_name'          => config('app.name'),
                'user_action'         => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
            ],
            'purchase_units' => [[
                'description' => $description,
                'amount'      => [
                    'currency_code' => $currency,
                    'value'         => number_format($amount, 2, '.', ''),
                ],
            ]],
        ];

        $response = $this->client()->createOrder($data);

        if (isset($response['error'])) {
            Log::error('PayPal createOrder error', $response);
            throw new \RuntimeException('PayPal order creation failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        $approvalUrl = collect($response['links'])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (! $approvalUrl) {
            throw new \RuntimeException('PayPal did not return an approval URL.');
        }

        return [
            'order_id'     => $response['id'],
            'approval_url' => $approvalUrl,
        ];
    }

    /**
     * Capture a previously approved PayPal order.
     *
     * @param  string $orderId  The PayPal order ID from createOrder()
     * @return array            Full PayPal capture response
     */
    public function captureOrder(string $orderId): array
    {
        $response = $this->client()->capturePaymentOrder($orderId);

        if (isset($response['error'])) {
            Log::error('PayPal captureOrder error', ['order_id' => $orderId, 'response' => $response]);
            throw new \RuntimeException('PayPal capture failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        if (($response['status'] ?? '') !== 'COMPLETED') {
            throw new \RuntimeException('PayPal payment not completed. Status: ' . ($response['status'] ?? 'unknown'));
        }

        return $response;
    }

    /**
     * Get full order details from PayPal.
     */
    public function getOrder(string $orderId): array
    {
        return $this->client()->showOrderDetails($orderId);
    }

    // ──────────────────────────────────────────────────────────────
    // RECURRING SUBSCRIPTIONS  (uses PayPal Subscriptions API)
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a PayPal subscription against an existing Plan ID.
     *
     * @return array{subscription_id: string, approval_url: string}
     */
    public function createSubscription(
        string $paypalPlanId,
        string $returnUrl,
        string $cancelUrl
    ): array {
        $data = [
            'plan_id'             => $paypalPlanId,
            'application_context' => [
                'brand_name'  => config('app.name'),
                'user_action' => 'SUBSCRIBE_NOW',
                'return_url'  => $returnUrl,
                'cancel_url'  => $cancelUrl,
                'payment_method' => [
                    'payer_selected'  => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
            ],
        ];

        $response = $this->client()->createSubscription($data);

        if (isset($response['error'])) {
            Log::error('PayPal createSubscription error', $response);
            throw new \RuntimeException('PayPal subscription creation failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        $approvalUrl = collect($response['links'])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (! $approvalUrl) {
            throw new \RuntimeException('PayPal did not return a subscription approval URL.');
        }

        return [
            'subscription_id' => $response['id'],
            'approval_url'    => $approvalUrl,
        ];
    }

    /**
     * Get details of an existing PayPal subscription.
     */
    public function getSubscription(string $subscriptionId): array
    {
        return $this->client()->showSubscriptionDetails($subscriptionId);
    }

    /**
     * Cancel a PayPal subscription.
     */
    public function cancelSubscription(string $subscriptionId, string $reason = 'Cancelled by vendor'): void
    {
        $this->client()->cancelSubscription($subscriptionId, $reason);
    }

    // ──────────────────────────────────────────────────────────────
    // BILLING PLANS  (admin utility — create plans in PayPal)
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a PayPal Billing Plan for a subscription tier.
     * Call once per SubscriptionPlan and store the returned ID
     * in subscription_plans.paypal_plan_id.
     */
    public function createBillingPlan(
        string $name,
        string $description,
        float  $amount,
        string $interval      = 'MONTH',
        int    $intervalCount = 1,
        string $currency      = 'USD'
    ): string {
        $data = [
            'product_id'          => $this->ensureProduct(),
            'name'                => $name,
            'description'         => $description,
            'status'              => 'ACTIVE',
            'billing_cycles'      => [[
                'frequency'      => [
                    'interval_unit'  => $interval,
                    'interval_count' => $intervalCount,
                ],
                'tenure_type'    => 'REGULAR',
                'sequence'       => 1,
                'total_cycles'   => 0,
                'pricing_scheme' => [
                    'fixed_price' => [
                        'value'         => number_format($amount, 2, '.', ''),
                        'currency_code' => $currency,
                    ],
                ],
            ]],
            'payment_preferences' => [
                'auto_bill_outstanding'     => true,
                'setup_fee_failure_action'  => 'CONTINUE',
                'payment_failure_threshold' => 3,
            ],
        ];

        $response = $this->client()->createPlan($data);

        if (isset($response['error'])) {
            Log::error('PayPal createBillingPlan error', $response);
            throw new \RuntimeException('Failed to create PayPal billing plan: ' . ($response['error']['message'] ?? 'Unknown'));
        }

        return $response['id'];
    }

    /**
     * Ensure a PayPal product exists for the platform.
     */
    private function ensureProduct(): string
    {
        $productId = config('paypal.product_id');

        if ($productId) {
            return $productId;
        }

        $response = $this->client()->createProduct([
            'name'        => config('app.name') . ' Vendor Subscription',
            'description' => 'Vendor portal subscription for ' . config('app.name'),
            'type'        => 'SERVICE',
            'category'    => 'SOFTWARE',
        ]);

        if (isset($response['error'])) {
            throw new \RuntimeException('Failed to create PayPal product: ' . ($response['error']['message'] ?? 'Unknown'));
        }

        Log::info('PayPal product created', ['id' => $response['id']]);

        return $response['id'];
    }

    // ──────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────

    /**
     * Extract the captured amount from a PayPal capture response.
     */
    public function getCapturedAmount(array $captureResponse): float
    {
        return (float) ($captureResponse['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0);
    }

    /**
     * Extract the payer ID from a PayPal capture response.
     */
    public function getPayerId(array $captureResponse): ?string
    {
        return $captureResponse['payer']['payer_id'] ?? null;
    }
}
