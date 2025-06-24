# WooCommerce Order Dashboard for Laravel

A powerful Laravel package that provides a clean and efficient dashboard for managing WooCommerce orders. This package is designed to work seamlessly with your existing WooCommerce installation while providing enhanced performance and functionality.

## Features

- 🚀 High-performance order management with Eloquent ORM
- 📊 Real-time order statistics and analytics
- 🔍 Advanced order filtering and search
- 🎯 Smart caching system with multiple driver support
- 🛠️ Comprehensive Eloquent models for WooCommerce data
- 📱 Responsive and modern UI
- 🔒 Safe integration with WooCommerce (no database modifications by default)
- 📦 Optional database optimizations for new installations
- 🛣️ Clean, unprefixed routing system
- ✨ Order creation and management interface
- 🔄 Bulk operations support
- **Order Management**: View, create, and manage WooCommerce orders
- **Dynamic Status Management**: Automatically fetch order statuses from WooCommerce database
- **AJAX Tab Loading**: Load order details, customer info, and notes dynamically
- **Status Change**: Change order status directly from the order detail page
- **Performance Optimized**: Cached queries and optimized database operations
- **Responsive Design**: Bootstrap 4 based responsive interface

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

3. Configure your WooCommerce database connection in `config/database.php`:

```php
'connections' => [
    // ... other connections
    
    'woocommerce' => [
        'driver' => 'mysql',
        'host' => env('WOO_DB_HOST', env('DB_HOST', '127.0.0.1')),
        'port' => env('WOO_DB_PORT', env('DB_PORT', '3306')),
        'database' => env('WOO_DB_DATABASE', env('DB_DATABASE')),
        'username' => env('WOO_DB_USERNAME', env('DB_USERNAME')),
        'password' => env('WOO_DB_PASSWORD', env('DB_PASSWORD')),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => env('WOO_DB_PREFIX', 'wp_'),
        'strict' => true,
        'engine' => null,
    ],
],
```

4. Add WooCommerce database credentials to your `.env`:

```env
WOO_DB_HOST=127.0.0.1
WOO_DB_PORT=3306
WOO_DB_DATABASE=your_woocommerce_db
WOO_DB_USERNAME=your_username
WOO_DB_PASSWORD=your_password
WOO_DB_PREFIX=wp_
```

## Route Configuration

The package provides clean, unprefixed routes for easy integration:

### Available Routes

```php
// Order Management
GET /orders                    - List all orders (orders.index)
GET /orders/{id}              - View order details (orders.show)
GET /orders/create            - Create new order form (orders.create)
POST /orders                  - Store new order (orders.store)

// AJAX Search Routes
GET /products/search          - Search products (products.search)
GET /customers/search         - Search customers (customers.search)

// Bulk Operations
POST /orders/bulk-delete      - Delete multiple orders (orders.bulk-delete)
```

### Route Registration

Routes are automatically registered with the following middleware:
- `web` - Web middleware group
- `auth:admin` - Authentication middleware (customize as needed)

You can customize the middleware in `routes/web.php`:

```php
Route::group(['middleware' => ['web', 'auth:admin']], function() {
    // Your custom routes here
});
```

## Database Architecture

This package uses Eloquent ORM models to interact with WooCommerce data, providing a more Laravel-native approach:

### Core Models

- `Order` - WooCommerce orders (posts table)
- `OrderItem` - Order line items (woocommerce_order_items table)
- `OrderItemMeta` - Order item metadata (woocommerce_order_itemmeta table)
- `Product` - WooCommerce products (posts table)
- `Customer` - WooCommerce customers (users table)
- `PostMeta` - WordPress post metadata (postmeta table)
- `UserMeta` - WordPress user metadata (usermeta table)
- `Comment` - WordPress comments (comments table)

### Database Compatibility

The package supports both traditional WooCommerce installations (using `posts` table for orders) and High-Performance Order Storage (HPOS) setups. The models automatically adapt to your database schema.

