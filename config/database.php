<?php

use Illuminate\Support\Str;

return [
    // ... existing code ...

    'connections' => [
        // ... existing connections ...

        'woocommerce' => [
            'driver' => 'mysql',
            'url' => env('WOO_DATABASE_URL'),
            'host' => env('WOO_DB_HOST', '127.0.0.1'),
            'port' => env('WOO_DB_PORT', '3306'),
            'database' => env('WOO_DB_DATABASE', 'forge'),
            'username' => env('WOO_DB_USERNAME', 'forge'),
            'password' => env('WOO_DB_PASSWORD', ''),
            'unix_socket' => env('WOO_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('WOO_DB_PREFIX', 'wp_'),
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
    ],

    // ... rest of the file ...
]; 