<?php

use Illuminate\Support\Facades\Route;
use YourVendor\WooOrderDashboard\Http\Controllers\WooOrderDashboardController;

Route::middleware(config('woo-order-dashboard.routes.middleware'))
    ->prefix(config('woo-order-dashboard.routes.prefix'))
    ->group(function () {
        Route::get('/', [WooOrderDashboardController::class, 'index'])->name('woo.dashboard');
        Route::get('/orders', [WooOrderDashboardController::class, 'orders'])->name('woo.orders');
        Route::get('/orders/{id}', [WooOrderDashboardController::class, 'show'])->name('woo.orders.show');
    }); 