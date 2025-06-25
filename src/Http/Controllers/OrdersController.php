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
use Makiomar\WooOrderDashboard\Services\WooCommerceApiService;

class OrdersController extends Controller
{
    public function create()
    {
        $prefix = DB::getDatabaseName() . '.';
        $now = now();
        $defaultOrderDate = $now->format('Y-m-d');
        $defaultOrderHour = $now->format('H');
        $defaultOrderMinute = $now->format('i');
        return view('woo-order-dashboard::orders.create', compact('prefix', 'defaultOrderDate', 'defaultOrderHour', 'defaultOrderMinute'));
    }

    public function searchProducts(Request $request)
    {
        $q = $request->get('q');
        $searchType = $request->get('search_type', 'sku'); // 'sku' or 'title', default to 'sku'
        $prefix = DB::getDatabaseName() . '.';
        
        // First, search for regular products (simple and variable)
        $products = Product::with('meta')
            ->where('post_status', 'publish')
            ->where('post_type', 'product')
            ->where(function ($query) use ($q, $searchType) {
                if ($searchType === 'title') {
                    $query->where('post_title', 'LIKE', "%{$q}%");
                } else {
                    // Default to SKU search
                    $query->whereHas('meta', function ($subQuery) use ($q) {
                        $subQuery->where('meta_key', '_sku')->where('meta_value', 'LIKE', "%{$q}%");
                    });
                }
            })
            ->limit(20)
            ->get();

        $results = collect();
        $processedVariations = collect(); // Track which variations we've already processed

        foreach ($products as $product) {
            $meta = $product->meta->pluck('meta_value', 'meta_key');
            $productType = $meta->get('_product_type', 'simple');
            
            // If searching by title and it's a variable product, skip the parent and only include variations
            if ($searchType === 'title' && $productType === 'variable') {
                $variations = Product::with('meta')
                    ->where('post_status', 'publish')
                    ->where('post_type', 'product_variation')
                    ->where('post_parent', $product->ID)
                    ->get();
                
                foreach ($variations as $variation) {
                    $variationMeta = $variation->meta->pluck('meta_value', 'meta_key');
                    
                    $attributes = [];
                    foreach ($variationMeta as $key => $value) {
                        if (strpos($key, 'attribute_') === 0) {
                            $attributes[$key] = $value;
                        }
                    }
                    
                    $results->push([
                        'product_id' => $product->ID,
                        'variation_id' => $variation->ID,
                        'name' => $product->post_title,
                        'sku' => $variationMeta->get('_sku'),
                        'price' => $variationMeta->get('_price'),
                        'attributes' => $attributes,
                    ]);
                    
                    // Mark this variation as processed
                    $processedVariations->push($variation->ID);
                }
            } else {
                // For SKU search or simple products, include the main product
                $results->push([
                    'product_id' => $product->ID,
                    'variation_id' => 0,
                    'name' => $product->post_title,
                    'sku' => $meta->get('_sku'),
                    'price' => $meta->get('_price'),
                    'attributes' => [],
                ]);
                
                // If it's a variable product and we're searching by SKU, also get its variations
                if ($searchType === 'sku' && $productType === 'variable') {
                    $variations = Product::with('meta')
                        ->where('post_status', 'publish')
                        ->where('post_type', 'product_variation')
                        ->where('post_parent', $product->ID)
                        ->get();
                    
                    foreach ($variations as $variation) {
                        $variationMeta = $variation->meta->pluck('meta_value', 'meta_key');
                        
                        $attributes = [];
                        foreach ($variationMeta as $key => $value) {
                            if (strpos($key, 'attribute_') === 0) {
                                $attributes[$key] = $value;
                            }
                        }
                        
                        $results->push([
                            'product_id' => $product->ID,
                            'variation_id' => $variation->ID,
                            'name' => $product->post_title,
                            'sku' => $variationMeta->get('_sku'),
                            'price' => $variationMeta->get('_price'),
                            'attributes' => $attributes,
                        ]);
                        
                        // Mark this variation as processed
                        $processedVariations->push($variation->ID);
                    }
                }
            }
        }

        // Also search for variations directly (in case someone searches by variation SKU or name)
        $variations = Product::with('meta')
            ->where('post_status', 'publish')
            ->where('post_type', 'product_variation')
            ->where(function ($query) use ($q, $searchType) {
                if ($searchType === 'title') {
                    $query->where('post_title', 'LIKE', "%{$q}%");
                } else {
                    // Default to SKU search
                    $query->whereHas('meta', function ($subQuery) use ($q) {
                        $subQuery->where('meta_key', '_sku')->where('meta_value', 'LIKE', "%{$q}%");
                    });
                }
            })
            ->limit(10)
            ->get();

        foreach ($variations as $variation) {
            // Skip if we've already processed this variation
            if ($processedVariations->contains($variation->ID)) {
                continue;
            }
            
            $variationMeta = $variation->meta->pluck('meta_value', 'meta_key');
            
            // Get parent product info
            $parentProduct = Product::with('meta')->find($variation->post_parent);
            if ($parentProduct) {
                $parentMeta = $parentProduct->meta->pluck('meta_value', 'meta_key');
                $parentProductType = $parentMeta->get('_product_type', 'simple');
                
                // Only include if parent is a variable product
                if ($parentProductType === 'variable') {
                    $attributes = [];
                    foreach ($variationMeta as $key => $value) {
                        if (strpos($key, 'attribute_') === 0) {
                            $attributes[$key] = $value;
                        }
                    }
                    
                    $results->push([
                        'product_id' => $parentProduct->ID,
                        'variation_id' => $variation->ID,
                        'name' => $parentProduct->post_title,
                        'sku' => $variationMeta->get('_sku'),
                        'price' => $variationMeta->get('_price'),
                        'attributes' => $attributes,
                    ]);
                }
            }
        }

        // Limit results to 20
        return response()->json($results->take(20)->values());
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
        
        if (empty($items)) {
            return back()->with('error', 'No order items found. Please add at least one product to the order.');
        }

        if (config('woo-order-dashboard.api.enabled', false)) {
            return $this->createOrderViaApi($data);
        } else {
            return $this->createOrderViaDatabase($data);
        }
    }