## Configuration

### Basic Configuration

The package supports extensive configuration through the `config/woo-order-dashboard.php` file:

```php
return [
    'cache' => [
        'enabled' => env('WOO_CACHE_ENABLED', true),
        'driver' => env('WOO_CACHE_DRIVER', 'file'),
        'prefix' => env('WOO_CACHE_PREFIX', 'woo_'),
        'ttl' => [
            'order' => env('WOO_CACHE_TTL_ORDER', 60),
            'product' => env('WOO_CACHE_TTL_PRODUCT', 300),
            'customer' => env('WOO_CACHE_TTL_CUSTOMER', 300),
        ],
    ],
    
    'order_statuses' => [
        'pending', 'processing', 'on-hold', 'completed', 
        'cancelled', 'refunded', 'failed'
    ],
    
    'status_colors' => [
        'pending' => 'warning',
        'processing' => 'info',
        'on-hold' => 'secondary',
        'completed' => 'success',
        'cancelled' => 'danger',
        'refunded' => 'info',
        'failed' => 'danger',
    ],
    
    'meta_keys' => [
        '_order_total' => 'Order Total',
        '_billing_first_name' => 'Billing First Name',
        '_billing_last_name' => 'Billing Last Name',
        '_billing_email' => 'Billing Email',
        '_billing_phone' => 'Billing Phone',
        '_shipping_first_name' => 'Shipping First Name',
        '_shipping_last_name' => 'Shipping Last Name',
    ],
];
```

### Environment Variables

```env
# Cache Settings
WOO_CACHE_ENABLED=true
WOO_CACHE_DRIVER=file
WOO_CACHE_PREFIX=woo_
WOO_CACHE_TTL_ORDER=60
WOO_CACHE_TTL_PRODUCT=300
WOO_CACHE_TTL_CUSTOMER=300

# Database Settings
WOO_DB_HOST=127.0.0.1
WOO_DB_PORT=3306
WOO_DB_DATABASE=your_woocommerce_db
WOO_DB_USERNAME=your_username
WOO_DB_PASSWORD=your_password
WOO_DB_PREFIX=wp_
```

## Status Management

The package includes a comprehensive `StatusHelper` that dynamically manages WooCommerce order statuses by merging predefined default statuses with custom statuses from your database.

### StatusHelper Features

The `StatusHelper` provides several methods for managing order statuses:

```php
use Makiomar\WooOrderDashboard\Helpers\Orders\StatusHelper;

// Get all statuses (merged: default + database)
$allStatuses = StatusHelper::getAllStatuses();

// Get only predefined default statuses
$defaultStatuses = StatusHelper::getDefaultStatuses();

// Get only database statuses
$databaseStatuses = StatusHelper::getDatabaseStatuses();

// Get statuses with metadata (custom vs default, color classes)
$statusesWithMetadata = StatusHelper::getAllStatusesWithMetadata();

// Get statuses with wc- prefix for database queries
$statusesWithPrefix = StatusHelper::getAllStatusesWithPrefix();

// Prefix handling methods
$statusWithPrefix = StatusHelper::getStatusWithPrefix('processing'); // Returns 'wc-processing'
$statusWithoutPrefix = StatusHelper::removeStatusPrefix('wc-processing'); // Returns 'processing'

// Get status label by key
$label = StatusHelper::getStatusLabel('processing');

// Check if status exists
$exists = StatusHelper::statusExists('custom-status');

// Check if status is custom (not in default statuses)
$isCustom = StatusHelper::isCustomStatus('custom-status');
```

### Default Statuses

The package includes predefined default statuses that are always available:

```php
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
```

### Custom Statuses

Custom statuses are automatically detected from your WooCommerce database and merged with the default statuses. Database statuses take precedence over default statuses with the same key.

### WooCommerce Status Prefix Handling

WooCommerce stores order statuses in the database with a `wc-` prefix (e.g., `wc-processing`, `wc-completed`). The StatusHelper provides methods to handle this prefix automatically:

