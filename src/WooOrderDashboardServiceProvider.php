<?php

namespace Makiomar\WooOrderDashboard;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

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

        // Register cache driver configuration
        $this->registerCacheConfiguration();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/woo-order-dashboard.php' => config_path('woo-order-dashboard.php'),
        ], 'config');

        // Publish optional migrations
        $this->publishes([
            __DIR__.'/../database/migrations/optional' => database_path('migrations/optional'),
        ], 'migrations');

        // Load only required migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/required');

        // Register routes if enabled
        $this->registerRoutes();

        // Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'woo-order-dashboard');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/woo-order-dashboard'),
        ], 'views');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/woo-order-dashboard'),
        ], 'assets');

        // Publish all assets with main tag
        $this->publishes([
            __DIR__.'/../config/woo-order-dashboard.php' => config_path('woo-order-dashboard.php'),
            __DIR__.'/../resources/views' => resource_path('views/vendor/woo-order-dashboard'),
            __DIR__.'/../resources/assets' => public_path('vendor/woo-order-dashboard'),
            __DIR__.'/../database/migrations/optional' => database_path('migrations/optional'),
        ], 'woo-order-dashboard');

        Blade::component('order.section', 'order-section');
        Blade::component('order.detail', 'order-detail');

        // Load assets
        $this->loadAssets();

        // Configure performance optimizations
        $this->configurePerformanceOptimizations();
    }

    /**
     * Register routes if enabled in configuration
     *
     * @return void
     */
    protected function registerRoutes()
    {
        // Check if routes are enabled
        if (!config('woo-order-dashboard.routes.enabled', false)) {
            return;
        }

        // Get route configuration
        $prefix = config('woo-order-dashboard.routes.prefix', 'woo-dashboard');
        $middleware = config('woo-order-dashboard.routes.middleware', 'web');
        $namePrefix = config('woo-order-dashboard.routes.name_prefix', 'woo.');

        Route::group([
            'prefix' => $prefix,
            'middleware' => $middleware,
            'as' => $namePrefix,
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        // Publish routes file for customization
        $this->publishes([
            __DIR__.'/../routes/web.php' => base_path('routes/woo-dashboard.php'),
        ], 'routes');
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

    /**
     * Register cache configuration.
     *
     * @return void
     */
    protected function registerCacheConfiguration()
    {
        $this->app->singleton('woo-order-dashboard.cache', function ($app) {
            return Cache::store(config('woo-order-dashboard.cache.store', 'file'));
        });
    }

    /**
     * Configure performance optimizations.
     *
     * @return void
     */
    protected function configurePerformanceOptimizations()
    {
        // Configure query log (disable in production)
        if (!$this->app->environment('production')) {
            Config::set('database.connections.woocommerce.logging', true);
        }

        // Configure database connection pooling
        Config::set('database.connections.woocommerce.sticky', true);
        
        // Configure query cache
        if (config('woo-order-dashboard.cache.query_cache_enabled', true)) {
            Config::set('database.connections.woocommerce.query_cache', [
                'store' => config('woo-order-dashboard.cache.store', 'file'),
                'ttl' => config('woo-order-dashboard.cache.query_cache_ttl', 3600),
            ]);
        }

        // Configure connection pool
        if (config('woo-order-dashboard.database.use_connection_pool', true)) {
            Config::set('database.connections.woocommerce.pool', [
                'min' => config('woo-order-dashboard.database.min_connections', 2),
                'max' => config('woo-order-dashboard.database.max_connections', 10),
            ]);
        }
    }
} 