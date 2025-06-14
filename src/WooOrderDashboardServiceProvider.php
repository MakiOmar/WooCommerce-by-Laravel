<?php

namespace YourVendor\WooOrderDashboard;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class WooOrderDashboardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/woo-order-dashboard.php', 'woo-order-dashboard'
        );
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'woo-order-dashboard');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/woo-order-dashboard.php' => config_path('woo-order-dashboard.php'),
            __DIR__.'/../resources/views' => resource_path('views/vendor/woo-order-dashboard'),
            __DIR__.'/../resources/assets' => public_path('vendor/woo-order-dashboard'),
        ], 'woo-order-dashboard');

        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/woo-order-dashboard'),
        ], 'woo-order-dashboard-assets');
    }
} 