<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorVerifiedMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Employees inherit their vendor's status
        $vendor = $user->isEmployee()
            ? $user->employee?->vendor
            : $user->vendor;

        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        if ($vendor->status === 'pending' || $vendor->status === 'under_review') {
            return redirect()->route('vendor.register.status')
                ->with('info', 'Your application is currently under review. You will be notified once approved.');
        }

        if ($vendor->status === 'rejected') {
            return redirect()->route('vendor.register.status')
                ->with('error', 'Your application was not approved. ' . $vendor->rejection_reason);
        }

        if ($vendor->status === 'suspended') {
            auth()->logout();
            return redirect()->route('vendor.login')
                ->with('error', 'Your vendor account has been suspended. Contact support for assistance.');
        }

        return $next($request);
    }
}
