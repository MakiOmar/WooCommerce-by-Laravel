<?php

use Illuminate\Support\Facades\Route;
use Makiomar\WooOrderDashboard\Http\Controllers\WooOrderDashboardController;

Route::group(['middleware' => 'auth:admin'], function() {
    Route::get('/woo-orders', [WooOrderDashboardController::class, 'index'])->name('woo.dashboard');
    Route::get('/orders', [WooOrderDashboardController::class, 'orders'])->name('woo.orders');
    Route::get('/orders/{id}', [WooOrderDashboardController::class, 'show'])->name('woo.orders.show');
}); 