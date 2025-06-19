# WooCommerce Order Dashboard for Laravel

A powerful Laravel package that provides a clean and efficient dashboard for managing WooCommerce orders. This package is designed to work seamlessly with your existing WooCommerce installation while providing enhanced performance and functionality.

## Features

- 🚀 High-performance order management
- 📊 Real-time order statistics and analytics
- 🔍 Advanced order filtering and search
- 🎯 Smart caching system with multiple driver support
- 🛠️ Comprehensive helper classes for WooCommerce data
- 📱 Responsive and modern UI
- 🔒 Safe integration with WooCommerce (no database modifications by default)
- 📦 Optional database optimizations for new installations
- 🛣️ Flexible routing with optional route registration

## Requirements

- PHP 7.4 or higher
- Laravel 8.0 or higher
- WordPress with WooCommerce installed
- MySQL 5.7+ or MariaDB 10.2+

## Installation

1. Install the package via Composer:

```bash
composer require makiomar/woo-order-dashboard
```

2. Publish the configuration:

```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider"
```

3. Configure your database connection in `.env`:

```env
WOO_DB_PREFIX=wp_  # Your WordPress table prefix
```

## Route Configuration

By default, the package's routes are disabled to give you full control over routing. To enable and configure the routes:

1. Enable routes in your `.env`:
```env
WOO_ROUTES_ENABLED=true
```

2. Configure route settings (optional):
```env
WOO_ROUTE_PREFIX=woo-dashboard
WOO_ROUTE_MIDDLEWARE=web
WOO_ROUTE_NAME_PREFIX=woo.
```

3. Publish and customize routes (optional):
```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="routes"
```

This will create `routes/woo-dashboard.php` which you can customize.

4. Available Routes (when enabled):
```
GET /woo-dashboard/orders          - List orders
GET /woo-dashboard/orders/{id}     - View order details
GET /woo-dashboard/statistics      - View dashboard statistics
```

You can also register routes manually in your application's route file:

```php
use Makiomar\WooOrderDashboard\Http\Controllers\WooOrderDashboardController;

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('/my-custom-path/orders', [WooOrderDashboardController::class, 'index'])->name('custom.orders.index');
    Route::get('/my-custom-path/orders/{id}', [WooOrderDashboardController::class, 'show'])->name('custom.orders.show');
});
```

## Optional Database Optimizations

The package includes optional database optimizations that can be used for new WooCommerce installations. These are disabled by default to ensure compatibility with existing installations.

To use the optional database optimizations:

1. Publish the optional migrations:
```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="optional-migrations"
```

2. Review the migrations in `database/migrations/optional`:
   - These migrations add performance-optimizing indexes
   - They are safe for new installations
   - Use with caution on existing installations

3. To apply the optional migrations:
```bash
php artisan migrate --path=database/migrations/optional
```

Note: Always backup your database before applying any migrations to an existing WooCommerce installation.

## Configuration

### Basic Configuration

The package supports extensive configuration through environment variables or the `config/woo-order-dashboard.php` file:

```php
return [
    'db_prefix' => env('WOO_DB_PREFIX', 'wp_'),
    
    'cache' => [
        'enabled' => env('WOO_CACHE_ENABLED', true),
        'driver' => env('WOO_CACHE_DRIVER', 'file'),
        'prefix' => env('WOO_CACHE_PREFIX', 'woo_'),
        'tags_enabled' => env('WOO_CACHE_TAGS_ENABLED', true),
    ],
    // ... more configuration options
];
```

### Cache Configuration

The package supports multiple cache drivers with automatic fallbacks:

```env
# Cache Settings
WOO_CACHE_ENABLED=true
WOO_CACHE_DRIVER=file  # Options: file, redis, memcached, array
WOO_CACHE_PREFIX=woo_
WOO_CACHE_TAGS_ENABLED=false  # Set to true if using Redis/Memcached

# Cache TTL Settings (in seconds)
WOO_CACHE_TTL_SHORT=300    # 5 minutes
WOO_CACHE_TTL_MEDIUM=1800  # 30 minutes
WOO_CACHE_TTL_LONG=3600    # 1 hour
WOO_CACHE_TTL_EXTENDED=86400  # 24 hours
```

### Performance Settings

Configure performance-related settings:

```env
# Chunk size for processing large datasets
WOO_CHUNK_SIZE=100

# Query timeout and retry settings
WOO_QUERY_TIMEOUT=5
WOO_MAX_RETRY_ATTEMPTS=3

# Monitoring
WOO_QUERY_LOG_ENABLED=false
WOO_SLOW_QUERY_THRESHOLD=1000
```

## Usage

### Order Management

```php
use Makiomar\WooOrderDashboard\Helpers\Orders\OrderHelper;

// Get orders with filters
$orders = OrderHelper::getOrders([
    'status' => ['processing', 'completed'],
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31'
]);

// Get order details
$orderItems = OrderHelper::getOrderItems($orderId);
$orderMeta = OrderHelper::getOrderMeta($orderId);

// Get order statistics
$stats = OrderHelper::getOrderStats('2024-01-01', '2024-12-31');
```

### Helper Classes

The package provides several helper classes for different aspects of WooCommerce:

```php
use Makiomar\WooOrderDashboard\Helpers\Orders\OrderHelper;
use Makiomar\WooOrderDashboard\Helpers\Products\ProductHelper;
use Makiomar\WooOrderDashboard\Helpers\Customers\CustomerHelper;

// Order operations
$orders = OrderHelper::getOrders($filters);

// Product operations
$products = ProductHelper::getProducts($filters);

// Customer operations
$customers = CustomerHelper::getCustomers($filters);
```

### Performance Optimization

The package includes built-in performance optimizations:

1. **Smart Caching**:
   - Automatic cache key generation
   - Support for multiple cache drivers
   - Intelligent cache invalidation
   - Configurable TTL for different types of data

2. **Query Optimization**:
   - Efficient use of existing WooCommerce indexes
   - Chunked processing for large datasets
   - Query retry mechanism for better reliability

3. **Memory Management**:
   - Automatic chunk processing for large datasets
   - Memory-efficient collection handling
   - Configurable chunk sizes

## Views and Assets

The package includes pre-built views and assets:

- Order listing view
- Order detail view
- Dashboard statistics
- Filtering components
- Modern, responsive UI

To customize the views, publish them:

```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="views"
```

## Best Practices

1. **Cache Configuration**:
   - Use Redis or Memcached in production for best performance
   - File cache works well for smaller sites
   - Configure TTL values based on your needs

2. **Performance Tuning**:
   - Adjust chunk sizes based on your server capacity
   - Monitor slow queries and adjust thresholds
   - Use appropriate cache TTL values

3. **Error Handling**:
   - Monitor logs for cache and query errors
   - Configure proper retry attempts for queries
   - Set appropriate timeouts

## Troubleshooting

### Cache Issues

If experiencing cache-related issues:

1. Check if caching is enabled:
```env
WOO_CACHE_ENABLED=true
```

2. Try different cache drivers:
```env
WOO_CACHE_DRIVER=file  # or redis, memcached
```

3. Clear the cache:
```bash
php artisan cache:clear
```

### Performance Issues

If experiencing performance issues:

1. Adjust chunk sizes:
```env
WOO_CHUNK_SIZE=50  # Decrease if memory issues
```

2. Monitor slow queries:
```env
WOO_QUERY_LOG_ENABLED=true
WOO_SLOW_QUERY_THRESHOLD=1000
```

3. Check cache configuration:
```env
WOO_CACHE_TTL_MEDIUM=1800  # Adjust based on needs
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the MIT license. 