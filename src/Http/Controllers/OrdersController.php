<?php

namespace Makiomar\WooOrderDashboard\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrdersController extends Controller
{
    public function create()
    {
        return view('woo-order-dashboard::orders.create');
    }

    public function productsSearch(Request $request)
    {
        $query = $request->input('q');
        $products = app(\Makiomar\WooOrderDashboard\Services\WooCommerceService::class)->getProducts($query);
        return response()->json($products);
    }

    public function customersSearch(Request $request)
    {
        $query = $request->input('q');
        $customers = app(\Makiomar\WooOrderDashboard\Services\WooCommerceService::class)->getCustomers($query);
        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_items' => 'required|string',
            'customer_id' => 'nullable|integer',
            'customer_note' => 'nullable|string',
            'private_note' => 'nullable|string',
            'order_date' => 'nullable|date',
            'order_hour' => 'nullable|string',
            'order_minute' => 'nullable|string',
            'order_status' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'shipping' => 'nullable|numeric',
            'taxes' => 'nullable|numeric',
        ]);
        
        try {
            $data['order_items'] = json_decode($data['order_items'], true);
            $orderId = app(\Makiomar\WooOrderDashboard\Services\WooCommerceService::class)->createOrder($data);
            
            return redirect()->route('woo.orders')->with('success', 'Order created successfully. Order ID: ' . $orderId);
        } catch (\Exception $e) {
            return back()->with('error', 'Order creation failed: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer|exists:woocommerce.posts,ID'
        ]);

        $orderIds = $request->input('order_ids');
        $result = app(\Makiomar\WooOrderDashboard\Services\WooCommerceService::class)->bulkDeleteOrders($orderIds);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }
} 