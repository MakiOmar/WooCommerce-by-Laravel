# WooCommerce Order Dashboard for Laravel

A powerful Laravel package that provides a clean and efficient dashboard for managing WooCommerce orders. This package is designed to work seamlessly with your existing WooCommerce installation while providing enhanced performance and functionality.

## ⚠️ IMPORTANT: Required Database Configuration

**Before using this package, you MUST configure the WooCommerce database connection in your Laravel application. This is a critical step that cannot be skipped.**

**If you encounter "Undefined array key driver" errors, it means you haven't properly configured the database connection.**

**See the [Database Connection Configuration](#-critical-database-connection-configuration) section below for detailed instructions.**

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
- 🌐 **WooCommerce REST API Integration**: Optional API-based order creation for better integration
- **Order Management**: View, create, and manage WooCommerce orders
- **Dynamic Status Management**: Automatically fetch order statuses from WooCommerce database
- **AJAX Tab Loading**: Load order details, customer info, and notes dynamically
- **Status Change**: Change order status directly from the order detail page
- **Performance Optimized**: Cached queries and optimized database operations
- **Responsive Design**: Bootstrap 4 based responsive interface
- **Loading Indicators**: Comprehensive loading state management for AJAX operations

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

2. Publish the package assets and configurations:

```bash
# Publish all package assets
php artisan vendor:publish --tag=woo-order-dashboard

# Or publish specific components:
php artisan vendor:publish --tag=woo-order-dashboard-config
php artisan vendor:publish --tag=woo-order-dashboard-views
php artisan vendor:publish --tag=woo-order-dashboard-assets
php artisan vendor:publish --tag=woo-order-dashboard-migrations
```

3. Run the migrations (optional):

```bash
php artisan migrate --path=vendor/makiomar/woo-order-dashboard/database/migrations
```

4. **⚠️ CRITICAL STEP**: Configure the WooCommerce database connection (see [Database Connection Configuration](#-critical-database-connection-configuration) below)

**This step is REQUIRED and must be completed before using the package.**

## Publishing Commands

The package provides several publish tags for different components:

### Using Provider-Based Commands (Recommended)
```bash
# Publish all package assets
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider"

# Publish specific components:
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="config"
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="views"
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="assets"
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="migrations"
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="routes"
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="pagination"
```

### Using Tag-Based Commands (Alternative)
```bash
# Publish all package assets
php artisan vendor:publish --tag=woo-order-dashboard

# Or publish specific components:
php artisan vendor:publish --tag=woo-order-dashboard-config
php artisan vendor:publish --tag=woo-order-dashboard-views
php artisan vendor:publish --tag=woo-order-dashboard-assets
php artisan vendor:publish --tag=woo-order-dashboard-migrations
php artisan vendor:publish --tag=woo-order-dashboard-routes
```

### Configuration
```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="config"
```
Publishes the configuration file to `config/woo-order-dashboard.php`

### Views
```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="views"
```
Publishes Blade views to `resources/views/vendor/woo-order-dashboard/`

### Assets
```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="assets"
```
Publishes CSS and JS assets to `public/vendor/woo-order-dashboard/`

### Migrations
```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="migrations"
```
Publishes optional database migrations to `database/migrations/optional/`

### Routes
```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="routes"
```
Publishes routes file to `routes/woo-dashboard.php` for customization

### Pagination
```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="pagination"
```
Publishes Bootstrap 4 pagination views to `resources/views/vendor/pagination/` for customization

### Data Files
```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="data"
```
Publishes data files (like continent-country mapping) to `storage/app/woo-order-dashboard/` for customization

### All Assets
```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider"
```
Publishes all package assets (config, views, assets, migrations)

### Available Tags Summary
- `config` - Configuration file
- `views` - Blade templates
- `assets` - CSS and JavaScript files
- `migrations` - Optional database migrations
- `routes` - Routes file for customization
- `pagination` - Bootstrap 4 pagination views
- `data` - Data files (continent-country mapping)
- No tag - All assets (recommended for first-time setup)

## ⚠️ CRITICAL: Database Connection Configuration

**This step is REQUIRED for the package to work properly. Without this configuration, you will get "Undefined array key driver" errors.**

### Step 1: Add WooCommerce Database Connection

You **MUST** add the WooCommerce database connection to your Laravel `config/database.php` file. This is not optional and must be done before using the package.

Open your `config/database.php` file and add the following connection to the `connections` array:

```php
'connections' => [
    // ... your existing connections (mysql, sqlite, etc.)
    
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

### Step 2: Add WooCommerce Database Credentials

Add the following environment variables to your `.env` file:

```env
# WooCommerce Database Configuration (REQUIRED)
WOO_DB_HOST=127.0.0.1
WOO_DB_PORT=3306
WOO_DB_DATABASE=your_woocommerce_db
WOO_DB_USERNAME=your_username
WOO_DB_PASSWORD=your_password
WOO_DB_PREFIX=wp_
```

### Step 3: Verify Configuration

After adding the database connection, you can verify it's working by running:

```bash
php artisan tinker
>>> DB::connection('woocommerce')->getPdo();
```

If you see a PDO object returned, your connection is working correctly.

### Troubleshooting Database Connection Issues

If you encounter "Undefined array key driver" errors:

1. **Check that you added the 'woocommerce' connection to `config/database.php`**
2. **Verify your `.env` file has the correct WooCommerce database credentials**
3. **Clear Laravel's configuration cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```
4. **Restart your web server/development server**

## WooCommerce REST API Configuration (Optional)

The package supports creating orders via the WooCommerce REST API for better integration and compatibility with WooCommerce plugins.

### API Configuration

Add the following environment variables to your `.env` file:

```env
# WooCommerce API Configuration
WOO_API_ENABLED=false
WOO_SITE_URL=https://your-woocommerce-site.com
WOO_CONSUMER_KEY=ck_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WOO_CONSUMER_SECRET=cs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WOO_API_VERSION=wc/v3
WOO_API_TIMEOUT=30
WOO_API_MAX_RETRIES=3
WOO_API_RETRY_DELAY=2
WOO_API_VERIFY_SSL=true
WOO_API_DEFAULT_CURRENCY=USD
```

### Getting API Credentials

1. **Log in to your WooCommerce admin panel**
2. **Go to WooCommerce → Settings → Advanced → REST API**
3. **Click "Add Key"**
4. **Fill in the details:**
   - Description: "Laravel Dashboard API"
   - User: Select an admin user
   - Permissions: "Read/Write"
5. **Click "Generate API Key"**
6. **Copy the Consumer Key and Consumer Secret**

### Testing the API Connection

Run the following command to test your API configuration:

```bash
php artisan woo:test-api
```

### Usage Modes

#### Database Method (Default)
By default, orders are created and deleted using direct database insertion:
```env
WOO_API_ENABLED=false
```

#### API Method
To use the WooCommerce REST API for order creation and deletion:
```env
WOO_API_ENABLED=true
```

**Note**: Payment method and order status are taken from the form submission, not from environment variables. This ensures that users can select the appropriate payment method and order status for each order.

### Supported Operations

The API integration supports the following operations:

- **Order Creation**: Create new orders via WooCommerce REST API
- **Order Deletion**: Delete orders via WooCommerce REST API (including bulk deletion)
- **Connection Testing**: Test API connectivity before operations

### Benefits

**API Method Benefits:**
- Better integration with WooCommerce plugins
- Automatic cache updates and lookup table management
- Triggers all WooCommerce hooks and filters
- Data validation by WooCommerce

**Database Method Benefits:**
- Faster performance
- Works offline
- More control over data structure
- No API rate limits

For detailed API configuration instructions, see [WOOCOMMERCE_API_CONFIG.md](WOOCOMMERCE_API_CONFIG.md).

## Bootstrap 4 Pagination

The package includes Bootstrap 4 pagination views that are automatically used when you call `{{ $orders->links() }}` or any other pagination links.

### Available Pagination Views

1. **Default Bootstrap 4 Pagination** (`bootstrap-4.blade.php`)
   - Full pagination with page numbers
   - Previous/Next buttons with icons
   - Results count display
   - Centered layout

2. **Simple Bootstrap 4 Pagination** (`simple-bootstrap-4.blade.php`)
   - Previous/Next buttons only
   - Page count display
   - Compact layout

### Using Different Pagination Styles

To use a specific pagination style, you can specify it in your Blade templates:

```php
{{-- Use default Bootstrap 4 pagination --}}
{{ $orders->links() }}

{{-- Use simple pagination --}}
{{ $orders->links('vendor.pagination.simple-bootstrap-4') }}

{{-- Use custom pagination view --}}
{{ $orders->links('vendor.pagination.custom-view') }}
```

### Customizing Pagination

After publishing the pagination views, you can customize them:

```bash
# Publish pagination views
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="pagination"
```

The pagination views will be published to `resources/views/vendor/pagination/` where you can modify them to match your design requirements.

### Pagination Configuration

You can configure pagination settings in your Laravel application:

```php
// In config/pagination.php
return [
    'default' => 'bootstrap-4',
    'path' => resource_path('views/vendor/pagination'),
];
```

### Features

- **Responsive Design**: Works on all screen sizes
- **Accessibility**: Includes proper ARIA labels and semantic HTML
- **FontAwesome Icons**: Uses chevron icons for navigation
- **Bootstrap 4 Classes**: Fully compatible with Bootstrap 4 styling
- **Customizable**: Easy to modify and extend

## Assets Configuration

The package provides flexible control over external asset loading to avoid conflicts with existing CSS frameworks.

### Bootstrap CSS Configuration

By default, Bootstrap CSS is **disabled** to prevent conflicts with existing Bootstrap installations:

```env
# Enable Bootstrap CSS (disabled by default)
WOO_BOOTSTRAP_CSS_ENABLED=false

# Custom Bootstrap CSS URL (optional)
WOO_BOOTSTRAP_CSS_URL=https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css
```

### Font Awesome Configuration

Font Awesome is enabled by default but can be disabled if you have your own icon library:

```env
# Enable Font Awesome (enabled by default)
WOO_FONTAWESOME_ENABLED=true

# Custom Font Awesome URL (optional)
WOO_FONTAWESOME_URL=https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css
```

### Usage Scenarios

#### Scenario 1: Using Your Own Bootstrap
If you already have Bootstrap CSS loaded in your application:

```env
WOO_BOOTSTRAP_CSS_ENABLED=false
```

#### Scenario 2: Using Package Bootstrap
If you want to use the package's Bootstrap CSS:

```env
WOO_BOOTSTRAP_CSS_ENABLED=true
```

#### Scenario 3: Custom Bootstrap Version
If you want to use a different Bootstrap version:

```env
WOO_BOOTSTRAP_CSS_ENABLED=true
WOO_BOOTSTRAP_CSS_URL=https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css
```

### Benefits

- **No Conflicts**: Prevents CSS conflicts with existing frameworks
- **Flexible**: Choose which assets to load
- **Customizable**: Use your own CDN URLs
- **Performance**: Only load what you need

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

# Assets Settings
WOO_BOOTSTRAP_CSS_ENABLED=false
WOO_BOOTSTRAP_CSS_URL=https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css
WOO_FONTAWESOME_ENABLED=true
WOO_FONTAWESOME_URL=https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css

# Shipping Settings
WOO_SHIPPING_CART_TOTAL_FILTERING=true
WOO_SHIPPING_SA_LOW_THRESHOLD=499
WOO_SHIPPING_SA_MEDIUM_THRESHOLD=999
WOO_SHIPPING_SA_LOW_COST=21.74
WOO_SHIPPING_SA_MEDIUM_COST=8.70
WOO_SHIPPING_SA_HIGH_COST=0.00
WOO_SHIPPING_OTHER_LOW_THRESHOLD=499
WOO_SHIPPING_OTHER_MEDIUM_THRESHOLD=999
```

## Shipping Configuration

The package includes advanced shipping method filtering based on cart total, similar to your WordPress theme logic. This feature is **enabled by default** but can be customized or disabled.

### Shipping Method Filtering

The shipping filtering works as follows:

#### For Saudi Arabia (SA):
- **Cart total < 499 SAR**: Shows only methods with cost 21.74 SAR
- **Cart total 499-998 SAR**: Shows only methods with cost 8.70 SAR  
- **Cart total ≥ 999 SAR**: Shows only methods with cost 0.00 SAR (free shipping)

#### For Other Countries:
- **Cart total < 499**: Excludes specific methods based on configuration
- **Cart total 499-998**: Excludes different methods based on configuration
- **Cart total ≥ 999**: Excludes specific methods based on configuration

#### Always Included Methods:
- Redbox pickup delivery methods
- Local pickup methods
- Any methods specified in the configuration

### Disabling Shipping Filtering

To disable cart total-based filtering and show all available shipping methods:

```env
WOO_SHIPPING_CART_TOTAL_FILTERING=false
```

### Customizing Shipping Thresholds

You can customize the cart total thresholds and shipping costs:

```env
# Saudi Arabia thresholds and costs
WOO_SHIPPING_SA_LOW_THRESHOLD=499
WOO_SHIPPING_SA_MEDIUM_THRESHOLD=999
WOO_SHIPPING_SA_LOW_COST=21.74
WOO_SHIPPING_SA_MEDIUM_COST=8.70
WOO_SHIPPING_SA_HIGH_COST=0.00

# Other countries thresholds
WOO_SHIPPING_OTHER_LOW_THRESHOLD=499
WOO_SHIPPING_OTHER_MEDIUM_THRESHOLD=999
```

### Advanced Configuration

For advanced customization, you can modify the `config/woo-order-dashboard.php` file to:
- Change which countries exclude DHL Express
- Modify which methods are excluded for different cart totals
- Add or remove methods that should always be included

## Language Configuration

The package supports multilingual interfaces with Arabic and English. Arabic is the default language.

### Language Settings

```env
# Language Settings
WOO_DEFAULT_LANGUAGE=ar
```

### Available Languages

- **Arabic (ar)**: العربية - Default language with RTL support
- **English (en)**: English - LTR language

### Language Switching

Users can switch languages using the language switcher in the interface. The selected language is stored in the session and persists across requests.

### RTL Support

Arabic language automatically enables RTL (Right-to-Left) layout support, including:
- Text direction
- Form layouts
- Navigation elements
- Table structures

### Adding New Languages

To add new languages:

1. Create language files in `src/Resources/lang/{language_code}/`
2. Add the language code to the configuration
3. Update the language names and RTL settings

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
2. In the "Order Status" section at the top of the page, click the "Change Status" dropdown
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

## Loading Indicators

The package includes a comprehensive loading state management system for AJAX operations, providing visual feedback to users during data loading and form submissions.

### Features

- **Input Loading**: Spinning indicators for search inputs during AJAX requests
- **Button Loading**: Loading states for buttons during form submissions
- **Overlay Loading**: Full-page overlay for major operations
- **Row Loading**: Individual table row loading states
- **Automatic Management**: Centralized loading state management
- **Error Handling**: Proper cleanup on errors

### Demo Page

Visit `/loading-demo` to see all loading indicators in action and test their functionality.

### Usage

#### 1. Include the Loading Utilities

Add the loading utilities script to your views:

```html
<script src="{{ asset('vendor/woo-order-dashboard/js/loading-utils.js') }}"></script>
```

#### 2. HTML Structure

For input loading indicators:

```html
<div class="search-input-container">
    <input type="text" class="form-control" id="product-search" placeholder="Search...">
    <div class="loading-indicator">
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
    </div>
</div>
```

For buttons (no special HTML required):

```html
<button type="button" class="btn btn-primary" id="submit-btn">
    <i class="fas fa-save"></i> Save
</button>
```

#### 3. JavaScript Usage

```javascript
// Show input loading
loadingManager.showInputLoading('#product-search');

// Hide input loading
loadingManager.hideInputLoading('#product-search');

// Show button loading
loadingManager.showButtonLoading('#submit-btn', 'Saving...');

// Hide button loading
loadingManager.hideButtonLoading('#submit-btn');

// Show overlay
loadingManager.showOverlay('Processing data...');

// Hide overlay
loadingManager.hideOverlay();

// Show row loading
loadingManager.showRowLoading('#row-1');

// Hide row loading
loadingManager.hideRowLoading('#row-1');

// Hide all active loaders
loadingManager.hideAll();
```

#### 4. AJAX Integration Example

```javascript
// Product search with loading indicator
$('#product-search').on('input', function() {
    var query = $(this).val();
    if (query.length < 2) return;
    
    // Show loading
    loadingManager.showInputLoading('#product-search');
    
    $.getJSON('/products/search', {q: query})
        .done(function(data) {
            // Handle results
        })
        .fail(function(xhr, status, error) {
            // Handle errors
        })
        .always(function() {
            // Hide loading
            loadingManager.hideInputLoading('#product-search');
        });
});

// Form submission with button loading
$('#order-form').on('submit', function(e) {
    // Show button loading
    loadingManager.showButtonLoading('#submit-btn', 'Creating Order...');
    
    $.post('/orders', $(this).serialize())
        .done(function(response) {
            // Handle success
        })
        .fail(function(xhr) {
            // Handle errors
        })
        .always(function() {
            // Hide button loading
            loadingManager.hideButtonLoading('#submit-btn');
        });
});
```

### Available Methods

#### Input Loading
- `showInputLoading(selector, options)` - Show loading for input field
- `hideInputLoading(selector)` - Hide loading for input field

#### Button Loading
- `showButtonLoading(selector, loadingText)` - Show loading for button
- `hideButtonLoading(selector)` - Hide loading for button

#### Overlay Loading
- `showOverlay(message)` - Show full-page overlay
- `hideOverlay()` - Hide overlay

#### Row Loading
- `showRowLoading(selector)` - Show loading for table row
- `hideRowLoading(selector)` - Hide loading for table row

#### Utility Methods
- `hideAll()` - Hide all active loaders
- `getActiveCount()` - Get number of active loaders

### CSS Classes

The loading system uses the following CSS classes:

- `.loading-indicator` - Base loading indicator container
- `.loading-indicator.show` - Shows the indicator
- `.input-loading` - Applied to inputs during loading
- `.btn-loading` - Applied to buttons during loading
- `.table-row-loading` - Applied to table rows during loading
- `.loading-overlay` - Full-page overlay container
- `.loading-overlay.show` - Shows the overlay

### Integration Points

The loading indicators are already integrated into:

1. **Product Search**: Shows loading during product search AJAX requests
2. **Customer Search**: Shows loading during customer search AJAX requests
3. **Order Creation**: Shows button loading during form submission
4. **Bulk Delete**: Shows button loading during bulk operations
5. **Filter Forms**: Shows button loading during filter submissions

### Customization

You can customize the loading indicators by modifying the CSS in `resources/assets/css/woo-order-dashboard.css`:

```css
/* Custom loading indicator styles */
.loading-indicator {
    /* Your custom styles */
}

.loading-indicator .spinner-border {
    /* Custom spinner styles */
}
```

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