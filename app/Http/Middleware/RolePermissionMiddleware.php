<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        // Vendor owners always have full access
        if ($user->isVendor()) {
            return $next($request);
        }

        // Employees: check merged permission set across all their roles
        if ($user->isEmployee()) {
            $employee = $user->employee;

            if (!$employee) {
                abort(403, 'Employee profile not found.');
            }

            $hasPermission = $employee->roles()
                ->with('permissions')
                ->get()
                ->flatMap(fn ($role) => $role->permissions->pluck('slug'))
                ->unique()
                ->contains($permission);

            if (!$hasPermission) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'You do not have permission to perform this action.'], 403);
                }

                return redirect()->back()->with('error', 'You do not have permission to perform this action.');
            }
        }

        return $next($request);
    }
}
