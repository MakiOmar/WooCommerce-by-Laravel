<?php

namespace Makiomar\WooOrderDashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Makiomar\WooOrderDashboard\Models\Order;
use Illuminate\Support\Facades\Log;

class WooOrderDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validateFilters($request);
        
        $cacheKey = 'woo_orders_' . md5(json_encode($filters));
        
        $orders = Cache::remember($cacheKey, config('woo-order-dashboard.cache.ttl.orders', 60), function () use ($filters) {
            $query = Order::with('items.meta');

            if (!empty($filters['order_id'])) {
                $query->where('id', $filters['order_id']);
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['start_date'])) {
                $query->whereDate('date_created', '>=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $query->whereDate('date_created', '<=', $filters['end_date']);
            }
            
            return $query->orderBy('date_created', 'desc')
                         ->paginate($filters['per_page'] ?? config('woo-order-dashboard.pagination.per_page', 15));
        });

        return view('woo-order-dashboard::index', compact('orders', 'filters'));
    }

    public function show($id)
    {
        $cacheKey = 'woo_order_' . $id;
        
        $order = Cache::remember($cacheKey, config('woo-order-dashboard.cache.ttl.order', 60), function () use ($id) {
            return Order::with(['items.meta', 'customer.meta'])->find($id);
        });

        if (!$order) {
            return redirect()->route('woo.orders.index')->with('error', 'Order not found.');
        }

        return view('woo-order-dashboard::orders.show', compact('order'));
    }

    protected function validateFilters(Request $request)
    {
        return $request->validate([
            'order_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1',
        ]);
    }
} 