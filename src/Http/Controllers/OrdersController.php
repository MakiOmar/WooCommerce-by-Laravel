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
use Makiomar\WooOrderDashboard\Helpers\CacheHelper;
use Makiomar\WooOrderDashboard\Models\Comment;

class OrdersController extends Controller
{
    public function create()
    {
        $prefix = DB::getDatabaseName() . '.';
        return view('woo-order-dashboard::orders.create', compact('prefix'));
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
        // Debug: Log the incoming request data
        \Log::info('Order creation request:', $request->all());
        
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

        // Debug: Log the validated data
        \Log::info('Validated order data:', $data);

        $items = json_decode($data['order_items'], true);
        
        // Debug: Log the decoded items
        \Log::info('Decoded order items:', $items);

        // Validate that we have items
        if (empty($items)) {
            return back()->with('error', 'No order items found. Please add at least one product to the order.');
        }

        DB::connection('woocommerce')->beginTransaction();
        try {
            // Debug: Log the order data we're about to create
            $orderData = [
                'post_type' => 'shop_order',
                'post_status' => $data['order_status'] ?? 'wc-processing',
                'ping_status' => 'closed',
                'post_author' => auth()->id() ?? 1,
                'post_title' => 'Order &ndash; ' . now()->format('F j, Y @ h:i A'),
                'post_content' => '',
                'post_excerpt' => $data['customer_note'] ?? '',
                'post_date' => now(),
                'post_date_gmt' => now()->utc(),
                'post_modified' => now(),
                'post_modified_gmt' => now()->utc(),
                'to_ping' => '',
                'pinged' => '',
                'post_content_filtered' => '',
                'post_parent' => 0,
                'menu_order' => 0,
                'comment_status' => 'closed',
                'guid' => '',
            ];
            
            \Log::info('Creating order with data:', $orderData);
            
            // 1. Create the main order record in 'posts'
            $subtotal = collect($items)->sum(function ($item) {
                return ($item['price'] * $item['qty']);
            });
            $total = $subtotal - ($data['discount'] ?? 0) + ($data['shipping'] ?? 0) + ($data['taxes'] ?? 0);
            
            \Log::info('Calculated totals:', ['subtotal' => $subtotal, 'total' => $total]);

            $order = Order::create($orderData);
            
            \Log::info('Order created successfully:', ['order_id' => $order->ID]);

            // 2. Add meta data to the order
            $metaData = [
                ['_customer_user', $data['customer_id'] ?? '0'],
                ['_order_total', $total],
                ['_order_currency', 'USD'], // Consider making this dynamic from config
                ['_payment_method', $data['payment_method'] ?? ''],
                ['_payment_method_title', $data['payment_method'] ? ucwords(str_replace('_', ' ', $data['payment_method'])) : ''],
                ['_cart_discount', $data['discount'] ?? '0'],
                ['_order_shipping', $data['shipping'] ?? '0'],
                ['_order_tax', $data['taxes'] ?? '0'],
                // Add billing and shipping meta fields
                ['_billing_first_name', ''],
                ['_billing_last_name', ''],
                ['_billing_email', ''],
                ['_billing_phone', ''],
                ['_billing_address_1', ''],
                ['_billing_address_2', ''],
                ['_billing_city', ''],
                ['_billing_state', ''],
                ['_billing_postcode', ''],
                ['_billing_country', ''],
                ['_shipping_first_name', ''],
                ['_shipping_last_name', ''],
                ['_shipping_address_1', ''],
                ['_shipping_address_2', ''],
                ['_shipping_city', ''],
                ['_shipping_state', ''],
                ['_shipping_postcode', ''],
                ['_shipping_country', ''],
            ];

            \Log::info('Creating meta data:', $metaData);

            foreach ($metaData as $meta) {
                $postMeta = PostMeta::create([
                    'post_id' => $order->ID,
                    'meta_key' => $meta[0],
                    'meta_value' => $meta[1],
                ]);
                \Log::info('Created meta:', ['key' => $meta[0], 'value' => $meta[1], 'id' => $postMeta->meta_id]);
            }

            // 3. Create order items and their meta
            \Log::info('Creating order items:', $items);
            
            foreach ($items as $itemData) {
                \Log::info('Creating order item:', $itemData);
                
                $orderItem = OrderItem::create([
                    'order_item_name' => $itemData['name'],
                    'order_item_type' => 'line_item',
                    'order_id' => $order->ID,
                ]);
                
                \Log::info('Created order item:', ['item_id' => $orderItem->order_item_id]);

                $orderItemMeta = [
                    ['_product_id', $itemData['product_id']],
                    ['_variation_id', $itemData['variation_id'] ?? 0],
                    ['_qty', $itemData['qty']],
                    ['_tax_class', ''],
                    ['_line_subtotal', $itemData['price']],
                    ['_line_subtotal_tax', '0'],
                    ['_line_total', $itemData['price'] * $itemData['qty']],
                    ['_line_tax', '0'],
                    ['_line_tax_data', 'a:2:{s:5:"total";a:0:{}s:8:"subtotal";a:0:{}}'],
                ];
                
                foreach ($orderItemMeta as $meta) {
                    $itemMeta = OrderItemMeta::create([
                        'order_item_id' => $orderItem->order_item_id,
                        'meta_key' => $meta[0],
                        'meta_value' => $meta[1],
                    ]);
                    \Log::info('Created item meta:', ['key' => $meta[0], 'value' => $meta[1], 'id' => $itemMeta->meta_id]);
                }
            }
            
            // 4. Add WooCommerce lookup table entries
            \Log::info('Adding WooCommerce lookup table entries');
            
            try {
                // Add to wc_order_stats table
                DB::connection('woocommerce')->table('wc_order_stats')->insert([
                    'order_id' => $order->ID,
                    'parent_id' => 0,
                    'date_created' => now(),
                    'date_created_gmt' => now()->utc(),
                    'date_paid' => now(),
                    'date_updated' => now(),
                    'num_items_sold' => collect($items)->sum('qty'),
                    'total_sales' => $total,
                    'tax_total' => $data['taxes'] ?? 0,
                    'shipping_total' => $data['shipping'] ?? 0,
                    'net_total' => $total - ($data['taxes'] ?? 0) - ($data['shipping'] ?? 0),
                    'returning_customer' => 0,
                    'status' => $data['order_status'] ?? 'wc-processing',
                ]);
                
                \Log::info('Added to wc_order_stats table');
                
                // Add to wc_order_product_lookup table for each product
                foreach ($items as $itemData) {
                    DB::connection('woocommerce')->table('wc_order_product_lookup')->insert([
                        'order_id' => $order->ID,
                        'product_id' => $itemData['product_id'],
                        'variation_id' => $itemData['variation_id'] ?? 0,
                        'customer_id' => $data['customer_id'] ?? 0,
                        'date_created' => now(),
                        'product_qty' => $itemData['qty'],
                        'product_net_revenue' => ($itemData['price'] * $itemData['qty']) - (($data['taxes'] ?? 0) / count($items)),
                        'product_gross_revenue' => $itemData['price'] * $itemData['qty'],
                        'coupon_amount' => 0,
                        'tax_amount' => ($data['taxes'] ?? 0) / count($items),
                        'shipping_amount' => ($data['shipping'] ?? 0) / count($items),
                        'shipping_tax_amount' => 0,
                    ]);
                }
                
                \Log::info('Added to wc_order_product_lookup table');
                
                // Add to wc_order_tax_lookup table
                if (($data['taxes'] ?? 0) > 0) {
                    DB::connection('woocommerce')->table('wc_order_tax_lookup')->insert([
                        'order_id' => $order->ID,
                        'tax_rate_id' => 1, // Default tax rate
                        'date_created' => now(),
                        'shipping_tax' => 0,
                        'order_tax' => $data['taxes'] ?? 0,
                        'total_tax' => $data['taxes'] ?? 0,
                    ]);
                }
                
                // Add to wc_order_coupon_lookup table (empty for now)
                // This table is used for coupon tracking
                
                // Add to wc_order_operational_data table (WooCommerce 7.0+)
                try {
                    DB::connection('woocommerce')->table('wc_order_operational_data')->insert([
                        'order_id' => $order->ID,
                        'created_via' => 'admin',
                        'woocommerce_version' => '7.0.0',
                        'prices_include_tax' => 0,
                        'discount_total' => $data['discount'] ?? 0,
                        'discount_tax' => 0,
                        'shipping_total' => $data['shipping'] ?? 0,
                        'shipping_tax' => 0,
                        'cart_tax' => $data['taxes'] ?? 0,
                        'total' => $total,
                        'total_tax' => $data['taxes'] ?? 0,
                        'customer_id' => $data['customer_id'] ?? 0,
                        'order_key' => 'wc_' . uniqid(),
                        'billing_email' => '',
                        'billing_first_name' => '',
                        'billing_last_name' => '',
                        'billing_phone' => '',
                        'billing_address_1' => '',
                        'billing_address_2' => '',
                        'billing_city' => '',
                        'billing_state' => '',
                        'billing_postcode' => '',
                        'billing_country' => '',
                        'shipping_first_name' => '',
                        'shipping_last_name' => '',
                        'shipping_address_1' => '',
                        'shipping_address_2' => '',
                        'shipping_city' => '',
                        'shipping_state' => '',
                        'shipping_postcode' => '',
                        'shipping_country' => '',
                        'payment_method' => $data['payment_method'] ?? '',
                        'payment_method_title' => $data['payment_method'] ? ucwords(str_replace('_', ' ', $data['payment_method'])) : '',
                        'transaction_id' => '',
                        'customer_ip_address' => request()->ip(),
                        'customer_user_agent' => request()->userAgent(),
                        'customer_note' => $data['customer_note'] ?? '',
                        'date_completed' => null,
                        'date_paid' => now(),
                        'cart_hash' => '',
                    ]);
                    
                    \Log::info('Added to wc_order_operational_data table');
                } catch (\Exception $e) {
                    \Log::info('wc_order_operational_data table not available or failed: ' . $e->getMessage());
                }
                
            } catch (\Exception $e) {
                \Log::warning('Failed to add WooCommerce lookup table entries: ' . $e->getMessage());
                // Continue with order creation even if lookup tables fail
            }
            
            DB::connection('woocommerce')->commit();
            \Log::info('Database transaction committed successfully');

            // Clear cache after successful order creation
            CacheHelper::clearCacheOnOrderCreate();
            \Log::info('Cache cleared successfully');

            // Clear WooCommerce cache to ensure order appears in admin
            try {
                // Clear WooCommerce transients
                DB::connection('woocommerce')->table('options')->where('option_name', 'like', '_transient_wc_order_%')->delete();
                DB::connection('woocommerce')->table('options')->where('option_name', 'like', '_transient_timeout_wc_order_%')->delete();
                
                // Clear WooCommerce cache
                DB::connection('woocommerce')->table('options')->where('option_name', 'like', '_transient_wc_%')->delete();
                DB::connection('woocommerce')->table('options')->where('option_name', 'like', '_transient_timeout_wc_%')->delete();
                
                \Log::info('WooCommerce cache cleared successfully');
            } catch (\Exception $e) {
                \Log::warning('Failed to clear WooCommerce cache: ' . $e->getMessage());
            }

            return redirect()->route('orders.index')->with('success', 'Order created successfully. Order ID: ' . $order->ID);

        } catch (\Exception $e) {
            DB::connection('woocommerce')->rollBack();
            \Log::error('Order creation failed with exception:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Order creation failed: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
        ]);

        $orderIds = $request->input('order_ids');
        
        DB::connection('woocommerce')->beginTransaction();
        try {
            // Get order item IDs before deleting them
            $orderItemIds = OrderItem::whereIn('order_id', $orderIds)->pluck('order_item_id');

            // Delete meta for the order items
            OrderItemMeta::whereIn('order_item_id', $orderItemIds)->delete();
            
            // Delete the order items themselves
            OrderItem::whereIn('order_id', $orderIds)->delete();

            // Delete post meta for the orders
            PostMeta::whereIn('post_id', $orderIds)->delete();

            // Delete the orders from the posts table
            Order::whereIn('ID', $orderIds)->delete();

            DB::connection('woocommerce')->commit();

            // Clear cache after successful bulk deletion
            CacheHelper::clearCacheOnOrderDelete($orderIds);

            return response()->json([
                'success' => true,
                'message' => 'Successfully deleted ' . count($orderIds) . ' orders.'
            ]);
        } catch (\Exception $e) {
            DB::connection('woocommerce')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing order
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $data = $request->validate([
            'order_status' => 'nullable|string',
            'customer_note' => 'nullable|string',
            'private_note' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        DB::connection('woocommerce')->beginTransaction();
        try {
            // Update order fields
            if (isset($data['order_status'])) {
                $order->post_status = $data['order_status'];
            }
            if (isset($data['customer_note'])) {
                $order->post_excerpt = $data['customer_note'];
            }
            
            $order->post_modified = now();
            $order->post_modified_gmt = now()->utc();
            $order->save();

            // Update meta if provided
            if (isset($data['payment_method'])) {
                PostMeta::updateOrCreate(
                    ['post_id' => $order->ID, 'meta_key' => '_payment_method'],
                    ['meta_value' => $data['payment_method']]
                );
                
                PostMeta::updateOrCreate(
                    ['post_id' => $order->ID, 'meta_key' => '_payment_method_title'],
                    ['meta_value' => ucwords(str_replace('_', ' ', $data['payment_method']))]
                );
            }

            DB::connection('woocommerce')->commit();

            // Clear cache after successful order update
            CacheHelper::clearCacheOnOrderUpdate($order->ID);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::connection('woocommerce')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $data = $request->validate([
            'status' => 'required|string',
        ]);

        DB::connection('woocommerce')->beginTransaction();
        try {
            $oldStatus = $order->post_status;
            $order->post_status = $data['status'];
            $order->post_modified = now();
            $order->post_modified_gmt = now()->utc();
            $order->save();

            // Add order note about status change
            if ($oldStatus !== $data['status']) {
                $comment = Comment::create([
                    'comment_post_ID' => $order->ID,
                    'comment_author' => auth()->user()->name ?? 'System',
                    'comment_content' => "Order status changed from {$oldStatus} to {$data['status']}",
                    'comment_type' => 'order_note',
                    'comment_date' => now(),
                    'comment_date_gmt' => now()->utc(),
                    'comment_approved' => 1,
                ]);
            }

            DB::connection('woocommerce')->commit();

            // Clear cache after status change
            CacheHelper::clearCacheOnOrderStatusChange($order->ID);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'new_status' => $data['status']
            ]);

        } catch (\Exception $e) {
            DB::connection('woocommerce')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }
} 