<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WooOrderDashboardController;

Route::middleware(config('woo-order-dashboard.routes.middleware', ['web', 'auth']))
    ->prefix(config('woo-order-dashboard.routes.prefix', 'woo'))
    ->group(function () {
        Route::get('/', [WooOrderDashboardController::class, 'index'])->name('woo.dashboard');
        Route::get('/orders', [WooOrderDashboardController::class, 'orders'])->name('woo.orders');
        Route::get('/orders/{id}', [WooOrderDashboardController::class, 'show'])->name('woo.orders.show');
    }); 