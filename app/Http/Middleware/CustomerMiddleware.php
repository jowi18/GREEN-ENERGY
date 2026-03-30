<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isCustomer()) {
            return redirect()->route('customer.login');
        }

        if (!auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('customer.login')->with('error', 'Your account has been suspended.');
        }

        return $next($request);
    }
}
