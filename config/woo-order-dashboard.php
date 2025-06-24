<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the database connection settings for WooCommerce integration.
    |
    */
    'db_prefix' => env('WOO_DB_PREFIX', 'wp_'),

    /*
    |--------------------------------------------------------------------------
    | WooCommerce API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the WooCommerce REST API settings for order creation.
    |
    */
    'api' => [
        // Enable/disable WooCommerce API usage for order creation
        'enabled' => env('WOO_API_ENABLED', false),
        
        // WooCommerce site URL
        'site_url' => env('WOO_SITE_URL'),
        
        // Consumer Key (WooCommerce API Key)
        'consumer_key' => env('WOO_CONSUMER_KEY'),
        
        // Consumer Secret (WooCommerce API Secret)
        'consumer_secret' => env('WOO_CONSUMER_SECRET'),
        
        // API version to use
        'version' => env('WOO_API_VERSION', 'wc/v3'),
        
        // Request timeout in seconds
        'timeout' => env('WOO_API_TIMEOUT', 30),
        
        // Maximum retry attempts for failed API calls
        'max_retries' => env('WOO_API_MAX_RETRIES', 3),
        
        // Retry delay in seconds between attempts
        'retry_delay' => env('WOO_API_RETRY_DELAY', 2),
        
        // Enable/disable SSL verification (set to false for self-signed certificates)
        'verify_ssl' => env('WOO_API_VERIFY_SSL', true),
        
        // Default currency
        'default_currency' => env('WOO_API_DEFAULT_CURRENCY', 'USD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure routing settings for the dashboard.
    |
    */
    'routes' => [
        // Enable/disable route registration
        'enabled' => env('WOO_ROUTES_ENABLED', false),

        // Route prefix for all dashboard routes
        'prefix' => env('WOO_ROUTE_PREFIX', 'woo-dashboard'),

        // Middleware to apply to all dashboard routes
        'middleware' => env('WOO_ROUTE_MIDDLEWARE', 'web'),

        // Name prefix for route names
        'name_prefix' => env('WOO_ROUTE_NAME_PREFIX', 'woo.'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the pagination settings for the orders list.
    |
    */
    'pagination' => [
        'per_page' => 15,
        'page_name' => 'page',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching settings for improved performance.
    | Supports multiple cache drivers (file, array, redis, memcached, etc.)
    |
    */
    'cache' => [
        // Enable/disable caching
        'enabled' => env('WOO_CACHE_ENABLED', true),

        // Cache driver to use (falls back to Laravel's default if not specified)
        'driver' => env('WOO_CACHE_DRIVER', env('CACHE_DRIVER', 'file')),

        // Cache prefix to prevent collisions
        'prefix' => env('WOO_CACHE_PREFIX', 'woo_'),

        // Enable cache tags (requires Redis or Memcached)
        'tags_enabled' => env('WOO_CACHE_TAGS_ENABLED', true),

        // Default TTL values in seconds
        'ttl' => [
            'short' => env('WOO_CACHE_TTL_SHORT', 300),    // 5 minutes
            'medium' => env('WOO_CACHE_TTL_MEDIUM', 1800), // 30 minutes
            'long' => env('WOO_CACHE_TTL_LONG', 3600),    // 1 hour
            'extended' => env('WOO_CACHE_TTL_EXTENDED', 86400), // 24 hours
        ],

        // File cache specific settings
        'file' => [
            'path' => storage_path('framework/cache/woocommerce'),
        ],

        // Array cache specific settings (useful for testing)
        'array' => [
            'serialize' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure database performance optimization settings.
    |
    */
    'database' => [
        // Use connection pooling
        'use_connection_pool' => env('WOO_USE_CONNECTION_POOL', true),

        // Minimum connections in the pool
        'min_connections' => env('WOO_MIN_DB_CONNECTIONS', 2),

        // Maximum connections in the pool
        'max_connections' => env('WOO_MAX_DB_CONNECTIONS', 10),

        // Query timeout in seconds
        'query_timeout' => env('WOO_QUERY_TIMEOUT', 5),

        // Maximum retry attempts for failed queries
        'max_retry_attempts' => env('WOO_MAX_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunk Settings
    |--------------------------------------------------------------------------
    |
    | Configure chunk sizes for processing large datasets.
    |
    */
    'chunks' => [
        // Default chunk size for processing large datasets
        'default_size' => env('WOO_DEFAULT_CHUNK_SIZE', 100),

        // Maximum chunk size
        'max_size' => env('WOO_MAX_CHUNK_SIZE', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure performance optimization settings.
    |
    */
    'performance' => [
        // Chunk size for processing large datasets
        'chunk_size' => env('WOO_CHUNK_SIZE', 100),

        // Query timeout in seconds
        'query_timeout' => env('WOO_QUERY_TIMEOUT', 5),

        // Maximum retry attempts for failed queries
        'max_retry_attempts' => env('WOO_MAX_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure monitoring settings.
    |
    */
    'monitoring' => [
        // Enable query logging (development only)
        'query_log_enabled' => env('WOO_QUERY_LOG_ENABLED', false),

        // Log slow queries (in milliseconds)
        'slow_query_threshold' => env('WOO_SLOW_QUERY_THRESHOLD', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Meta Keys for Filtering
    |--------------------------------------------------------------------------
    |
    | Define the available meta keys for filtering orders.
    | Users can extend this list by adding their own custom meta keys.
    | Format: 'meta_key' => 'Display Label'
    |
    */
    'meta_keys' => [
        // Billing Information
        '_billing_first_name' => 'Billing First Name',
        '_billing_last_name' => 'Billing Last Name',
        '_billing_email' => 'Billing Email',
        '_billing_phone' => 'Billing Phone',
        '_billing_address_1' => 'Billing Address 1',
        '_billing_address_2' => 'Billing Address 2',
        '_billing_city' => 'Billing City',
        '_billing_state' => 'Billing State',
        '_billing_postcode' => 'Billing Postcode',
        '_billing_country' => 'Billing Country',
        '_billing_company' => 'Billing Company',
        
        // Shipping Information
        '_shipping_first_name' => 'Shipping First Name',
        '_shipping_last_name' => 'Shipping Last Name',
        '_shipping_address_1' => 'Shipping Address 1',
        '_shipping_address_2' => 'Shipping Address 2',
        '_shipping_city' => 'Shipping City',
        '_shipping_state' => 'Shipping State',
        '_shipping_postcode' => 'Shipping Postcode',
        '_shipping_country' => 'Shipping Country',
        '_shipping_company' => 'Shipping Company',
        
        // Payment Information
        '_payment_method' => 'Payment Method',
        '_payment_method_title' => 'Payment Method Title',
        '_transaction_id' => 'Transaction ID',
        '_order_currency' => 'Order Currency',
        '_order_total' => 'Order Total',
        '_order_tax' => 'Order Tax',
        '_order_shipping' => 'Order Shipping',
        '_cart_discount' => 'Cart Discount',
        '_order_discount' => 'Order Discount',
        
        // Customer Information
        '_customer_user' => 'Customer User ID',
        '_customer_ip_address' => 'Customer IP Address',
        '_customer_user_agent' => 'Customer User Agent',
        
        // Order Information
        '_order_key' => 'Order Key',
        '_order_version' => 'Order Version',
        '_prices_include_tax' => 'Prices Include Tax',
        '_tax_display_cart' => 'Tax Display Cart',
        '_order_stock_reduced' => 'Order Stock Reduced',
        
        // Custom/Third-party Meta Keys
        '_wcpdf_invoice_number' => 'PDF Invoice Number',
        'odoo_order_number' => 'Odoo Order Number',
        '_wc_order_attribution_tracking_id' => 'Order Attribution Tracking ID',
        '_wc_order_attribution_utm_source' => 'Order Attribution UTM Source',
        '_wc_order_attribution_utm_medium' => 'Order Attribution UTM Medium',
        '_wc_order_attribution_utm_campaign' => 'Order Attribution UTM Campaign',
        
        // WooCommerce Subscriptions (if applicable)
        '_subscription_id' => 'Subscription ID',
        '_subscription_status' => 'Subscription Status',
        
        // WooCommerce Bookings (if applicable)
        '_booking_id' => 'Booking ID',
        '_booking_start' => 'Booking Start Date',
        '_booking_end' => 'Booking End Date',
        
        // Custom Meta Keys (extensible)
        // Add your custom meta keys here:
        // '_custom_field' => 'Custom Field Label',
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Key Categories
    |--------------------------------------------------------------------------
    |
    | Group meta keys into categories for better organization in the dropdown.
    | This helps users find the right meta key more easily.
    |
    */
    'meta_key_categories' => [
        'billing' => [
            'label' => 'Billing Information',
            'keys' => [
                '_billing_first_name', '_billing_last_name', '_billing_email', '_billing_phone',
                '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_state',
                '_billing_postcode', '_billing_country', '_billing_company'
            ]
        ],
        'shipping' => [
            'label' => 'Shipping Information',
            'keys' => [
                '_shipping_first_name', '_shipping_last_name', '_shipping_address_1', '_shipping_address_2',
                '_shipping_city', '_shipping_state', '_shipping_postcode', '_shipping_country', '_shipping_company'
            ]
        ],
        'payment' => [
            'label' => 'Payment Information',
            'keys' => [
                '_payment_method', '_payment_method_title', '_transaction_id', '_order_currency',
                '_order_total', '_order_tax', '_order_shipping', '_cart_discount', '_order_discount'
            ]
        ],
        'customer' => [
            'label' => 'Customer Information',
            'keys' => [
                '_customer_user', '_customer_ip_address', '_customer_user_agent'
            ]
        ],
        'order' => [
            'label' => 'Order Information',
            'keys' => [
                '_order_key', '_order_version', '_prices_include_tax', '_tax_display_cart', '_order_stock_reduced'
            ]
        ],
        'custom' => [
            'label' => 'Custom Fields',
            'keys' => [
                '_wcpdf_invoice_number', 'odoo_order_number', '_wc_order_attribution_tracking_id',
                '_wc_order_attribution_utm_source', '_wc_order_attribution_utm_medium', '_wc_order_attribution_utm_campaign'
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Format Configuration
    |--------------------------------------------------------------------------
    */
    'date_format' => [
        'display' => 'Y-m-d H:i:s',
        'api' => 'Y-m-d\TH:i:s',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported WooCommerce Versions
    |--------------------------------------------------------------------------
    */
    'supported_versions' => [
        '9.3.3',
        '9.9.3',
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Statuses
    |--------------------------------------------------------------------------
    |
    | Define the available order statuses for WooCommerce orders.
    | These statuses are used in the order filters and displays.
    | The statuses are now fetched dynamically using WooOrderStatusHelper.
    |
    */
    'default_order_statuses' => [
        'pending' => 'Pending payment',
        'processing' => 'Processing',
        'on-hold' => 'On hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
        'failed' => 'Failed',
        'checkout-draft' => 'Checkout draft',
        'auto-draft' => 'Auto draft',
    ],

    'order_statuses' => function() {
        return array_keys(app(\Makiomar\WooOrderDashboard\Helpers\Orders\StatusHelper::class)->getAllStatuses());
    },

    /*
    |--------------------------------------------------------------------------
    | Status Colors
    |--------------------------------------------------------------------------
    |
    | Map order statuses to Bootstrap color classes for visual representation.
    | Default color mappings for common statuses.
    |
    */
    'status_colors' => [
        'pending'    => 'warning', // For "Pending payment"
        'processing' => 'primary',
        'on-hold'    => 'info',    // For "On hold"
        'completed'  => 'success',
        'cancelled'  => 'danger',
        'refunded'   => 'secondary',
        'failed'     => 'danger',
        'default'    => 'secondary', // Fallback color for any undefined status
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets Configuration
    |--------------------------------------------------------------------------
    |
    | Configure external assets loading settings.
    |
    */
    'assets' => [
        // Enable/disable Bootstrap CSS loading
        'bootstrap_css_enabled' => env('WOO_BOOTSTRAP_CSS_ENABLED', false),

        // Bootstrap CSS CDN URL
        'bootstrap_css_url' => env('WOO_BOOTSTRAP_CSS_URL', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css'),

        // Enable/disable Font Awesome loading
        'fontawesome_enabled' => env('WOO_FONTAWESOME_ENABLED', true),

        // Font Awesome CDN URL
        'fontawesome_url' => env('WOO_FONTAWESOME_URL', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'),
    ],
]; 