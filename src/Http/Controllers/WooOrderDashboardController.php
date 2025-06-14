<?php

namespace Makiomar\WooOrderDashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Makiomar\WooOrderDashboard\Services\WooCommerceService;

class WooOrderDashboardController extends Controller
{
    protected $wooService;

    public function __construct(WooCommerceService $wooService)
    {
        $this->wooService = $wooService;
    }

    public function index()
    {
        return view('woo-order-dashboard::index');
    }

    public function orders(Request $request)
    {
        $filters = $this->validateFilters($request);
        
        $cacheKey = 'woo_orders_' . md5(json_encode($filters));
        
        $orders = Cache::remember($cacheKey, config('woo-order-dashboard.cache.ttl.orders'), function () use ($filters) {
            return $this->wooService->getOrders($filters);
        });

        return view('woo-order-dashboard::orders.index', compact('orders', 'filters'));
    }

    public function show($id)
    {
        $cacheKey = 'woo_order_' . $id;
        
        $order = Cache::remember($cacheKey, config('woo-order-dashboard.cache.ttl.order'), function () use ($id) {
            return $this->wooService->getOrder($id);
        });

        return view('woo-order-dashboard::orders.show', compact('order'));
    }

    protected function validateFilters(Request $request)
    {
        return $request->validate([
            'order_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string',
            'meta_key' => 'nullable|string|in:' . implode(',', config('woo-order-dashboard.meta_keys')),
            'meta_value' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:' . config('woo-order-dashboard.pagination.max_per_page'),
        ]);
    }
} 