```php
// Add wc- prefix for database queries
$statusWithPrefix = StatusHelper::getStatusWithPrefix('processing'); // Returns 'wc-processing'

// Remove wc- prefix for display
$statusWithoutPrefix = StatusHelper::removeStatusPrefix('wc-processing'); // Returns 'processing'

// Get all statuses with wc- prefix for database operations
$statusesWithPrefix = StatusHelper::getAllStatusesWithPrefix();
```

This ensures consistent handling of WooCommerce status prefixes throughout your application.

### Status Metadata

The `getAllStatusesWithMetadata()` method provides detailed information about each status:

```php
$statusesWithMetadata = StatusHelper::getAllStatusesWithMetadata();

// Example output:
[
    'processing' => [
        'label' => 'Processing',
        'is_custom' => false,
        'is_default' => true,
        'color_class' => 'primary',
    ],
    'custom-status' => [
        'label' => 'Custom Status',
        'is_custom' => true,
        'is_default' => false,
        'color_class' => 'secondary',
    ],
]
```

### Caching

All status queries are cached for 1 hour to improve performance. The cache is automatically invalidated when the helper methods are called.

## Cache Management

The package implements comprehensive cache clearing strategies to ensure data consistency:

### Cache Clearing Triggers

- **Order Creation**: Clears order list cache and statistics cache
- **Order Updates**: Clears specific order cache and order list cache
- **Order Deletion**: Clears specific order caches and order list cache
- **Status Changes**: Clears specific order cache, order list cache, and status cache

### Cache Helper Methods

```php
// Clear all order-related cache
CacheHelper::clearOrderCache();

// Clear specific order cache
CacheHelper::clearOrderCacheById($orderId);

// Clear cache on specific events
CacheHelper::clearCacheOnOrderCreate();
CacheHelper::clearCacheOnOrderUpdate($orderId);
CacheHelper::clearCacheOnOrderDelete($orderIds);
CacheHelper::clearCacheOnOrderStatusChange($orderId);
```

### Manual Cache Clearing

You can manually clear cache using the helper methods:

```php
use Makiomar\WooOrderDashboard\Helpers\CacheHelper;

// Clear all WooCommerce dashboard cache
CacheHelper::clearAllWooCommerceCache();

// Clear specific cache types
CacheHelper::clearOrderListCache();
CacheHelper::clearStatusCache();
CacheHelper::clearStatisticsCache();
```

## Performance Optimizations

## Usage

### Order Management with Eloquent Models

```php
use Makiomar\WooOrderDashboard\Models\Order;
use Makiomar\WooOrderDashboard\Models\Product;
use Makiomar\WooOrderDashboard\Models\Customer;

// Get orders with relationships
$orders = Order::with(['meta', 'items.meta', 'comments'])
    ->where('post_status', 'wc-completed')
    ->orderBy('post_date_gmt', 'desc')
    ->paginate(15);

// Get order details
$order = Order::with(['meta', 'items.meta', 'comments'])->find($orderId);

// Get order meta
$orderTotal = $order->meta->where('meta_key', '_order_total')->first()->meta_value ?? 0;

// Get order items
$orderItems = $order->items;

// Search products
$products = Product::where('post_title', 'like', '%search term%')
    ->where('post_type', 'product')
    ->get();

// Get customers
$customers = Customer::with('meta')->get();
```

### Creating Orders

```php
use Makiomar\WooOrderDashboard\Models\Order;
use Makiomar\WooOrderDashboard\Models\OrderItem;

// Create a new order
$order = new Order();
$order->post_title = 'Order &ndash; January 1, 2024 @ 12:00 PM';
$order->post_content = '';
$order->post_status = 'wc-processing';
$order->post_type = 'shop_order';
$order->post_date = now();
$order->post_date_gmt = now()->utc();
$order->post_modified = now();
$order->post_modified_gmt = now()->utc();
$order->save();

// Add order meta
$order->meta()->create([
    'meta_key' => '_order_total',
    'meta_value' => 99.99
]);

// Add order items
$orderItem = new OrderItem();
$orderItem->order_id = $order->ID;
$orderItem->order_item_name = 'Product Name';
$orderItem->order_item_type = 'line_item';
$orderItem->save();
```

