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
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'woo-order-dashboard');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Blade::component('order.section', 'order-section');
        Blade::component('order.detail', 'order-detail');

        // Publish config file
        $this->publishes([
            __DIR__.'/../config/woo-order-dashboard.php' => config_path('woo-order-dashboard.php'),
        ], 'woo-order-dashboard-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/woo-order-dashboard'),
        ], 'woo-order-dashboard-views');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/woo-order-dashboard'),
        ], 'woo-order-dashboard-assets');

        // Load assets
        $this->loadAssets();
    }

    protected function loadAssets()
    {
        // Register CSS
        $this->app['view']->share('wooOrderDashboardStyles', asset('vendor/woo-order-dashboard/css/app.css'));

        // Register JavaScript
        $this->app['view']->share('wooOrderDashboardScripts', asset('vendor/woo-order-dashboard/js/app.js'));
    }
} 