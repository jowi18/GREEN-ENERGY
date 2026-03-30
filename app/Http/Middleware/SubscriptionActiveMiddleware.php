<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks vendor portal access if the subscription is lapsed or not yet paid.
 *
 * IMPORTANT: always calls ->fresh() on the vendor to avoid serving a
 * stale cached model after the subscription is activated on the success URL.
 * Without fresh(), the middleware would re-read the old 'pending' status
 * and redirect the vendor back to the subscription page in a loop.
 */
class SubscriptionActiveMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Resolve vendor — owner or employee
        $vendor = $user->isEmployee()
            ? $user->employee?->vendor
            : $user->vendor;

        if (! $vendor) {
            abort(403);
        }

        // Always reload from DB — never trust the in-memory cached model
        $vendor = $vendor->fresh();

        // Approved but subscription not yet paid
        if ($vendor->status === 'subscription_required') {
            return redirect()->route('vendor.subscription.index')
                ->with('info', 'Please complete your subscription to access the vendor portal.');
        }

        // Active vendor — verify the subscription record hasn't expired
        if ($vendor->status === 'active') {
            $activeSub = $vendor->activeSubscription()->first(); // fresh query, no cache

            if (! $activeSub) {
                // Subscription expired since last check — downgrade vendor
                $vendor->update(['status' => 'subscription_required']);

                return redirect()->route('vendor.subscription.index')
                    ->with('warning', 'Your subscription has expired. Please renew to continue.');
            }
        }

        return $next($request);
    }
}
