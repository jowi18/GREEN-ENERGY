<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    /**
     * Show subscription plan selection page.
     */
    public function index()
    {
        $vendor = auth()->user()->vendor
            ?? auth()->user()->employee?->vendor;

        $plans               = SubscriptionPlan::active()->get();
        $currentSubscription = $vendor?->activeSubscription?->load('plan');

        return view('vendor.subscription.index', compact(
            'vendor',
            'plans',
            'currentSubscription'
        ));
    }

    /**
     * Initiate PayPal checkout for the selected plan.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
        ]);

        $vendor = auth()->user()->vendor;

        if (! $vendor) {
            return redirect()
                ->route('vendor.subscription.index')
                ->with('error', 'Vendor profile not found.');
        }

        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        try {
            $approvalUrl = $this->subscriptionService->initiateCheckout($vendor, $plan);
            return redirect($approvalUrl);
        } catch (\Exception $e) {
            Log::error('Subscription checkout failed', [
                'vendor_id' => $vendor->id,
                'plan_id'   => $plan->id,
                'error'     => $e->getMessage(),
            ]);

            return redirect()
                ->route('vendor.subscription.index')
                ->with('error', 'Unable to connect to PayPal. ' . $e->getMessage());
        }
    }

    /**
     * PayPal redirects back here after the vendor approves payment.
     *
     * PayPal appends these query params:
     *   - token      : the PayPal order token  (one-time orders)
     *   - PayerID    : the payer's PayPal ID
     *   - subscription_db_id : our internal Subscription.id (we added this)
     *
     * The method is idempotent — if the vendor refreshes this page after a
     * successful capture it won't re-charge them.
     */
    public function success(Request $request)
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor) {
            return redirect()->route('vendor.login');
        }

        // Log what PayPal sent back to help debug any future issues
        Log::info('PayPal success callback received', [
            'vendor_id'          => $vendor->id,
            'token'              => $request->query('token'),
            'PayerID'            => $request->query('PayerID'),
            'subscription_db_id' => $request->query('subscription_db_id'),
            'all_params'         => $request->query(),
        ]);

        try {
            $this->subscriptionService->capturePayment(
                vendor:  $vendor,
                token:   $request->query('token'),
                payerId: $request->query('PayerID'),
                dbId:    $request->query('subscription_db_id')
                            ? (int) $request->query('subscription_db_id')
                            : null,
            );

            // Forget the authenticated user model so the next request
            // gets a fresh vendor/subscription state from the database
            auth()->setUser(auth()->user()->fresh());

            return redirect()
                ->route('vendor.dashboard')
                ->with('success', '🎉 Subscription activated! Your vendor portal is now fully accessible.');

        } catch (\Exception $e) {
            Log::error('Subscription capture failed', [
                'vendor_id' => $vendor->id,
                'token'     => $request->query('token'),
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('vendor.subscription.index')
                ->with('error', 'Payment could not be confirmed: ' . $e->getMessage());
        }
    }

    /**
     * PayPal redirects here if the vendor cancels on the PayPal page.
     */
    public function cancel(Request $request)
    {
        // Mark the pending subscription as cancelled if we have the DB id
        if ($dbId = $request->query('subscription_db_id')) {
            \App\Models\Subscription::where('id', (int) $dbId)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);
        }

        return redirect()
            ->route('vendor.subscription.index')
            ->with('warning', 'Payment was cancelled. Choose a plan and try again whenever you\'re ready.');
    }
}
