<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\PayPalService::class);

        $this->app->singleton(\App\Services\SubscriptionService::class, function ($app) {
            return new \App\Services\SubscriptionService($app->make(\App\Services\PayPalService::class));
        });
        $this->app->singleton(\App\Services\InventoryService::class);
        $this->app->singleton(\App\Services\ChatService::class);
        $this->app->singleton(\App\Services\ServiceManagementService::class);

        $this->app->singleton(\App\Services\OrderManagementService::class, function ($app) {
            return new \App\Services\OrderManagementService($app->make(\App\Services\InventoryService::class));
        });

        $this->app->singleton(\App\Services\PayrollService::class);
        $this->app->singleton(\App\Services\AttendanceService::class, function ($app) {
            return new \App\Services\AttendanceService(
                $app->make(\App\Services\PayrollService::class)
            );
        });
        $this->app->singleton(\App\Services\ReviewService::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
