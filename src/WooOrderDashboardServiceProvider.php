<?php

namespace Makiomar\WooOrderDashboard;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class WooOrderDashboardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/woo-order-dashboard.php', 'woo-order-dashboard'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'woo-order-dashboard');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        $this->publishes([
            __DIR__.'/../config/woo-order-dashboard.php' => config_path('woo-order-dashboard.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/woo-order-dashboard'),
        ], 'views');

        Blade::component('order.section', 'order-section');
        Blade::component('order.detail', 'order-detail');

        // Load assets
        $this->loadAssets();
    }

    protected function loadAssets()
    {
        // Load CSS
        $this->publishes([
            __DIR__.'/../resources/assets/css' => public_path('vendor/woo-order-dashboard/css'),
        ], 'assets');

        // Load JS
        $this->publishes([
            __DIR__.'/../resources/assets/js' => public_path('vendor/woo-order-dashboard/js'),
        ], 'assets');
    }
} 