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
use Makiomar\WooOrderDashboard\Helpers\Terms\TaxonomyHelper;
use Makiomar\WooOrderDashboard\Helpers\Shipping\ShippingHelper;
use Makiomar\WooOrderDashboard\Services\WooCommerceShippingService;

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

    /**
     * Calculate product price with sale and tax information
     */
    protected function calculateProductPrice($meta)
    {
        $regularPrice = floatval($meta->get('_regular_price', 0));
        $salePrice = floatval($meta->get('_sale_price', 0));
        $price = floatval($meta->get('_price', 0));
        
        // Determine if product is on sale
        $isOnSale = false;
        $currentPrice = $price;
        
        if ($salePrice > 0 && $salePrice < $regularPrice) {
            $isOnSale = true;
            $currentPrice = $salePrice;
        } elseif ($regularPrice > 0) {
            $currentPrice = $regularPrice;
        }
        
        // Check if product has a valid price (greater than 0)
        if ($currentPrice <= 0) {
            return null; // Return null to indicate invalid price
        }
        
        // Get tax rate from config (default 15%)
        $taxRate = config('woo-order-dashboard.tax_rate', 0.15);
        $taxAmount = $currentPrice * $taxRate;
        $priceWithTax = $currentPrice + $taxAmount;
        
        return [
            'regular_price' => $regularPrice,
            'sale_price' => $salePrice,
            'current_price' => $currentPrice,
            'is_on_sale' => $isOnSale,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'price_with_tax' => $priceWithTax,
        ];
    }

    /**
     * Build address index for WooCommerce
     */
    protected function buildAddressIndex($customerInfo, $type)
    {
        $prefix = $type === 'billing' ? 'billing' : 'shipping';
        $parts = [
            $customerInfo['_' . $prefix . '_first_name'] ?? '',
            $customerInfo['_' . $prefix . '_last_name'] ?? '',
            $customerInfo['_' . $prefix . '_address_1'] ?? '',
            $customerInfo['_' . $prefix . '_city'] ?? '',
            $customerInfo['_' . $prefix . '_state'] ?? '',
            $customerInfo['_' . $prefix . '_postcode'] ?? '',
            $customerInfo['_' . $prefix . '_country'] ?? '',
        ];
        
        if ($type === 'billing') {
            $parts[] = $customerInfo['_billing_email'] ?? '';
            $parts[] = $customerInfo['_billing_phone'] ?? '';
        } else {
            $parts[] = $customerInfo['_shipping_phone'] ?? '';
        }
        
        return implode(' ', array_filter($parts));
    }

    /**
     * Ensure VAT tax rate exists in WooCommerce
     */
    protected function ensureTaxRateExists()
    {
        // Enable WooCommerce taxes if not already enabled
        $taxEnabled = DB::connection('woocommerce')->table('options')
            ->where('option_name', 'woocommerce_calc_taxes')
            ->value('option_value');
            
        if ($taxEnabled !== 'yes') {
            DB::connection('woocommerce')->table('options')->updateOrInsert(
                ['option_name' => 'woocommerce_calc_taxes'],
                ['option_value' => 'yes']
            );
        }
        
        // Configure tax display settings for proper tax display
        DB::connection('woocommerce')->table('options')->updateOrInsert(
            ['option_name' => 'woocommerce_tax_display_shop'],
            ['option_value' => 'incl']
        );
        
        DB::connection('woocommerce')->table('options')->updateOrInsert(
            ['option_name' => 'woocommerce_tax_display_cart'],
            ['option_value' => 'incl']
        );
        
        DB::connection('woocommerce')->table('options')->updateOrInsert(
            ['option_name' => 'woocommerce_tax_total_display'],
            ['option_value' => 'itemized']
        );
        
        DB::connection('woocommerce')->table('options')->updateOrInsert(
            ['option_name' => 'woocommerce_price_decimal_sep'],
            ['option_value' => '.']
        );
        
        DB::connection('woocommerce')->table('options')->updateOrInsert(
            ['option_name' => 'woocommerce_price_thousand_sep'],
            ['option_value' => ',']
        );

        // Check if VAT tax rate already exists
        $existingTaxRate = DB::connection('woocommerce')->table('woocommerce_tax_rates')
            ->where('tax_rate_name', 'VAT')
            ->where('tax_rate', '15.0000')
            ->first();

        if (!$existingTaxRate) {
            // Create VAT tax rate
            $taxRateId = DB::connection('woocommerce')->table('woocommerce_tax_rates')->insertGetId([
                'tax_rate_country' => '',
                'tax_rate_state' => '',
                'tax_rate' => '15.0000',
                'tax_rate_name' => 'VAT',
                'tax_rate_priority' => 1,
                'tax_rate_compound' => 0,
                'tax_rate_shipping' => 1,
                'tax_rate_order' => 1,
                'tax_rate_class' => '',
            ]);
        } else {
            $taxRateId = $existingTaxRate->tax_rate_id;
        }
        
        return $existingTaxRate ? $existingTaxRate->tax_rate_id : $taxRateId ?? 1;
    }
    
    public function searchProducts(Request $request)
    {
        $q = $request->get('q');
        $searchType = $request->get('search_type', 'sku'); // 'sku' or 'title', default to 'sku'
        $prefix = DB::getDatabaseName() . '.';
        
        // Debug logging

        
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
            
            // If _product_type is not set, check for variable product indicators
            if ($productType === 'simple') {
                $productAttributes = $meta->get('_product_attributes');
                if ($productAttributes && !empty($productAttributes)) {
                    try {
                        $attributes = unserialize($productAttributes);
                        if (is_array($attributes)) {
                            foreach ($attributes as $attribute) {
                                if (isset($attribute['is_variation']) && $attribute['is_variation'] == 1) {
                                    $productType = 'variable';
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // Failed to unserialize product attributes
                    }
                }
            }
            
            
            
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
                            $taxonomy = str_replace('attribute_', '', $key);
                            $attrLabel = TaxonomyHelper::getTaxonomyLabel($taxonomy);
                            $term = TaxonomyHelper::getTermBySlug($taxonomy, $value);
                            $valueLabel = $term ? $term['name'] : $value;
                            $attributes[$attrLabel] = $valueLabel;
                        }
                    }
                    
                    $priceInfo = $this->calculateProductPrice($variationMeta);
                    
                    // Skip products with invalid prices
                    if ($priceInfo === null) {
                        continue;
                    }
                    
                    $results->push([
                        'product_id' => $product->ID,
                        'variation_id' => $variation->ID,
                        'name' => $product->post_title,
                        'sku' => $variationMeta->get('_sku'),
                        'price' => $priceInfo['current_price'],
                        'regular_price' => $priceInfo['regular_price'],
                        'sale_price' => $priceInfo['sale_price'],
                        'is_on_sale' => $priceInfo['is_on_sale'],
                        'tax_rate' => $priceInfo['tax_rate'],
                        'tax_amount' => $priceInfo['tax_amount'],
                        'price_with_tax' => $priceInfo['price_with_tax'],
                        'attributes' => $attributes,
                    ]);
                    
                    // Mark this variation as processed
                    $processedVariations->push($variation->ID);
                }
            } else {
                // For SKU search or simple products
                if ($productType === 'variable') {
                    // For variable products in SKU search, only return variations (not the main product)
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
                                $taxonomy = str_replace('attribute_', '', $key);
                                $attrLabel = TaxonomyHelper::getTaxonomyLabel($taxonomy);
                                $term = TaxonomyHelper::getTermBySlug($taxonomy, $value);
                                $valueLabel = $term ? $term['name'] : $value;
                                $attributes[$attrLabel] = $valueLabel;
                            }
                        }
                        
                        $priceInfo = $this->calculateProductPrice($variationMeta);
                        
                        // Skip products with invalid prices
                        if ($priceInfo === null) {
                            continue;
                        }
                        
                        $results->push([
                            'product_id' => $product->ID,
                            'variation_id' => $variation->ID,
                            'name' => $product->post_title,
                            'sku' => $variationMeta->get('_sku'),
                            'price' => $priceInfo['current_price'],
                            'regular_price' => $priceInfo['regular_price'],
                            'sale_price' => $priceInfo['sale_price'],
                            'is_on_sale' => $priceInfo['is_on_sale'],
                            'tax_rate' => $priceInfo['tax_rate'],
                            'tax_amount' => $priceInfo['tax_amount'],
                            'price_with_tax' => $priceInfo['price_with_tax'],
                            'attributes' => $attributes,
                        ]);
                        
                        // Mark this variation as processed
                        $processedVariations->push($variation->ID);
                    }
                } else {
                    // For simple products, include the main product
                    $priceInfo = $this->calculateProductPrice($meta);
                    
                    // Skip products with invalid prices
                    if ($priceInfo === null) {
                        continue;
                    }
                    
                    $results->push([
                        'product_id' => $product->ID,
                        'variation_id' => 0,
                        'name' => $product->post_title,
                        'sku' => $meta->get('_sku'),
                        'price' => $priceInfo['current_price'],
                        'regular_price' => $priceInfo['regular_price'],
                        'sale_price' => $priceInfo['sale_price'],
                        'is_on_sale' => $priceInfo['is_on_sale'],
                        'tax_rate' => $priceInfo['tax_rate'],
                        'tax_amount' => $priceInfo['tax_amount'],
                        'price_with_tax' => $priceInfo['price_with_tax'],
                        'attributes' => [],
                    ]);
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
                            $taxonomy = str_replace('attribute_', '', $key);
                            $attrLabel = TaxonomyHelper::getTaxonomyLabel($taxonomy);
                            $term = TaxonomyHelper::getTermBySlug($taxonomy, $value);
                            $valueLabel = $term ? $term['name'] : $value;
                            $attributes[$attrLabel] = $valueLabel;
                        }
                    }
                    
                    $priceInfo = $this->calculateProductPrice($variationMeta);
                    
                    // Skip products with invalid prices
                    if ($priceInfo === null) {
                        continue;
                    }
                    
                    $results->push([
                        'product_id' => $parentProduct->ID,
                        'variation_id' => $variation->ID,
                        'name' => $parentProduct->post_title,
                        'sku' => $variationMeta->get('_sku'),
                        'price' => $priceInfo['current_price'],
                        'regular_price' => $priceInfo['regular_price'],
                        'sale_price' => $priceInfo['sale_price'],
                        'is_on_sale' => $priceInfo['is_on_sale'],
                        'tax_rate' => $priceInfo['tax_rate'],
                        'tax_amount' => $priceInfo['tax_amount'],
                        'price_with_tax' => $priceInfo['price_with_tax'],
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
        $prefix = DB::getDatabaseName() . '.';
        
        $customers = Customer::where('user_status', 0)
            ->where(function ($query) use ($q) {
                $query->where('display_name', 'LIKE', "%{$q}%")
                      ->orWhere('user_email', 'LIKE', "%{$q}%");
            })
            ->limit(10)
            ->get();
        
        $results = $customers->map(function ($customer) {
            $meta = $customer->meta->pluck('meta_value', 'meta_key');
            return [
                'id' => $customer->ID,
                'name' => $customer->display_name,
                'email' => $customer->user_email,
                'billing_first_name' => $meta->get('first_name', ''),
                'billing_last_name' => $meta->get('last_name', ''),
                'billing_phone' => $meta->get('billing_phone', ''),
                'billing_address_1' => $meta->get('billing_address_1', ''),
                'billing_address_2' => $meta->get('billing_address_2', ''),
                'billing_city' => $meta->get('billing_city', ''),
                'billing_state' => $meta->get('billing_state', ''),
                'billing_postcode' => $meta->get('billing_postcode', ''),
                'billing_country' => $meta->get('billing_country', ''),
            ];
        });
        
        return response()->json($results);
    }

    public function getShippingMethods(Request $request)
    {
        $destination = [
            'country' => $request->input('country', 'SA'),
            'state' => $request->input('state', ''),
            'postcode' => $request->input('postcode', ''),
        ];
        
        $cartItems = $request->input('items', []);
        
        $shippingService = new WooCommerceShippingService();
        $methods = $shippingService->getShippingMethods($destination, $cartItems);
        
        $availableMethods = [];
        foreach ($methods as $method) {
            $availableMethods[] = [
                'id' => $method['id'],
                'title' => $method['title'],
                'description' => '',
                'cost' => $method['cost'],
            ];
        }
        return response()->json(['methods' => $availableMethods]);
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
            'shipping_method_id' => 'nullable|string',
            'shipping_method_title' => 'nullable|string',
            'shipping_instance_id' => 'nullable|string',
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
        
        // Ensure tax rate exists and settings are configured FIRST
        $taxRateId = $this->ensureTaxRateExists();
        
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
            
            // Calculate tax correctly (matching WooCommerce structure)
            $lineItemsTax = collect($items)->sum(function ($item) {
                return ($item['price'] * $item['qty']) * 0.15;
            });
            
            // Calculate shipping tax correctly
            $shippingCostWithoutTax = ($data['shipping'] ?? 0) / 1.15; // Remove 15% tax
            $shippingTax = ($data['shipping'] ?? 0) - $shippingCostWithoutTax; // Extract tax from shipping total
            
            // Total tax is the sum of line items tax and shipping tax
            $totalTax = $lineItemsTax + $shippingTax;
            
            // Calculate total (subtotal + shipping + tax - discount)
            $total = $subtotal + ($data['shipping'] ?? 0) + $totalTax - ($data['discount'] ?? 0);
            
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
                ['_order_key', 'wc_' . uniqid()],
                ['_customer_user', $data['customer_id'] ?? ''],
                ['_payment_method', $data['payment_method'] ?? ''],
                ['_payment_method_title', 'الحوالة البنكية'],
                ['_customer_ip_address', request()->ip()],
                ['_customer_user_agent', request()->userAgent()],
                ['_created_via', 'checkout'],
                ['_cart_hash', md5(json_encode($items))],
                ['_download_permissions_granted', 'no'],
                ['_recorded_sales', 'no'],
                ['_recorded_coupon_usage_counts', 'no'],
                ['_new_order_email_sent', 'false'],
                ['_order_stock_reduced', 'no'],
                ['_billing_first_name', $customerInfo['_billing_first_name'] ?? ''],
                ['_billing_last_name', $customerInfo['_billing_last_name'] ?? ''],
                ['_billing_address_1', $customerInfo['_billing_address_1'] ?? ''],
                ['_billing_state', $customerInfo['_billing_state'] ?? ''],
                ['_billing_postcode', $customerInfo['_billing_postcode'] ?? ''],
                ['_billing_country', $customerInfo['_billing_country'] ?? ''],
                ['_billing_email', $customerInfo['_billing_email'] ?? ''],
                ['_billing_phone', $customerInfo['_billing_phone'] ?? ''],
                ['_shipping_first_name', $customerInfo['_shipping_first_name'] ?? ''],
                ['_shipping_last_name', $customerInfo['_shipping_last_name'] ?? ''],
                ['_shipping_address_1', $customerInfo['_shipping_address_1'] ?? ''],
                ['_shipping_city', $customerInfo['_shipping_state'] ?? ''],
                ['_shipping_postcode', $customerInfo['_shipping_postcode'] ?? ''],
                ['_shipping_country', $customerInfo['_shipping_country'] ?? ''],
                ['_shipping_phone', $customerInfo['_billing_phone'] ?? ''],
                ['_order_currency', 'SAR'],
                ['_cart_discount', $data['discount'] ?? '0'],
                ['_cart_discount_tax', '0'],
                ['_order_shipping', $shippingCostWithoutTax],
                ['_order_shipping_tax', $shippingTax],
                ['_order_tax', $totalTax],
                ['_order_total', $total],
                ['_order_version', '9.3.3'],
                ['_prices_include_tax', 'no'],
                ['_billing_address_index', $this->buildAddressIndex($customerInfo, 'billing')],
                ['_shipping_address_index', $this->buildAddressIndex($customerInfo, 'shipping')],
                ['_shipping_email', $customerInfo['_billing_email'] ?? ''],
                ['_billing_lat', ''],
                ['_shipping_lat', ''],
                ['_billing_lng', ''],
                ['_shipping_lng', ''],
                ['_billing_street_number', ''],
                ['_shipping_street_number', ''],
                ['is_vat_exempt', 'no'],
                ['customer_geolocation', 'EG'],
                ['_wpo_order_creator', $data['customer_id'] ?? ''],
                ['_billing_city', null],
                ['_wc_cancel_key', md5(uniqid())],
                ['whatsapp_notifications', '-1'],
                ['sms_notifications', '-1'],
                ['sms_notifications_time', now()->format('Y-m-d H:i:s')],
                ['whatsapp_notifications_time', now()->format('Y-m-d H:i:s')],
                ['odoo_order', '25840'],
                ['odoo_order_number', 'S25797'],
                ['oodo-status', 'success'],
                ['_edit_lock', time() . ':' . ($data['customer_id'] ?? '')],
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

                // Calculate line item tax (15%) - matching WordPress structure
                $lineSubtotal = $itemData['price'] * $itemData['qty'];
                $lineTax = $lineSubtotal * 0.15;
                $lineTotal = $lineSubtotal; // Total should be tax-exclusive (matching WordPress)
                
                // Use the tax rate ID from the beginning of the method
                // $taxRateId is already available from ensureTaxRateExists()
                
                // Proper tax data serialization format
                $taxData = serialize([
                    'total' => [$taxRateId => $lineTax],
                    'subtotal' => [$taxRateId => $lineTax]
                ]);
                

                
                $orderItemMeta = [
                    ['_product_id', $itemData['product_id']],
                    ['_variation_id', $itemData['variation_id'] ?? 0],
                    ['_qty', $itemData['qty']],
                    ['_tax_class', ''],
                    ['_line_subtotal', $lineSubtotal],
                    ['_line_subtotal_tax', $lineTax],
                    ['_line_total', $lineTotal],
                    ['_line_tax', $lineTax],
                    ['_line_tax_data', $taxData],
                    ['_reduced_stock', '1'],
                ];
                
                // Add variation attributes if they exist
                if (isset($itemData['attributes']) && is_array($itemData['attributes'])) {
                    foreach ($itemData['attributes'] as $attrKey => $attrValue) {
                        // Convert percent-encoded keys to proper Arabic
                        $decodedKey = urldecode($attrKey);
                        $decodedValue = urldecode($attrValue);
                        $orderItemMeta[] = [$decodedKey, $decodedValue];
                    }
                }
                
                foreach ($orderItemMeta as $meta) {
                    $itemMeta = OrderItemMeta::create([
                        'order_item_id' => $orderItem->order_item_id,
                        'meta_key' => $meta[0],
                        'meta_value' => $meta[1],
                    ]);
                }
            }

            // Create shipping line item if shipping amount > 0
            if (($data['shipping'] ?? 0) > 0) {
                // Use actual shipping method details if available, otherwise fallback to defaults
                $shippingMethodTitle = $data['shipping_method_title'] ?? 'Flat Rate';
                $shippingMethodId = $data['shipping_method_id'] ?? 'flat_rate';
                $shippingInstanceId = $data['shipping_instance_id'] ?? '';
                
                // Calculate shipping cost without tax (matching WordPress structure)
                $shippingCostWithoutTax = $data['shipping'] / 1.15; // Remove 15% tax
                $shippingTax = $data['shipping'] - $shippingCostWithoutTax;
                
                $shippingItem = OrderItem::create([
                    'order_item_name' => $shippingMethodTitle,
                    'order_item_type' => 'shipping',
                    'order_id' => $order->ID,
                ]);

                // Use the tax rate ID from the beginning of the method
                // $taxRateId is already available from ensureTaxRateExists()
                
                // Proper shipping tax data serialization
                $shippingTaxData = serialize(['total' => [$taxRateId => $shippingTax]]);
                
                $shippingItemMeta = [
                    ['method_title', $shippingMethodTitle],
                    ['Items', $itemData['name'] . ' × ' . $itemData['qty']],
                    ['wpo_package_hash', md5(uniqid())],
                    ['wpo_shipping_method_id', $shippingMethodId . ':' . $shippingInstanceId],
                ];
                
                foreach ($shippingItemMeta as $meta) {
                    $itemMeta = OrderItemMeta::create([
                        'order_item_id' => $shippingItem->order_item_id,
                        'meta_key' => $meta[0],
                        'meta_value' => $meta[1],
                    ]);
                }
            }

            // Tax rate already configured at the beginning of the method
            
            try {

                
                DB::connection('woocommerce')->table('wc_order_stats')->insert([
                    'order_id' => $order->ID,
                    'parent_id' => 0,
                    'date_created' => now(),
                    'date_created_gmt' => now()->utc(),
                    'date_paid' => now(),
                    'num_items_sold' => collect($items)->sum('qty'),
                    'total_sales' => $total,
                    'tax_total' => $totalTax,
                    'shipping_total' => $data['shipping'] ?? 0,
                    'net_total' => $subtotal - ($data['discount'] ?? 0),
                    'returning_customer' => 0,
                    'status' => $wcOrderStatus,
                    'customer_id' => $data['customer_id'] ?? 0,
                    'date_completed' => null,
                ]);
                
                foreach ($items as $itemData) {
                    $itemSubtotal = $itemData['price'] * $itemData['qty'];
                    $itemTax = $itemSubtotal * 0.15;
                    
                    // Get the order item ID for this product
                    $orderItem = OrderItem::where('order_id', $order->ID)
                        ->where('order_item_type', 'line_item')
                        ->whereHas('meta', function($query) use ($itemData) {
                            $query->where('meta_key', '_product_id')
                                  ->where('meta_value', $itemData['product_id']);
                        })
                        ->first();
                    
                    if ($orderItem) {
                        DB::connection('woocommerce')->table('wc_order_product_lookup')->insert([
                            'order_id' => $order->ID,
                            'order_item_id' => $orderItem->order_item_id,
                            'product_id' => $itemData['product_id'],
                            'variation_id' => $itemData['variation_id'] ?? 0,
                            'customer_id' => $data['customer_id'] ?? 0,
                            'date_created' => now(),
                            'product_qty' => $itemData['qty'],
                            'product_net_revenue' => $itemSubtotal,
                            'product_gross_revenue' => $itemSubtotal + $itemTax,
                            'coupon_amount' => 0,
                            'tax_amount' => $itemTax,
                            'shipping_amount' => ($data['shipping'] ?? 0) / count($items),
                            'shipping_tax_amount' => ($shippingTax / count($items)),
                        ]);
                    }
                }
                
                if ($totalTax > 0) {
                    // Use the tax rate ID from the beginning of the method
                    // $taxRateId is already available from ensureTaxRateExists()
                    
                    DB::connection('woocommerce')->table('wc_order_tax_lookup')->insert([
                        'order_id' => $order->ID,
                        'tax_rate_id' => $taxRateId,
                        'date_created' => now(),
                        'shipping_tax' => $shippingTax,
                        'order_tax' => $lineItemsTax,
                        'total_tax' => $totalTax,
                    ]);
                    

                }
                

                
            } catch (\Exception $e) {
                // Failed to add WooCommerce lookup table entries
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
                // Failed to clear WooCommerce cache
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