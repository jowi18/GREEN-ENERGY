<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Exception;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected ?PayPalClient $provider = null;

    /**
     * Lazy-init the provider only when an API call is needed.
     * Prevents crashes during artisan commands (db:seed, migrate, etc.)
     * that have nothing to do with PayPal.
     */
    protected function provider(): PayPalClient
    {
        if ($this->provider === null) {
            $this->provider = new PayPalClient;
            $this->provider->setApiCredentials(config('paypal'));
            $token = $this->provider->getAccessToken();
            $this->provider->setAccessToken($token);
        }

        return $this->provider;
    }

    // ──────────────────────────────────────────────────────────────────────
    // ONE-TIME ORDERS
    // Used for: vendor subscription payments, online order checkout, POS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Create a simple PayPal order (single amount + description).
     * Returns ['success', 'order_id', 'approval_url'] on success.
     * Returns ['success' => false, 'message'] on failure.
     */
    public function createPayment(
        float  $amount,
        string $currency,
        string $description,
        string $returnUrl,
        string $cancelUrl
    ): array {
        try {
            $data = [
                'intent'              => 'CAPTURE',
                'purchase_units'      => [[
                    'amount'      => [
                        'currency_code' => $currency,
                        'value'         => number_format($amount, 2, '.', ''),
                    ],
                    'description' => $description,
                ]],
                'application_context' => [
                    'cancel_url'          => $cancelUrl,
                    'return_url'          => $returnUrl,
                    'brand_name'          => config('app.name'),
                    'locale'              => 'en-US',
                    'landing_page'        => 'BILLING',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action'         => 'PAY_NOW',
                ],
            ];

            $order = $this->provider()->createOrder($data);

            if (isset($order['id']) && $order['id'] !== null) {
                foreach ($order['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return [
                            'success'      => true,
                            'order_id'     => $order['id'],
                            'approval_url' => $link['href'],
                        ];
                    }
                }
            }

            Log::error('PayPal createPayment failed', ['response' => $order]);

            return [
                'success' => false,
                'message' => $order['message'] ?? 'Unable to create PayPal order',
                'details' => $order,
            ];

        } catch (Exception $e) {
            Log::error('PayPal createPayment exception: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create a PayPal order with a full line-items breakdown.
     * Used by POS PayPal checkout where individual products need to be listed.
     *
     * $items = [
     *   ['name' => 'Solar Panel', 'description' => '...', 'quantity' => 2, 'price' => 12500.00],
     * ]
     */
    public function createPaymentWithItems(
        array  $items,
        string $currency,
        string $returnUrl,
        string $cancelUrl
    ): array {
        try {
            $subtotal   = 0;
            $itemsArray = [];

            foreach ($items as $item) {
                $itemTotal  = $item['quantity'] * $item['price'];
                $subtotal  += $itemTotal;

                $itemsArray[] = [
                    'name'        => $item['name'],
                    'description' => $item['description'] ?? '',
                    'quantity'    => (string) $item['quantity'],
                    'unit_amount' => [
                        'currency_code' => $currency,
                        'value'         => number_format($item['price'], 2, '.', ''),
                    ],
                ];
            }

            $data = [
                'intent'              => 'CAPTURE',
                'purchase_units'      => [[
                    'amount' => [
                        'currency_code' => $currency,
                        'value'         => number_format($subtotal, 2, '.', ''),
                        'breakdown'     => [
                            'item_total' => [
                                'currency_code' => $currency,
                                'value'         => number_format($subtotal, 2, '.', ''),
                            ],
                        ],
                    ],
                    'items' => $itemsArray,
                ]],
                'application_context' => [
                    'cancel_url'          => $cancelUrl,
                    'return_url'          => $returnUrl,
                    'brand_name'          => config('app.name'),
                    'locale'              => 'en-US',
                    'landing_page'        => 'BILLING',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action'         => 'PAY_NOW',
                ],
            ];

            $order = $this->provider()->createOrder($data);

            if (isset($order['id']) && $order['id'] !== null) {
                foreach ($order['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return [
                            'success'      => true,
                            'order_id'     => $order['id'],
                            'approval_url' => $link['href'],
                        ];
                    }
                }
            }

            Log::error('PayPal createPaymentWithItems failed', ['response' => $order]);

            return [
                'success' => false,
                'message' => $order['message'] ?? 'Unable to create PayPal order',
                'details' => $order,
            ];

        } catch (Exception $e) {
            Log::error('PayPal createPaymentWithItems exception: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Capture an approved PayPal order (called on the return URL).
     * Returns ['success', 'order', 'transaction_id', 'payer_id', 'payer_email', 'amount'].
     */
    public function capturePayment(string $orderId): array
    {
        try {
            $result = $this->provider()->capturePaymentOrder($orderId);

            if (isset($result['status']) && $result['status'] === 'COMPLETED') {
                return [
                    'success'        => true,
                    'order'          => $result,
                    'transaction_id' => $result['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                    'payer_id'       => $result['payer']['payer_id'] ?? null,
                    'payer_email'    => $result['payer']['email_address'] ?? null,
                    'amount'         => $result['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null,
                ];
            }

            Log::error('PayPal capturePayment failed', ['response' => $result]);

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Payment capture failed',
                'details' => $result,
            ];

        } catch (Exception $e) {
            Log::error('PayPal capturePayment exception: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get full details of a PayPal order by ID.
     */
    public function getOrderDetails(string $orderId): array
    {
        try {
            $order = $this->provider()->showOrderDetails($orderId);

            return ['success' => true, 'order' => $order];

        } catch (Exception $e) {
            Log::error('PayPal getOrderDetails exception: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // RECURRING SUBSCRIPTIONS
    // Used by: vendor subscription activation flow
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Create a recurring PayPal subscription against an existing Billing Plan ID.
     * Returns ['success', 'subscription_id', 'approval_url'] on success.
     */
    public function createSubscription(
        string $paypalPlanId,
        string $returnUrl,
        string $cancelUrl
    ): array {
        try {
            $data = [
                'plan_id'             => $paypalPlanId,
                'application_context' => [
                    'brand_name'     => config('app.name'),
                    'user_action'    => 'SUBSCRIBE_NOW',
                    'return_url'     => $returnUrl,
                    'cancel_url'     => $cancelUrl,
                    'payment_method' => [
                        'payer_selected'  => 'PAYPAL',
                        'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                    ],
                ],
            ];

            $response = $this->provider()->createSubscription($data);

            if (isset($response['id']) && $response['id'] !== null) {
                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return [
                            'success'         => true,
                            'subscription_id' => $response['id'],
                            'approval_url'    => $link['href'],
                        ];
                    }
                }
            }

            Log::error('PayPal createSubscription failed', ['response' => $response]);

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Unable to create PayPal subscription',
                'details' => $response,
            ];

        } catch (Exception $e) {
            Log::error('PayPal createSubscription exception: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get details of an existing PayPal subscription.
     */
    public function getSubscription(string $subscriptionId): array
    {
        try {
            $result = $this->provider()->showSubscriptionDetails($subscriptionId);

            return ['success' => true, 'subscription' => $result];

        } catch (Exception $e) {
            Log::error('PayPal getSubscription exception: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Cancel an active PayPal subscription.
     */
    public function cancelSubscription(
        string $subscriptionId,
        string $reason = 'Cancelled by vendor'
    ): array {
        try {
            $this->provider()->cancelSubscription($subscriptionId, $reason);

            return ['success' => true];

        } catch (Exception $e) {
            Log::error('PayPal cancelSubscription exception: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // BILLING PLANS  (admin utility — run once per SubscriptionPlan)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Create a PayPal Billing Plan for a vendor subscription tier.
     * Call once per SubscriptionPlan and store the ID in
     * subscription_plans.paypal_plan_id.
     *
     * Returns ['success', 'plan_id'] on success.
     */
    public function createBillingPlan(
        string $name,
        string $description,
        float  $amount,
        string $currency      = 'USD',
        string $interval      = 'MONTH',
        int    $intervalCount = 1
    ): array {
        try {
            $productResult = $this->ensureProduct();

            if (! $productResult['success']) {
                return $productResult;
            }

            $data = [
                'product_id'          => $productResult['product_id'],
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

            $response = $this->provider()->createPlan($data);

            if (isset($response['id'])) {
                return ['success' => true, 'plan_id' => $response['id']];
            }

            Log::error('PayPal createBillingPlan failed', ['response' => $response]);

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Failed to create billing plan',
                'details' => $response,
            ];

        } catch (Exception $e) {
            Log::error('PayPal createBillingPlan exception: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Ensure a PayPal Product exists for the platform.
     * Returns ['success', 'product_id'].
     */
    private function ensureProduct(): array
    {
        $productId = config('paypal.product_id');

        if ($productId) {
            return ['success' => true, 'product_id' => $productId];
        }

        try {
            $response = $this->provider()->createProduct([
                'name'        => config('app.name') . ' Vendor Subscription',
                'description' => 'Vendor portal subscription — ' . config('app.name'),
                'type'        => 'SERVICE',
                'category'    => 'SOFTWARE',
            ]);

            if (isset($response['id'])) {
                Log::info('PayPal product created — add to .env: PAYPAL_PRODUCT_ID=' . $response['id']);

                return ['success' => true, 'product_id' => $response['id']];
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Failed to create PayPal product',
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // CONVENIENCE HELPERS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Extract the captured amount from a capturePayment() result.
     */
    public function getCapturedAmount(array $captureResult): float
    {
        return (float) ($captureResult['amount'] ?? 0);
    }

    /**
     * Extract the payer ID from a capturePayment() result.
     */
    public function getPayerId(array $captureResult): ?string
    {
        return $captureResult['payer_id'] ?? null;
    }
}
