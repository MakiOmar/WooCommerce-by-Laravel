<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WooCommerceApiService
{
    protected $config;
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;
    protected $version;

    public function __construct()
    {
        $this->config = config('woo-order-dashboard.api');
        $this->baseUrl = rtrim($this->config['site_url'], '/');
        $this->consumerKey = $this->config['consumer_key'];
        $this->consumerSecret = $this->config['consumer_secret'];
        $this->version = $this->config['version'];
    }

    /**
     * Create an order via WooCommerce REST API
     *
     * @param array $orderData
     * @return array
     * @throws Exception
     */
    public function createOrder(array $orderData)
    {
        try {
            Log::info('Creating order via WooCommerce API', ['order_data' => $orderData]);

            // Prepare the order data for WooCommerce API
            $wcOrderData = $this->prepareOrderData($orderData);

            // Make the API request
            $response = $this->makeApiRequest('orders', 'POST', $wcOrderData);

            if ($response->successful()) {
                $order = $response->json();
                Log::info('Order created successfully via API', ['order_id' => $order['id']]);
                return $order;
            } else {
                $error = $response->json();
                Log::error('Failed to create order via API', [
                    'status' => $response->status(),
                    'error' => $error
                ]);
                throw new Exception('Failed to create order via API: ' . ($error['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            Log::error('Exception while creating order via API', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Prepare order data for WooCommerce API
     *
     * @param array $orderData
     * @return array
     */
    protected function prepareOrderData(array $orderData)
    {
        $items = json_decode($orderData['order_items'], true);
        
        // Calculate totals
        $subtotal = collect($items)->sum(function ($item) {
            return ($item['price'] * $item['qty']);
        });
        
        // Calculate tax for line items
        $lineItemsTax = collect($items)->sum(function ($item) {
            return ($item['price'] * $item['qty']) * 0.15;
        });
        
        // Calculate shipping tax
        $shippingCostWithoutTax = ($orderData['shipping'] ?? 0) / 1.15; // Remove 15% tax
        $shippingTax = ($orderData['shipping'] ?? 0) - $shippingCostWithoutTax;
        $totalTax = $lineItemsTax + $shippingTax;
        
        // Get the tax rate ID for VAT
        $taxRateId = \DB::connection('woocommerce')->table('woocommerce_tax_rates')
            ->where('tax_rate_name', 'VAT')
            ->where('tax_rate', '15.0000')
            ->value('tax_rate_id') ?? 1;
        
        $total = $subtotal - ($orderData['discount'] ?? 0) + ($orderData['shipping'] ?? 0) + $totalTax;

        // Prepare line items for WooCommerce API
        $lineItems = collect($items)->map(function ($item) {
            $subtotal = $item['price'] * $item['qty'];
            $tax = $subtotal * 0.15;
            
            return [
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? 0,
                'quantity' => $item['qty'],
                'total' => number_format($subtotal, 2, '.', ''), // Tax-exclusive total
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'subtotal_tax' => number_format($tax, 2, '.', ''),
                'total_tax' => number_format($tax, 2, '.', ''),
                'taxes' => [
                    [
                        'total' => number_format($tax, 2, '.', ''),
                        'subtotal' => number_format($tax, 2, '.', ''),
                    ]
                ],
                'meta_data' => [],
                'sku' => $item['sku'] ?? '',
                'price' => number_format($item['price'], 2, '.', ''),
            ];
        })->toArray();

        // Prepare billing address
        $billingAddress = $this->prepareAddress($orderData, 'billing');
        
        // Prepare shipping address - use RedBox point address if RedBox is selected
        $shippingAddress = $this->prepareAddress($orderData, 'shipping');
        
        // Override shipping address with RedBox point address if RedBox is selected
        if (!empty($orderData['redbox_point_id']) && !empty($orderData['redbox_point'])) {
            $redboxPointData = json_decode($orderData['redbox_point'], true);
            if (!$redboxPointData) {
                // If not JSON, try to parse the string format: "Point Name - City - District - Street"
                $parts = explode(' - ', $orderData['redbox_point']);
                if (count($parts) >= 4) {
                    $redboxPointData = [
                        'point_name' => trim($parts[0]),
                        'address' => [
                            'city' => trim($parts[1]),
                            'district' => trim($parts[2]),
                            'street' => trim($parts[3])
                        ]
                    ];
                }
            }
            
            if ($redboxPointData && isset($redboxPointData['address'])) {
                $shippingAddress = [
                    'first_name' => $billingAddress['first_name'] ?? '',
                    'last_name' => $billingAddress['last_name'] ?? '',
                    'company' => '',
                    'address_1' => $redboxPointData['address']['street'] ?? '',
                    'address_2' => $redboxPointData['address']['district'] ?? '',
                    'city' => $redboxPointData['address']['city'] ?? '',
                    'state' => $redboxPointData['address']['city'] ?? '', // Use city as state
                    'postcode' => '00000', // Default postcode for RedBox
                    'country' => 'SA', // Saudi Arabia
                    'email' => '',
                    'phone' => $billingAddress['phone'] ?? '',
                ];
                
                Log::info('RedBox shipping address set for API order:', $shippingAddress);
            }
        }

        $wcOrderData = [
            'payment_method' => $orderData['payment_method'] ?? '',
            'payment_method_title' => $this->getPaymentMethodTitle($orderData['payment_method'] ?? ''),
            'set_paid' => false,
            'billing' => $billingAddress,
            'shipping' => $shippingAddress,
            'line_items' => $lineItems,
            'shipping_lines' => ($orderData['shipping'] ?? 0) > 0 ? [
                [
                    'method_id' => $orderData['shipping_method_id'] ?? 'flat_rate',
                    'method_title' => $orderData['shipping_method_title'] ?? 'Flat Rate',
                    'total' => number_format($shippingCostWithoutTax, 2, '.', ''), // Show base shipping cost
                    'total_tax' => number_format($shippingTax, 2, '.', ''),
                    'taxes' => [
                        [
                            'total' => number_format($shippingTax, 2, '.', ''),
                            'subtotal' => number_format($shippingTax, 2, '.', ''),
                        ]
                    ],
                ]
            ] : [],

            'fee_lines' => [],
            'coupon_lines' => [],
            'tax_lines' => [], // WordPress doesn't use separate tax lines
            'refunds' => [],
            'total' => number_format($total, 2, '.', ''),
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'total_tax' => number_format($totalTax, 2, '.', ''),
            'total_shipping' => number_format($orderData['shipping'] ?? 0, 2, '.', ''),
            'total_discount' => number_format($orderData['discount'] ?? 0, 2, '.', ''),
            'customer_note' => $orderData['customer_note'] ?? '',
            'status' => $orderData['order_status'] ?? 'processing',
            'currency' => 'SAR',
            'customer_id' => $orderData['customer_id'] ?? 0,
            'meta_data' => [
                [
                    'key' => '_created_via',
                    'value' => 'laravel_dashboard'
                ],
                [
                    'key' => '_order_key',
                    'value' => 'wc_' . uniqid()
                ]
            ]
        ];

        // Add RedBox meta data if RedBox shipping method is selected
        if (!empty($orderData['redbox_point_id']) && !empty($orderData['redbox_point'])) {
            $wcOrderData['meta_data'][] = [
                'key' => '_redbox_point_id',
                'value' => $orderData['redbox_point_id']
            ];
            $wcOrderData['meta_data'][] = [
                'key' => '_redbox_point',
                'value' => $orderData['redbox_point']
            ];
            
            Log::info('RedBox meta data added to API order:', [
                'redbox_point_id' => $orderData['redbox_point_id'],
                'redbox_point' => $orderData['redbox_point']
            ]);
        }

        return $wcOrderData;
    }

    /**
     * Prepare billing or shipping address
     *
     * @param array $orderData
     * @param string $type
     * @return array
     */
    protected function prepareAddress(array $orderData, string $type)
    {
        $prefix = $type === 'billing' ? 'billing' : 'shipping';
        
        // If we have customer data, use it
        if (!empty($orderData['customer_id'])) {
            $customer = \Makiomar\WooOrderDashboard\Models\Customer::find($orderData['customer_id']);
            if ($customer) {
                $customerMeta = $customer->meta->pluck('meta_value', 'meta_key');
                return [
                    'first_name' => $customerMeta->get($prefix . '_first_name', $customerMeta->get('first_name', '')),
                    'last_name' => $customerMeta->get($prefix . '_last_name', $customerMeta->get('last_name', '')),
                    'company' => $customerMeta->get($prefix . '_company', ''),
                    'address_1' => $customerMeta->get($prefix . '_address_1', ''),
                    'address_2' => $customerMeta->get($prefix . '_address_2', ''),
                    'city' => $customerMeta->get($prefix . '_city', ''),
                    'state' => $customerMeta->get($prefix . '_state', ''),
                    'postcode' => $customerMeta->get($prefix . '_postcode', ''),
                    'country' => $customerMeta->get($prefix . '_country', ''),
                    'email' => $type === 'billing' ? $customer->user_email : '',
                    'phone' => $customerMeta->get($prefix . '_phone', ''),
                ];
            }
        }

        // Return empty address structure
        return [
            'first_name' => '',
            'last_name' => '',
            'company' => '',
            'address_1' => '',
            'address_2' => '',
            'city' => '',
            'state' => '',
            'postcode' => '',
            'country' => '',
            'email' => '',
            'phone' => '',
        ];
    }

    /**
     * Get payment method title
     *
     * @param string $paymentMethod
     * @return string
     */
    protected function getPaymentMethodTitle(string $paymentMethod): string
    {
        if (empty($paymentMethod)) {
            return 'Bank Transfer';
        }

        $titles = [
            'bacs' => 'Bank Transfer',
            'cheque' => 'Check',
            'cod' => 'Cash on Delivery',
            'paypal' => 'PayPal',
            'stripe' => 'Credit Card (Stripe)',
        ];

        return $titles[$paymentMethod] ?? ucwords(str_replace('_', ' ', $paymentMethod));
    }

    /**
     * Make API request to WooCommerce
     *
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     */
    protected function makeApiRequest(string $endpoint, string $method = 'GET', array $data = [])
    {
        $url = $this->baseUrl . '/wp-json/' . $this->version . '/' . $endpoint;
        
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $options = [
            'timeout' => $this->config['timeout'],
            'verify' => $this->config['verify_ssl'],
            'auth' => [$this->consumerKey, $this->consumerSecret],
        ];

        $attempts = 0;
        $maxAttempts = $this->config['max_retries'];

        while ($attempts <= $maxAttempts) {
            try {
                $response = Http::withHeaders($headers)
                    ->withOptions($options)
                    ->timeout($this->config['timeout']);

                if ($method === 'GET') {
                    $response = $response->get($url);
                } elseif ($method === 'POST') {
                    $response = $response->post($url, $data);
                } elseif ($method === 'PUT') {
                    $response = $response->put($url, $data);
                } elseif ($method === 'DELETE') {
                    $response = $response->delete($url);
                }

                return $response;
            } catch (Exception $e) {
                $attempts++;
                Log::warning("API request attempt {$attempts} failed", [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'error' => $e->getMessage()
                ]);

                if ($attempts > $maxAttempts) {
                    throw $e;
                }

                // Wait before retrying
                sleep($this->config['retry_delay']);
            }
        }
    }

    /**
     * Delete an order via WooCommerce REST API
     *
     * @param int $orderId
     * @return bool
     * @throws Exception
     */
    public function deleteOrder(int $orderId): bool
    {
        try {
            Log::info('Deleting order via WooCommerce API', ['order_id' => $orderId]);

            // Make the API request to delete the order
            $response = $this->makeApiRequest("orders/{$orderId}", 'DELETE');

            if ($response->successful()) {
                Log::info('Order deleted successfully via API', ['order_id' => $orderId]);
                return true;
            } else {
                $error = $response->json();
                Log::error('Failed to delete order via API', [
                    'order_id' => $orderId,
                    'status' => $response->status(),
                    'error' => $error
                ]);
                throw new Exception('Failed to delete order via API: ' . ($error['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            Log::error('Exception while deleting order via API', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete multiple orders via WooCommerce REST API
     *
     * @param array $orderIds
     * @return array
     * @throws Exception
     */
    public function deleteOrders(array $orderIds): array
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($orderIds as $orderId) {
            try {
                if ($this->deleteOrder($orderId)) {
                    $results['success'][] = $orderId;
                } else {
                    $results['failed'][] = $orderId;
                }
            } catch (Exception $e) {
                Log::error('Failed to delete order', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage()
                ]);
                $results['failed'][] = $orderId;
            }
        }

        return $results;
    }

    /**
     * Test API connection
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->makeApiRequest('orders');
            return $response->successful();
        } catch (Exception $e) {
            Log::error('API connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
} 