### Order Status Management

The package provides dynamic status management with the ability to change order statuses directly from the order detail page:

#### Viewing Order Status
- Order statuses are automatically fetched from the WooCommerce database
- Statuses are displayed with color-coded badges
- Both default and custom statuses are supported

#### Changing Order Status
1. Navigate to an order detail page (`/orders/{id}`)
2. In the order header section, click the "Change Status" dropdown next to the current status badge
3. Select the new status from the dropdown
4. Confirm the status change
5. The status will be updated via AJAX with automatic cache clearing

#### Status Change Features
- **Confirmation Dialog**: Prevents accidental status changes
- **Loading States**: Visual feedback during status updates
- **Success/Error Messages**: Clear feedback on operation results
- **Automatic Cache Clearing**: Ensures data consistency
- **Order Notes**: Status changes are automatically logged as order notes
- **Page Refresh**: Ensures all data is updated after status change

### AJAX Tab Loading

## Views and Assets

The package includes pre-built views and assets:

- Order listing view with advanced filtering
- Order detail view with comprehensive information
- Order creation interface with product/customer search
- Bulk operations interface
- Modern, responsive UI with Bootstrap 4

To customize the views, publish them:

```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="views"
```

## Recent Updates

### Version 2.0 - Architectural Refactoring

This version includes significant architectural improvements:

1. **Eloquent ORM Integration**: Replaced service-based architecture with native Laravel Eloquent models
2. **Clean Routing**: Removed route prefixes for simpler, more intuitive URLs
3. **Enhanced Order Management**: Added order creation and bulk operations
4. **Improved Performance**: Better caching and query optimization
5. **Modern UI**: Updated interface with better UX and responsive design

### Migration from Service-Based to Model-Based Architecture

The package has been refactored to use Laravel's Eloquent ORM instead of custom service classes. This provides:

- Better integration with Laravel's ecosystem
- More intuitive data access patterns
- Improved performance through Eloquent's query optimization
- Easier testing and maintenance

## Best Practices

1. **Database Configuration**:
   - Use separate database connections for WooCommerce and Laravel
   - Configure proper table prefixes
   - Ensure proper indexing for performance

2. **Caching Strategy**:
   - Use Redis or Memcached in production
   - Configure appropriate TTL values
   - Clear cache when WooCommerce data changes

3. **Performance Optimization**:
   - Use eager loading for relationships
   - Implement pagination for large datasets
   - Monitor query performance

4. **Security**:
   - Implement proper authentication middleware
   - Validate all user inputs
   - Use CSRF protection for forms

## Troubleshooting

### Database Connection Issues

If experiencing database connection issues:

1. Check your WooCommerce database configuration:
```env
WOO_DB_HOST=127.0.0.1
WOO_DB_DATABASE=your_woocommerce_db
WOO_DB_USERNAME=your_username
WOO_DB_PASSWORD=your_password
WOO_DB_PREFIX=wp_
```

2. Verify the database connection:
```bash
php artisan tinker
>>> DB::connection('woocommerce')->getPdo();
```

### Route Issues

If routes are not working:

1. Check if routes are properly registered in `routes/web.php`
2. Verify middleware configuration
3. Clear route cache:
```bash
php artisan route:clear
```

### Performance Issues

If experiencing performance issues:

1. Check cache configuration:
```env
WOO_CACHE_ENABLED=true
WOO_CACHE_DRIVER=redis
```

2. Monitor database queries:
```php
DB::connection('woocommerce')->enableQueryLog();
```

3. Use eager loading for relationships:
```php
$orders = Order::with(['meta', 'items.meta'])->get();
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the MIT license. 