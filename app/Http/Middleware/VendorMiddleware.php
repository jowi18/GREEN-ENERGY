<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('vendor.login');
        }

        $user = auth()->user();

        // Both vendor owners and their employees use the vendor portal
        if (!in_array($user->user_type, ['vendor', 'employee'])) {
            abort(403, 'Vendor access required.');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('vendor.login')->with('error', 'Your account has been deactivated.');
        }

        return $next($request);
    }
}
