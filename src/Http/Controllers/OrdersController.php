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
            // 1. Create the main order record
            $subtotal = collect($items)->sum(function ($item) {
                return ($item['price'] * $item['qty']);
            });

            $total = $subtotal - ($data['discount'] ?? 0) + ($data['shipping'] ?? 0) + ($data['taxes'] ?? 0);

            $order = Order::create([
                'status' => $data['order_status'] ?? 'wc-processing',
                'customer_id' => $data['customer_id'] ?? 0,
                'customer_note' => $data['customer_note'] ?? '',
                'total' => $total,
                'discount_total' => $data['discount'] ?? 0,
                'shipping_total' => $data['shipping'] ?? 0,
                'cart_tax' => $data['taxes'] ?? 0,
                'payment_method' => $data['payment_method'] ?? '',
                'date_created' => now(),
                'date_updated' => now(),
            ]);

            // 2. Create order items and their meta
            foreach ($items as $itemData) {
                $orderItem = $order->items()->create([
                    'order_item_name' => $itemData['name'],
                    'order_item_type' => 'line_item',
                ]);

                $orderItem->meta()->createMany([
                    ['meta_key' => '_product_id', 'meta_value' => $itemData['product_id']],
                    ['meta_key' => '_variation_id', 'meta_value' => $itemData['variation_id'] ?? 0],
                    ['meta_key' => '_quantity', 'meta_value' => $itemData['qty']],
                    ['meta_key' => '_line_subtotal', 'meta_value' => $itemData['price'] * $itemData['qty']],
                    ['meta_key' => '_line_total', 'meta_value' => $itemData['price'] * $itemData['qty']],
                ]);

                if (!empty($itemData['attributes'])) {
                    foreach($itemData['attributes'] as $key => $value) {
                        $orderItem->meta()->create([
                            'meta_key' => str_replace('attribute_', '', $key), 
                            'meta_value' => $value
                        ]);
                    }
                }
            }

            // 3. Create private note if it exists
            if (!empty($data['private_note'])) {
                // This is a simplified version. A full implementation might involve a different table or logic
                $order->items()->create([
                    'order_item_name' => $data['private_note'],
                    'order_item_type' => 'order_note',
                ]);
            }

            DB::commit();

            return redirect()->route('woo.orders.index')->with('success', 'Order created successfully. Order ID: ' . $order->id);

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
                // Use the Order model to delete orders. This assumes HPOS table.
                $deletedCount = Order::whereIn('id', $orderIds)->delete();

                // Order items and meta will be deleted via cascading constraints if they exist,
                // otherwise we should delete them manually.
                OrderItem::whereIn('order_id', $orderIds)->delete();
                // This is a bit of a simplification, as we'd need to get the order_item_ids first.
                // A better approach would be to loop, but for bulk this is faster.
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