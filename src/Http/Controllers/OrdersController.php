<?php

namespace Makiomar\WooOrderDashboard\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Makiomar\WooOrderDashboard\Models\Product;
use Makiomar\WooOrderDashboard\Models\Order;
use Makiomar\WooOrderDashboard\Models\OrderItem;
use Makiomar\WooOrderDashboard\Models\OrderItemMeta;
use Makiomar\WooOrderDashboard\Models\Customer;
use Makiomar\WooOrderDashboard\Models\PostMeta;

class OrdersController extends Controller
{
    public function create()
    {
        return view('woo-order-dashboard::orders.create');
    }

    public function searchProducts(Request $request)
    {
        $q = $request->get('q');
        $prefix = DB::getDatabaseName() . '.';
        
        $products = Product::with('meta')
            ->where('post_status', 'publish')
            ->where(function ($query) use ($q) {
                $query->where('post_title', 'LIKE', "%{$q}%")
                      ->orWhereHas('meta', function ($subQuery) use ($q) {
                          $subQuery->where('meta_key', '_sku')->where('meta_value', 'LIKE', "%{$q}%");
                      });
            })
            ->limit(20)
            ->get();

        $results = $products->map(function ($product) {
            $meta = $product->meta->pluck('meta_value', 'meta_key');
            
            $attributes = [];
            if ($product->post_type === 'product_variation') {
                foreach ($meta as $key => $value) {
                    if (strpos($key, 'attribute_') === 0) {
                        $attributes[$key] = $value;
                    }
                }
            }

            return [
                'product_id' => $product->post_type === 'product_variation' ? $product->post_parent : $product->ID,
                'variation_id' => $product->post_type === 'product_variation' ? $product->ID : 0,
                'name' => $product->post_title,
                'sku' => $meta->get('_sku'),
                'price' => $meta->get('_price'),
                'attributes' => $attributes,
            ];
        });

        return response()->json($results);
    }

    public function customersSearch(Request $request)
    {
        $q = $request->get('q');
        
        $customers = Customer::where('user_email', 'LIKE', "%{$q}%")
            ->orWhere('display_name', 'LIKE', "%{$q}%")
            ->orWhereHas('meta', function ($query) use ($q) {
                $query->whereIn('meta_key', ['first_name', 'last_name'])
                      ->where('meta_value', 'LIKE', "%{$q}%");
            })
            ->limit(10)
            ->get();

        $results = $customers->map(function ($customer) {
            $firstName = $customer->meta->where('meta_key', 'first_name')->first()->meta_value ?? '';
            $lastName = $customer->meta->where('meta_key', 'last_name')->first()->meta_value ?? '';
            return [
                'id' => $customer->ID,
                'name' => trim($firstName . ' ' . $lastName),
                'email' => $customer->user_email,
            ];
        });

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_items' => 'required|string',
            'customer_id' => 'nullable|integer',
            'customer_note' => 'nullable|string',
            'private_note' => 'nullable|string',
            'order_status' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'shipping' => 'nullable|numeric',
            'taxes' => 'nullable|numeric',
        ]);

        $items = json_decode($data['order_items'], true);

        DB::beginTransaction();
        try {
            // 1. Create the main order record in 'posts'
            $subtotal = collect($items)->sum(function ($item) {
                return ($item['price'] * $item['qty']);
            });
            $total = $subtotal - ($data['discount'] ?? 0) + ($data['shipping'] ?? 0) + ($data['taxes'] ?? 0);

            $order = Order::create([
                'post_type' => 'shop_order',
                'post_status' => $data['order_status'] ?? 'wc-processing',
                'ping_status' => 'closed',
                'post_author' => auth()->id() ?? 1,
                'post_title' => 'Order &ndash; ' . now()->format('F j, Y @ h:i A'),
                'post_content' => '',
                'post_excerpt' => $data['customer_note'] ?? '',
                'post_date' => now(),
                'post_modified' => now(),
            ]);

            // 2. Add meta data to the order
            $order->meta()->createMany([
                ['_customer_user', $data['customer_id'] ?? 0],
                ['_order_total', $total],
                ['_order_currency', 'USD'], // Consider making this dynamic
                ['_payment_method', $data['payment_method'] ?? ''],
                ['_cart_discount', $data['discount'] ?? 0],
            ]);

            // 3. Create order items and their meta
            foreach ($items as $itemData) {
                $orderItem = $order->items()->create([
                    'order_item_name' => $itemData['name'],
                    'order_item_type' => 'line_item',
                    'order_id' => $order->ID,
                ]);

                $orderItem->meta()->createMany([
                    ['_product_id', $itemData['product_id']],
                    ['_variation_id', $itemData['variation_id'] ?? 0],
                    ['_qty', $itemData['qty']],
                    ['_line_subtotal', $itemData['price'] * $itemData['qty']],
                    ['_line_total', $itemData['price'] * $itemData['qty']],
                ]);
            }
            
            DB::commit();

            return redirect()->route('woo.orders.index')->with('success', 'Order created successfully. Order ID: ' . $order->ID);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Order creation failed: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
        ]);

        $orderIds = $request->input('order_ids');
        
        try {
            DB::transaction(function () use ($orderIds) {
                // Delete from posts table
                Order::whereIn('ID', $orderIds)->delete();

                // Delete associated postmeta
                PostMeta::whereIn('post_id', $orderIds)->delete();

                // Delete order items and their meta
                $orderItemIds = OrderItem::whereIn('order_id', $orderIds)->pluck('order_item_id');
                OrderItem::whereIn('order_id', $orderIds)->delete();
                OrderItemMeta::whereIn('order_item_id', $orderItemIds)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Successfully deleted ' . count($orderIds) . ' orders.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete orders: ' . $e->getMessage()
            ], 500);
        }
    }
} 