<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class WooOrderDashboardServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../resources/css/woo-order-dashboard.css' => public_path('css/woo-order-dashboard.css'),
        ], 'woo-order-dashboard-assets');
    }
} 