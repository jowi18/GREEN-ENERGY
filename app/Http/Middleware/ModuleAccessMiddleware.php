<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ModuleAccessMiddleware
 *
 * Blocks vendor-portal employees from accessing routes whose module
 * they have not been granted any permission for.
 *
 * Vendor OWNERS bypass this check entirely — they have full access.
 *
 * Route-to-module map:
 *   vendor.dashboard*        → dashboard
 *   vendor.products*         → products
 *   vendor.inventory*        → inventory
 *   vendor.pos*              → pos
 *   vendor.orders*           → orders
 *   vendor.services*         → services
 *   vendor.service-catalog*  → services
 *   vendor.warranty*         → warranty
 *   vendor.employees*        → employees
 *   vendor.roles*            → roles
 *   vendor.subscription*     → subscription  (always allowed)
 *
 * Register alias in bootstrap/app.php:
 *   'module.access' => ModuleAccessMiddleware::class
 *
 * Apply globally in routes/vendor.php inside subscription.active group:
 *   Route::middleware(['subscription.active', 'module.access'])->group(...)
 */
class ModuleAccessMiddleware
{
    /**
     * Route name prefix → module slug mapping.
     * Order matters — more specific prefixes first.
     */
    private const ROUTE_MODULE_MAP = [
        'vendor.dashboard'       => 'dashboard',
        'vendor.products'        => 'products',
        'vendor.inventory'       => 'inventory',
        'vendor.pos'             => 'pos',
        'vendor.orders'          => 'orders',
        'vendor.services'        => 'services',
        'vendor.service-catalog' => 'services',
        'vendor.warranty'        => 'warranty',
        'vendor.employees'       => 'employees',
        'vendor.roles'           => 'roles',
        'vendor.subscription'    => 'subscription', // always allowed
    ];

    /**
     * Modules that are always accessible regardless of permissions.
     */
    private const ALWAYS_ALLOWED = [
        'subscription',
        'dashboard',   // employees can always see the dashboard
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Vendor owners always have full access
        if ($user->isVendor()) {
            return $next($request);
        }

        // Not an employee — let other middleware handle it
        if (! $user->isEmployee()) {
            return $next($request);
        }

        $employee = $user->employee;

        if (! $employee) {
            abort(403, 'Employee profile not found.');
        }

        // Determine which module this route belongs to
        $module = $this->resolveModule($request->route()->getName() ?? '');

        // Unknown/unmapped routes are allowed through
        if ($module === null) {
            return $next($request);
        }

        // Always-allowed modules bypass permission check
        if (in_array($module, self::ALWAYS_ALLOWED)) {
            return $next($request);
        }

        // Check if employee has any permission in this module
        if (! $employee->canAccessModule($module)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "You don't have access to the {$module} module.",
                ], 403);
            }

            return redirect()
                ->route('vendor.dashboard')
                ->with('error', "You don't have permission to access that module.");
        }

        return $next($request);
    }

    /**
     * Match the current route name to a module slug.
     */
    private function resolveModule(string $routeName): ?string
    {
        foreach (self::ROUTE_MODULE_MAP as $prefix => $module) {
            if (str_starts_with($routeName, $prefix)) {
                return $module;
            }
        }

        return null;
    }
}
