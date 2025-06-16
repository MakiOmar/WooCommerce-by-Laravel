<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can specify the database prefix used by your WooCommerce installation.
    | This is typically 'wp_' by default, but may be different if you've changed it.
    |
    */
    'db_prefix' => env('WOO_DB_PREFIX', 'wp_'),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the routes for the WooCommerce Order Dashboard.
    |
    */
    'route_prefix' => env('WOO_ORDER_DASHBOARD_ROUTE_PREFIX', ''),

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
    | Here you can configure the cache settings for the orders data.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 300, // 5 minutes
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
]; 