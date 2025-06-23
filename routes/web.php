<?php

use Illuminate\Support\Facades\Route;
use Makiomar\WooOrderDashboard\Http\Controllers\WooOrderDashboardController;
use Makiomar\WooOrderDashboard\Http\Controllers\OrdersController;

Route::group(['middleware' => ['web', 'auth:admin']], function() {

    // Main Order Dashboard
    Route::get('/orders', [WooOrderDashboardController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [WooOrderDashboardController::class, 'show'])->name('orders.show');
    
    // Order Creation & Actions
    Route::get('/orders/create', [OrdersController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrdersController::class, 'store'])->name('orders.store');

    // Ajax Search Routes
    Route::get('/products/search', [OrdersController::class, 'searchProducts'])->name('products.search');
    Route::get('/customers/search', [OrdersController::class, 'customersSearch'])->name('customers.search');

    // Bulk Actions
    Route::post('/orders/bulk-delete', [OrdersController::class, 'bulkDelete'])->name('orders.bulk-delete');
}); 