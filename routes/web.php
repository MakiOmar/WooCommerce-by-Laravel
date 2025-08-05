<?php

use Illuminate\Support\Facades\Route;
use Makiomar\WooOrderDashboard\Http\Controllers\WooOrderDashboardController;
use Makiomar\WooOrderDashboard\Http\Controllers\OrdersController;

Route::group(['middleware' => ['web', 'auth:admin', 'woo.language']], function() {

    // Main Order Dashboard
    Route::get('/orders', [WooOrderDashboardController::class, 'index'])->name('orders.index');
    
    // Loading Demo
    Route::get('/loading-demo', function() {
        return view('woo-order-dashboard::loading-demo');
    })->name('loading.demo');
    
    // Order Creation & Actions (specific routes first)
    Route::get('/orders/create', [OrdersController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrdersController::class, 'store'])->name('orders.store');
    Route::post('/orders/bulk-delete', [OrdersController::class, 'bulkDelete'])->name('orders.bulk-delete');
    
    // Parameterized routes (after specific routes)
    Route::get('/orders/{id}', [WooOrderDashboardController::class, 'show'])->name('orders.show');
    Route::get('/orders/{id}/tab-content', [WooOrderDashboardController::class, 'getTabContent'])->name('orders.tab-content');
    Route::put('/orders/{id}', [OrdersController::class, 'update'])->name('orders.update');
    Route::patch('/orders/{id}/status', [OrdersController::class, 'updateStatus'])->name('orders.update-status');

    // Ajax Search Routes
    Route::get('/products/search', [OrdersController::class, 'searchProducts'])->name('products.search');
    Route::get('/customers/search', [OrdersController::class, 'customersSearch'])->name('customers.search');
    Route::post('/shipping/methods', [OrdersController::class, 'getShippingMethods'])->name('shipping.methods');
    
    // RedBox Pickup Routes
    Route::get('/redbox/points', [OrdersController::class, 'getRedBoxPoints'])->name('redbox.points');
    Route::get('/redbox/map-token', [OrdersController::class, 'getRedBoxMapToken'])->name('redbox.map-token');
    
    // Language test route
    Route::get('/test-language', function() {
        return response()->json([
            'current_language' => \Makiomar\WooOrderDashboard\Helpers\LanguageHelper::getCurrentLanguage(),
            'is_rtl' => \Makiomar\WooOrderDashboard\Helpers\LanguageHelper::isRTL(),
            'session_language' => session('woo_language'),
            'app_locale' => app()->getLocale(),
            'config_default' => config('woo-order-dashboard.language.default'),
            'available_languages' => config('woo-order-dashboard.language.available'),
        ]);
    })->name('test.language');
}); 