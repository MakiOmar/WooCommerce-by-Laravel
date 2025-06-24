<?php

namespace Makiomar\WooOrderDashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Makiomar\WooOrderDashboard\Models\Order;
use Makiomar\WooOrderDashboard\Helpers\Orders\StatusHelper;
use Makiomar\WooOrderDashboard\Helpers\MetaHelper;
use Illuminate\Support\Facades\Log;

class WooOrderDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validateFilters($request);
        
        $cacheKey = 'woo_orders_' . md5(json_encode($filters));
        
        $orders = Cache::remember($cacheKey, config('woo-order-dashboard.cache.ttl.orders', 60), function () use ($filters) {
            $query = Order::with(['meta', 'items.meta']);

            if (!empty($filters['order_id'])) {
                $query->where('ID', $filters['order_id']);
            }
            if (!empty($filters['status'])) {
                $query->where('post_status', StatusHelper::getStatusWithPrefix($filters['status']));
            }
            if (!empty($filters['start_date'])) {
                $query->whereDate('post_date_gmt', '>=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $query->whereDate('post_date_gmt', '<=', $filters['end_date']);
            }
            
            // Meta key and value filtering
            if (!empty($filters['meta_key']) || !empty($filters['meta_value'])) {
                $query->whereHas('meta', function ($metaQuery) use ($filters) {
                    if (!empty($filters['meta_key'])) {
                        $metaQuery->where('meta_key', 'LIKE', '%' . $filters['meta_key'] . '%');
                    }
                    if (!empty($filters['meta_value'])) {
                        $metaQuery->where('meta_value', 'LIKE', '%' . $filters['meta_value'] . '%');
                    }
                });
            }
            
            return $query->orderBy('post_date_gmt', 'desc')
                         ->paginate($filters['per_page'] ?? config('woo-order-dashboard.pagination.per_page', 15));
        });

        // Get dynamic order statuses from the database
        $orderStatuses = StatusHelper::getAllStatuses();
        
        // Get meta key categories for the dropdown
        $metaKeyCategories = MetaHelper::getMetaKeyCategories();
        $availableMetaKeys = MetaHelper::getAvailableMetaKeys();

        return view('woo-order-dashboard::index', compact('orders', 'filters', 'orderStatuses', 'metaKeyCategories', 'availableMetaKeys'));
    }

    public function show($id)
    {
        $cacheKey = 'woo_order_' . $id;
        
        // Only load data needed for first tab initially
        $order = Cache::remember($cacheKey, config('woo-order-dashboard.cache.ttl.order', 60), function () use ($id) {
            return Order::with(['meta', 'items.meta'])->find($id);
        });

        if (!$order) {
            return redirect()->route('orders.index')->with('error', 'Order not found.');
        }

        // Get dynamic order statuses from the database
        $orderStatuses = StatusHelper::getAllStatuses();

        return view('woo-order-dashboard::orders.show', compact('order', 'orderStatuses'));
    }

    /**
     * Get tab content via AJAX using partials
     */
    public function getTabContent($id, Request $request)
    {
        $tab = $request->get('tab');
        
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        switch ($tab) {
            case 'customer-info':
                // Load customer data and return partial
                $order->load(['meta']);
                $html = view('woo-order-dashboard::partials.order-customer-info', compact('order'))->render();
                break;
                
            case 'order-notes':
                // Load comments/notes data and return partial
                $order->load(['comments']);
                $html = view('woo-order-dashboard::partials.order-notes', compact('order'))->render();
                break;
                
            default:
                return response()->json(['error' => 'Invalid tab'], 400);
        }

        return response()->json(['html' => $html]);
    }

    protected function validateFilters(Request $request)
    {
        return $request->validate([
            'order_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string',
            'meta_key' => 'nullable|string|max:255',
            'meta_value' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
    }
} 