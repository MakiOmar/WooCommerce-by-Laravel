<?php

use Illuminate\Support\Facades\Route;
use Makiomar\WooOrderDashboard\Http\Controllers\WooOrderDashboardController;
use Makiomar\WooOrderDashboard\Http\Controllers\OrdersController;

Route::group(['middleware' => 'auth:admin'], function() {
    Route::get('/woo-orders', [WooOrderDashboardController::class, 'index'])->name('woo.dashboard');
    Route::get('/orders', [WooOrderDashboardController::class, 'orders'])->name('woo.orders');
    Route::get('/orders/{id}', [WooOrderDashboardController::class, 'show'])->name('woo.orders.show');
    Route::get('/orders/create', [OrdersController::class, 'create'])->name('orders.create');
    Route::get('/orders/products/search', [OrdersController::class, 'productsSearch'])->name('orders.products.search');
    Route::get('/orders/customers/search', [OrdersController::class, 'customersSearch'])->name('orders.customers.search');
    Route::post('/orders', [OrdersController::class, 'store'])->name('orders.store');
    Route::post('/orders/bulk-delete', [OrdersController::class, 'bulkDelete'])->name('woo.orders.bulk-delete');
}); 