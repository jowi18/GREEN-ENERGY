<?php

// ════════════════════════════════════════════════════════════════════════════
// bootstrap/app.php
// Full file — replaces the default Laravel 11 bootstrap/app.php
// ════════════════════════════════════════════════════════════════════════════

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        // then: function () {
        //     Route::middleware('web')
        //         ->group(base_path('routes/admin.php'));

        //     Route::middleware('web')
        //         ->group(base_path('routes/vendor.php'));

        //     Route::middleware('web')
        //         ->group(base_path('routes/customer.php'));
        // },
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ── Aliases (used in route definitions) ───────────────────────────
        $middleware->alias([
            'admin'               => \App\Http\Middleware\AdminMiddleware::class,
            'vendor'              => \App\Http\Middleware\VendorMiddleware::class,
            'customer'            => \App\Http\Middleware\CustomerMiddleware::class,
            'supplier'            => \App\Http\Middleware\SupplierMiddleware::class,
            'vendor.verified'     => \App\Http\Middleware\VendorVerifiedMiddleware::class,
            'subscription.active' => \App\Http\Middleware\SubscriptionActiveMiddleware::class,
            'permission'          => \App\Http\Middleware\RolePermissionMiddleware::class,
            'module.access'       =>  \App\Http\Middleware\ModuleAccessMiddleware::class,
        ]);

        // ── Global web middleware additions ───────────────────────────────
        // (add any platform-wide middleware here, e.g. locale detection)
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
