<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Makiomar\WooOrderDashboard\Helpers\WooOrderStatusHelper;

class WooOrderDashboardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(WooOrderStatusHelper::class, function ($app) {
            return new WooOrderStatusHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../resources/css/woo-order-dashboard.css' => public_path('css/woo-order-dashboard.css'),
        ], 'woo-order-dashboard-assets');
    }
} 