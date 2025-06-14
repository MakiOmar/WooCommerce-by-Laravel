# WooCommerce Order Dashboard

A Laravel package for managing and displaying WooCommerce orders with advanced filtering and data visualization capabilities.

## Features

- Comprehensive order management dashboard
- Advanced filtering by date, status, and custom meta fields
- Detailed order view with all WooCommerce data
- Responsive design with modern UI
- Direct database integration with WooCommerce
- Secure connection handling for live sites
- Caching support for better performance

## Requirements

- PHP >= 8.1
- Laravel >= 10.0
- WooCommerce >= 6.0
- MySQL >= 5.7

## Installation

1. Install the package via Composer:

```bash
composer require makiomar/woo-order-dashboard
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="woo-order-dashboard-config"
```

3. Publish the views:

```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="woo-order-dashboard-views"
```

4. Publish the assets:

```bash
php artisan vendor:publish --provider="Makiomar\WooOrderDashboard\WooOrderDashboardServiceProvider" --tag="woo-order-dashboard-assets"
```

## Configuration

### Database Connection

The package uses a separate database connection for WooCommerce. You need to manually add the WooCommerce database connection to your `config/database.php` file:

1. Open `config/database.php`
2. Add the following connection to the `connections` array:

```php
'connections' => [
    // ... your existing connections ...

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
```

3. Add the following to your `.env` file:

```env
# WooCommerce Database Configuration
WOO_DB_HOST=your_woocommerce_db_host
WOO_DB_PORT=3306
WOO_DB_DATABASE=your_woocommerce_db_name
WOO_DB_USERNAME=your_woocommerce_db_user
WOO_DB_PASSWORD=your_woocommerce_db_password
WOO_DB_PREFIX=wp_
```

Note: The `database.php` configuration is not published by the package because it's a core Laravel configuration file. You need to add the WooCommerce connection manually to ensure proper separation of database connections.

### Security Considerations for Live Sites

When connecting to a live WooCommerce site, follow these security best practices:

1. **Database User Permissions**:
   - Create a dedicated database user for the Laravel application
   - Grant only necessary permissions:
     ```sql
     GRANT SELECT ON your_woocommerce_db.* TO 'laravel_user'@'%';
     ```
   - Never use the WordPress admin database user

2. **Connection Security**:
   - Use SSL/TLS for database connections
   - Add SSL configuration to your `.env`:
     ```env
     WOO_DB_SSL=true
     WOO_DB_SSL_CA=/path/to/ca-certificate.pem
     ```

3. **Network Security**:
   - If possible, use a VPN or private network
   - Configure firewall rules to allow only specific IPs
   - Use SSH tunneling for remote connections:
     ```bash
     ssh -L 3307:localhost:3306 user@woocommerce-server
     ```
   Then update your `.env`:
     ```env
     WOO_DB_HOST=127.0.0.1
     WOO_DB_PORT=3307
     ```

4. **Environment Variables**:
   - Never commit `.env` files to version control
   - Use different credentials for development and production
   - Regularly rotate database passwords

### Performance Optimization

1. **Caching**:
   Enable caching in `config/woo-order-dashboard.php`:
   ```php
   'cache' => [
       'enabled' => true,
       'ttl' => 300, // 5 minutes
   ],
   ```

2. **Query Optimization**:
   - The package uses efficient queries with proper indexing
   - Consider adding these indexes to your WooCommerce database:
     ```sql
     ALTER TABLE wp_posts ADD INDEX type_status_date (post_type, post_status, post_date);
     ALTER TABLE wp_postmeta ADD INDEX post_id_key (post_id, meta_key);
     ALTER TABLE wp_woocommerce_order_items ADD INDEX order_id_type (order_id, order_item_type);
     ```

3. **Pagination**:
   Configure pagination settings in `config/woo-order-dashboard.php`:
   ```php
   'pagination' => [
       'per_page' => 15,
       'page_name' => 'page',
   ],
   ```

### Pagination

The package uses Laravel's built-in pagination system. Here's how to configure and customize it:

