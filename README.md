# Laravel WooCommerce Order Dashboard

A powerful Laravel package that provides a comprehensive dashboard for managing WooCommerce orders. This package allows you to integrate WooCommerce order management capabilities into any Laravel application.

## Features

- Complete WooCommerce order management dashboard
- Advanced filtering capabilities:
  - Order ID
  - Customer information
  - Date range filtering
  - Order status
  - Custom meta key filtering:
    - Billing phone
    - Invoice number (_wcpdf_invoice_number)
    - Odoo order number (odoo_order_number)
- Extensible meta key filtering system
- Compatible with WooCommerce versions 9.3.3 and 9.9.3
- Built with Laravel Blade templates
- RESTful API endpoints for order management

## Requirements

- PHP >= 8.1
- Laravel >= 10.0
- WooCommerce >= 9.3.3
- Composer

## Installation

1. Install the package via Composer:

```bash
composer require your-vendor/woo-order-dashboard
```

2. Publish the package assets and configuration:

```bash
php artisan vendor:publish --provider="YourVendor\WooOrderDashboard\WooOrderDashboardServiceProvider"
```

3. Run the migrations:

```bash
php artisan migrate
```

4. Add the following to your `.env` file:

```env
WOO_CONSUMER_KEY=your_consumer_key
WOO_CONSUMER_SECRET=your_consumer_secret
WOO_STORE_URL=your_store_url
```

## Configuration

The package configuration file will be published to `config/woo-order-dashboard.php`. Here you can customize:

- API endpoints
- Default pagination settings
- Available meta keys for filtering
- Cache settings
- Custom date formats

## Usage

### Basic Implementation

Add the following to your routes file (`routes/web.php`):

```php
use YourVendor\WooOrderDashboard\WooOrderDashboard;

Route::prefix('woo-dashboard')->group(function () {
    Route::get('/', [WooOrderDashboard::class, 'index'])->name('woo.dashboard');
    Route::get('/orders', [WooOrderDashboard::class, 'orders'])->name('woo.orders');
    Route::get('/orders/{id}', [WooOrderDashboard::class, 'show'])->name('woo.orders.show');
});
```

### Accessing the Dashboard

Visit `/woo-dashboard` in your browser to access the order management interface.

### Filtering Orders

The dashboard supports various filtering methods:

1. By Order ID:
```php
/woo-dashboard/orders?order_id=123
```

2. By Date Range:
```php
/woo-dashboard/orders?start_date=2024-01-01&end_date=2024-03-20
```

3. By Status:
```php
/woo-dashboard/orders?status=processing
```

4. By Meta Keys:
```php
/woo-dashboard/orders?meta_key=billing_phone&meta_value=1234567890
```

## Extending Meta Keys

To add new meta keys for filtering:

1. Edit the `config/woo-order-dashboard.php` file
2. Add your new meta key to the `meta_keys` array:

```php
'meta_keys' => [
    'billing_phone',
    '_wcpdf_invoice_number',
    'odoo_order_number',
    'your_new_meta_key',
],
```

## API Endpoints

The package provides the following API endpoints:

- `GET /api/woo/orders` - List all orders
- `GET /api/woo/orders/{id}` - Get specific order
- `GET /api/woo/orders/filter` - Filter orders
- `GET /api/woo/orders/meta-keys` - List available meta keys

## Security

- All API requests are authenticated using WooCommerce API credentials
- CSRF protection is enabled for all web routes
- Rate limiting is implemented for API endpoints

## Caching

The package implements caching for better performance:

- Order lists are cached for 5 minutes by default
- Individual order details are cached for 10 minutes
- Cache duration can be configured in the config file

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, please open an issue in the GitHub repository or contact support@your-vendor.com.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently. 