    /**
     * Create order via WooCommerce REST API
     *
     * @param array $data
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function createOrderViaApi(array $data)
    {
        try {
            $apiService = new WooCommerceApiService();
            
            if (!$apiService->testConnection()) {
                return back()->with('error', 'Unable to connect to WooCommerce API. Please check your API configuration.');
            }

            $order = $apiService->createOrder($data);
            
            return redirect()->route('orders.show', $order['id'])
                ->with('success', 'Order #' . $order['id'] . ' created successfully via WooCommerce API!');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create order via API: ' . $e->getMessage());
        }
    }

    /**
     * Create order via direct database insertion (current method)
     *
     * @param array $data
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function createOrderViaDatabase(array $data)
    {
        $items = json_decode($data['order_items'], true);
        
        DB::connection('woocommerce')->beginTransaction();
        try {
            $orderStatus = $data['order_status'] ?? 'processing';
            $wcOrderStatus = strpos($orderStatus, 'wc-') === 0 ? $orderStatus : 'wc-' . $orderStatus;
            
            $orderData = [
                'post_type' => 'shop_order',
                'post_status' => $wcOrderStatus,
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
            
            $subtotal = collect($items)->sum(function ($item) {
                return ($item['price'] * $item['qty']);
            });
            $total = $subtotal - ($data['discount'] ?? 0) + ($data['shipping'] ?? 0) + ($data['taxes'] ?? 0);
            
            $order = Order::create($orderData);
            
            $customerInfo = [];
            if (!empty($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
                if ($customer) {
                    $customerMeta = $customer->meta->pluck('meta_value', 'meta_key');
                    $customerInfo = [
                        '_billing_first_name' => $customerMeta->get('first_name', ''),
                        '_billing_last_name' => $customerMeta->get('last_name', ''),
                        '_billing_email' => $customer->user_email,
                        '_billing_phone' => $customerMeta->get('billing_phone', ''),
                        '_billing_address_1' => $customerMeta->get('billing_address_1', ''),
                        '_billing_address_2' => $customerMeta->get('billing_address_2', ''),
                        '_billing_city' => $customerMeta->get('billing_city', ''),
                        '_billing_state' => $customerMeta->get('billing_state', ''),
                        '_billing_postcode' => $customerMeta->get('billing_postcode', ''),
                        '_billing_country' => $customerMeta->get('billing_country', ''),
                        '_shipping_first_name' => $customerMeta->get('shipping_first_name', $customerMeta->get('first_name', '')),
                        '_shipping_last_name' => $customerMeta->get('shipping_last_name', $customerMeta->get('last_name', '')),
                        '_shipping_address_1' => $customerMeta->get('shipping_address_1', $customerMeta->get('billing_address_1', '')),
                        '_shipping_address_2' => $customerMeta->get('shipping_address_2', $customerMeta->get('billing_address_2', '')),
                        '_shipping_city' => $customerMeta->get('shipping_city', $customerMeta->get('billing_city', '')),
                        '_shipping_state' => $customerMeta->get('shipping_state', $customerMeta->get('billing_state', '')),
                        '_shipping_postcode' => $customerMeta->get('shipping_postcode', $customerMeta->get('billing_postcode', '')),
                        '_shipping_country' => $customerMeta->get('shipping_country', $customerMeta->get('billing_country', '')),
                    ];
                }
            }
            
            $metaData = [
                ['_customer_user', $data['customer_id'] ?? '0'],
                ['_order_total', $total],
                ['_order_currency', 'USD'],
                ['_payment_method', $data['payment_method'] ?? ''],
                ['_payment_method_title', $data['payment_method'] ? ucwords(str_replace('_', ' ', $data['payment_method'])) : ''],
                ['_cart_discount', $data['discount'] ?? '0'],
                ['_order_shipping', $data['shipping'] ?? '0'],
                ['_order_tax', $data['taxes'] ?? '0'],
                ['_billing_first_name', $customerInfo['_billing_first_name'] ?? ''],
                ['_billing_last_name', $customerInfo['_billing_last_name'] ?? ''],
                ['_billing_email', $customerInfo['_billing_email'] ?? ''],
                ['_billing_phone', $customerInfo['_billing_phone'] ?? ''],
                ['_billing_address_1', $customerInfo['_billing_address_1'] ?? ''],
                ['_billing_address_2', $customerInfo['_billing_address_2'] ?? ''],
                ['_billing_city', $customerInfo['_billing_city'] ?? ''],
                ['_billing_state', $customerInfo['_billing_state'] ?? ''],
                ['_billing_postcode', $customerInfo['_billing_postcode'] ?? ''],
                ['_billing_country', $customerInfo['_billing_country'] ?? ''],
                ['_shipping_first_name', $customerInfo['_shipping_first_name'] ?? ''],
                ['_shipping_last_name', $customerInfo['_shipping_last_name'] ?? ''],
                ['_shipping_address_1', $customerInfo['_shipping_address_1'] ?? ''],
                ['_shipping_address_2', $customerInfo['_shipping_address_2'] ?? ''],
                ['_shipping_city', $customerInfo['_shipping_city'] ?? ''],
                ['_shipping_state', $customerInfo['_shipping_state'] ?? ''],
                ['_shipping_postcode', $customerInfo['_shipping_postcode'] ?? ''],
                ['_shipping_country', $customerInfo['_shipping_country'] ?? ''],
                ['_order_key', 'wc_' . uniqid()],
                ['_order_version', '7.0.0'],
                ['_prices_include_tax', 'no'],
                ['_discount_total', $data['discount'] ?? '0'],
                ['_discount_tax', '0'],
                ['_shipping_tax', '0'],
                ['_cart_tax', $data['taxes'] ?? '0'],
                ['_total_tax', $data['taxes'] ?? '0'],
                ['_customer_ip_address', request()->ip()],
                ['_customer_user_agent', request()->userAgent()],
                ['_created_via', 'admin'],
                ['_date_completed', ''],
                ['_date_paid', now()->format('Y-m-d H:i:s')],
                ['_cart_hash', ''],
            ];

            foreach ($metaData as $meta) {
                $postMeta = PostMeta::create([
                    'post_id' => $order->ID,
                    'meta_key' => $meta[0],
                    'meta_value' => $meta[1],
                ]);
            }

            foreach ($items as $itemData) {
                $orderItem = OrderItem::create([
                    'order_item_name' => $itemData['name'],
                    'order_item_type' => 'line_item',
                    'order_id' => $order->ID,
                ]);

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
                }
            }
            
            try {
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
                    'status' => $wcOrderStatus,
                ]);
                
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
                
                if (($data['taxes'] ?? 0) > 0) {
                    DB::connection('woocommerce')->table('wc_order_tax_lookup')->insert([
                        'order_id' => $order->ID,
                        'tax_rate_id' => 1,
                        'date_created' => now(),
                        'shipping_tax' => 0,
                        'order_tax' => $data['taxes'] ?? 0,
                        'total_tax' => $data['taxes'] ?? 0,
                    ]);
                }
                
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
                    
                } catch (\Exception $e) {
                    \Log::info('wc_order_operational_data table not available or failed: ' . $e->getMessage());
                }
                
            } catch (\Exception $e) {
                \Log::warning('Failed to add WooCommerce lookup table entries: ' . $e->getMessage());
            }
            
            DB::connection('woocommerce')->commit();

            CacheHelper::clearCacheOnOrderCreate();

            // Store private note as an order comment if provided
            if (!empty($data['private_note'])) {
                Comment::create([
                    'comment_post_ID' => $order->ID,
                    'comment_author' => auth()->user()->name ?? 'System',
                    'comment_author_email' => auth()->user()->email ?? '',
                    'comment_author_url' => '',
                    'comment_content' => $data['private_note'],
                    'comment_type' => 'order_note',
                    'comment_approved' => 1,
                    'user_id' => auth()->id() ?? 1,
                    'is_customer_note' => 0,
                    'comment_date' => now(),
                    'comment_date_gmt' => now()->utc(),
                ]);
            }

            try {
                DB::connection('woocommerce')->table('options')->where('option_name', 'like', '_transient_wc_order_%')->delete();
                DB::connection('woocommerce')->table('options')->where('option_name', 'like', '_transient_timeout_wc_order_%')->delete();
                
                DB::connection('woocommerce')->table('options')->where('option_name', 'like', '_transient_wc_%')->delete();
                DB::connection('woocommerce')->table('options')->where('option_name', 'like', '_transient_timeout_wc_%')->delete();
                
            } catch (\Exception $e) {
                \Log::warning('Failed to clear WooCommerce cache: ' . $e->getMessage());
            }

            return redirect()->route('orders.index')->with('success', 'Order created successfully. Order ID: ' . $order->ID);

        } catch (\Exception $e) {
            DB::connection('woocommerce')->rollBack();
            return back()->with('error', 'Order creation failed: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
        ]);

        $orderIds = $request->input('order_ids');
        
        if (config('woo-order-dashboard.api.enabled', false)) {
            return $this->deleteOrdersViaApi($orderIds);
        } else {
            return $this->deleteOrdersViaDatabase($orderIds);
        }
    }

    /**
     * Delete orders via WooCommerce REST API
     *
     * @param array $orderIds
     * @return \Illuminate\Http\JsonResponse
     */
    protected function deleteOrdersViaApi(array $orderIds)
    {
        try {
            $apiService = new WooCommerceApiService();
            
            if (!$apiService->testConnection()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to connect to WooCommerce API. Please check your API configuration.'
                ], 500);
            }

