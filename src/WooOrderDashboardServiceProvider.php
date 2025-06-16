<?php

namespace Makiomar\WooOrderDashboard;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'woo-order-dashboard');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        $this->publishes([
            __DIR__.'/../config/woo-order-dashboard.php' => config_path('woo-order-dashboard.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/woo-order-dashboard'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/woo-order-dashboard'),
        ], 'assets');

        // Don't register routes by default
        // $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        Blade::component('order.section', 'order-section');
        Blade::component('order.detail', 'order-detail');

        // Load assets
        $this->loadAssets();

        // Register WooCommerce Order Dashboard routes
        $this->app->make(\Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider::class)
            ->registerAdminRoutes();
    }

    /**
     * Register the package routes in admin context
     *
     * @param string|null $prefix Optional route prefix. If null, uses config value or defaults to empty string
     * @return void
     */
    public function registerAdminRoutes($prefix = null)
    {
        $prefix = $prefix ?? config('woo-order-dashboard.route_prefix', '');

        Route::group([
            'middleware' => 'auth:admin',
            'prefix' => $prefix
        ], function() {
            Route::get('/woo-orders', [WooOrderDashboardController::class, 'index'])->name('woo.dashboard');
            Route::get('/orders', [WooOrderDashboardController::class, 'orders'])->name('woo.orders');
            Route::get('/orders/{id}', [WooOrderDashboardController::class, 'show'])->name('woo.orders.show');
        });
    }

    protected function loadAssets()
    {
        // Register CSS
        $this->app['view']->share('wooOrderDashboardStyles', asset('vendor/woo-order-dashboard/css/app.css'));

        // Register JavaScript
        $this->app['view']->share('wooOrderDashboardScripts', asset('vendor/woo-order-dashboard/js/app.js'));
    }
} 