1. **Configuration**
   In `config/woo-order-dashboard.php`:
   ```php
   'pagination' => [
       'per_page' => 15,        // Number of orders per page
       'page_name' => 'page',   // Query parameter name for pagination
   ],
   ```

2. **Usage in Views**
   The orders list view includes pagination links:
   ```php
   {{ $orders['data']->links() }}
   ```

3. **Customizing Pagination**
   To customize the pagination appearance:

   a. Publish Laravel's pagination views:
   ```bash
   php artisan vendor:publish --tag=laravel-pagination
   ```

   b. Edit the views in `resources/views/vendor/pagination/`:
   - `default.blade.php` - Default pagination view
   - `bootstrap-4.blade.php` - Bootstrap 4 pagination view
   - `bootstrap-5.blade.php` - Bootstrap 5 pagination view
   - `tailwind.blade.php` - Tailwind CSS pagination view
   - `simple-bootstrap-4.blade.php` - Simple Bootstrap 4 pagination
   - `simple-bootstrap-5.blade.php` - Simple Bootstrap 5 pagination
   - `simple-tailwind.blade.php` - Simple Tailwind CSS pagination

4. **Pagination Features**
   - Maintains query parameters when navigating
   - Shows total number of pages
   - Displays current page
   - Provides first/last page links
   - Includes previous/next page links

5. **Customizing Per Page**
   You can change the number of items per page in the URL:
   ```
   /woo-dashboard/orders?per_page=20
   ```

6. **Pagination Styling**
   The package includes basic pagination styles in `public/vendor/woo-order-dashboard/css/app.css`. You can override these styles in your application's CSS.

7. **Pagination in API Responses**
   When using the package's API endpoints, pagination information is included in the response headers:
   ```
   X-WP-Total: 100
   X-WP-TotalPages: 7
   ```

8. **Troubleshooting Pagination**
   If pagination links are not working:
   - Clear the configuration cache:
     ```bash
     php artisan config:clear
     ```
   - Verify the pagination configuration in `config/woo-order-dashboard.php`
   - Check that the view is using the correct pagination method:
     ```php
     {{ $orders['data']->links() }}
     ```
   - Ensure the WooCommerceService is returning a LengthAwarePaginator instance

## Usage

### Routes

The package registers the following routes:

- `GET /woo-dashboard` - Main dashboard
- `GET /woo-dashboard/orders` - Orders list with filters
- `GET /woo-dashboard/orders/{id}` - Single order view

### Views

The package provides the following views:

- `vendor/woo-order-dashboard/dashboard/index.blade.php` - Main dashboard
- `vendor/woo-order-dashboard/orders/index.blade.php` - Orders list
- `vendor/woo-order-dashboard/orders/show.blade.php` - Single order view

### Customization

1. **Views**:
   - Publish the views to customize them
   - Extend the layout in `resources/views/vendor/woo-order-dashboard/layouts/app.blade.php`

2. **Styling**:
   - The package includes basic styles in `public/vendor/woo-order-dashboard/css/app.css`
   - Override styles in your application's CSS

3. **JavaScript**:
   - Basic functionality in `public/vendor/woo-order-dashboard/js/app.js`
   - Extend or override as needed

## Troubleshooting

### Common Issues

1. **Database Connection Errors**:
   - Verify database credentials
   - Check network connectivity
   - Ensure proper permissions
   - Verify SSL configuration if using

   If you see the error "Unsupported driver [http]", check that:
   - The `driver` is set to `mysql` in your database configuration
   - Your `.env` file has the correct database connection settings
   - You've cleared the configuration cache:
     ```bash
     php artisan config:clear
     php artisan cache:clear
     ```

2. **Performance Issues**:
   - Enable caching
   - Check database indexes
   - Monitor query performance
   - Adjust pagination settings

3. **Missing Data**:
   - Verify WooCommerce version compatibility
   - Check database prefix configuration
   - Ensure proper table structure

### Debugging

Enable debug mode in your `.env`:
```env
APP_DEBUG=true
```

Check Laravel logs in `storage/logs/laravel.log` for detailed error messages.

## Support

For issues and feature requests, please use the [GitHub issue tracker](https://github.com/makiomar/woo-order-dashboard/issues).

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request 