            $results = $apiService->deleteOrders($orderIds);
            
            $successCount = count($results['success']);
            $failedCount = count($results['failed']);
            
            if ($failedCount === 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully deleted {$successCount} orders via WooCommerce API."
                ]);
            } elseif ($successCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Deleted {$successCount} orders via API. {$failedCount} orders failed to delete.",
                    'failed_orders' => $results['failed']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete any orders via API.'
                ], 500);
            }
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete orders via API: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete orders via direct database queries (default method)
     *
     * @param array $orderIds
     * @return \Illuminate\Http\JsonResponse
     */
    protected function deleteOrdersViaDatabase(array $orderIds)
    {
        DB::connection('woocommerce')->beginTransaction();
        try {
            $orderItemIds = OrderItem::whereIn('order_id', $orderIds)->pluck('order_item_id');

            OrderItemMeta::whereIn('order_item_id', $orderItemIds)->delete();
            
            OrderItem::whereIn('order_id', $orderIds)->delete();

            PostMeta::whereIn('post_id', $orderIds)->delete();

            Order::whereIn('ID', $orderIds)->delete();

            DB::connection('woocommerce')->commit();

            CacheHelper::clearCacheOnOrderDelete($orderIds);

            return response()->json([
                'success' => true,
                'message' => 'Successfully deleted ' . count($orderIds) . ' orders via database.'
            ]);
        } catch (\Exception $e) {
            DB::connection('woocommerce')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete orders via database: ' . $e->getMessage()
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
            if (isset($data['order_status'])) {
                $order->post_status = $data['order_status'];
            }
            if (isset($data['customer_note'])) {
                $order->post_excerpt = $data['customer_note'];
            }
            
            $order->post_modified = now();
            $order->post_modified_gmt = now()->utc();
            $order->save();

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