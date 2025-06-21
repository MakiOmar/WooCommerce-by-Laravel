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
    */
    'meta_keys' => [
        'billing_phone',
        '_wcpdf_invoice_number',
        'odoo_order_number',
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
]; 