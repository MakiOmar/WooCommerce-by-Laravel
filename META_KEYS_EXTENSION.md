# Meta Keys Extension Guide

This guide explains how to extend the meta keys configuration for filtering WooCommerce orders in the dashboard.

## Overview

The WooCommerce Order Dashboard allows you to filter orders by meta keys (custom fields). The system comes with a comprehensive set of predefined meta keys organized into categories, and you can easily extend this list with your own custom meta keys.

## Default Meta Key Categories

The system includes the following categories:

- **Billing Information**: Customer billing details
- **Shipping Information**: Customer shipping details  
- **Payment Information**: Payment method and transaction details
- **Customer Information**: Customer user data
- **Order Information**: Order-specific data
- **Custom Fields**: Third-party plugin fields

## Adding Custom Meta Keys

### Method 1: Configuration File

You can add custom meta keys directly to the configuration file:

```php
// In config/woo-order-dashboard.php
'meta_keys' => [
    // ... existing keys ...
    
    // Your custom meta keys
    '_my_custom_field' => 'My Custom Field Label',
    '_another_field' => 'Another Field Label',
],

'meta_key_categories' => [
    // ... existing categories ...
    
    'my_custom_category' => [
        'label' => 'My Custom Category',
        'keys' => [
            '_my_custom_field',
            '_another_field',
        ]
    ],
]
```

### Method 2: Programmatically

You can add meta keys programmatically using the `MetaHelper` class:

```php
use Makiomar\WooOrderDashboard\Helpers\MetaHelper;

// Add a single meta key
MetaHelper::addMetaKey('_my_custom_field', 'My Custom Field Label', 'custom');

// Add a new category
MetaHelper::addMetaKeyCategory('my_plugin', 'My Plugin Fields', [
    '_my_plugin_field_1',
    '_my_plugin_field_2'
]);

// Add meta keys to an existing category
$customKeys = ['_field_1', '_field_2', '_field_3'];
foreach ($customKeys as $key) {
    MetaHelper::addMetaKey($key, ucwords(str_replace('_', ' ', $key)), 'custom');
}
```

### Method 3: Service Provider

You can register custom meta keys in a service provider:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Makiomar\WooOrderDashboard\Helpers\MetaHelper;

class WooCommerceMetaKeysServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Add custom meta keys for your plugin
        MetaHelper::addMetaKeyCategory('my_plugin', 'My Plugin Fields', []);
        
        MetaHelper::addMetaKey('_my_plugin_order_id', 'My Plugin Order ID', 'my_plugin');
        MetaHelper::addMetaKey('_my_plugin_status', 'My Plugin Status', 'my_plugin');
        MetaHelper::addMetaKey('_my_plugin_priority', 'My Plugin Priority', 'my_plugin');
    }
}
```

Then register the service provider in `config/app.php`:

```php
'providers' => [
    // ... other providers ...
    App\Providers\WooCommerceMetaKeysServiceProvider::class,
],
```

## Discovering Meta Keys

The system can automatically discover meta keys from your database:

```php
use Makiomar\WooOrderDashboard\Helpers\MetaHelper;

// Get all unique meta keys from the database
$discoveredKeys = MetaHelper::discoverMetaKeys();

// Get meta keys with their usage count
$keysWithCount = MetaHelper::getMetaKeysWithCount();

// Add discovered keys to configuration
foreach ($discoveredKeys as $key) {
    if (!array_key_exists($key, MetaHelper::getAvailableMetaKeys())) {
        MetaHelper::addMetaKey($key, ucwords(str_replace('_', ' ', $key)), 'discovered');
    }
}
```

## Common WooCommerce Meta Keys

Here are some commonly used WooCommerce meta keys you might want to add:

### Payment Gateways
```php
'_stripe_intent_id' => 'Stripe Intent ID',
'_paypal_transaction_id' => 'PayPal Transaction ID',
'_square_payment_id' => 'Square Payment ID',
```

### Shipping
```php
'_shipping_tracking_number' => 'Tracking Number',
'_shipping_carrier' => 'Shipping Carrier',
'_shipping_service' => 'Shipping Service',
```

### Tax
```php
'_tax_total' => 'Tax Total',
'_shipping_tax_total' => 'Shipping Tax Total',
'_tax_status' => 'Tax Status',
```

### Subscriptions
```php
'_subscription_id' => 'Subscription ID',
'_subscription_status' => 'Subscription Status',
'_subscription_period' => 'Subscription Period',
```

### Bookings
```php
'_booking_id' => 'Booking ID',
'_booking_start' => 'Booking Start',
'_booking_end' => 'Booking End',
'_booking_persons' => 'Booking Persons',
```

## Best Practices

1. **Use Descriptive Labels**: Make sure your meta key labels are clear and user-friendly.

2. **Organize by Category**: Group related meta keys into logical categories.

3. **Cache Management**: The system automatically clears cache when you add/remove meta keys.

4. **Validation**: Always validate meta key names to ensure they follow WordPress conventions.

5. **Documentation**: Document your custom meta keys for future reference.

## Example: Complete Integration

Here's a complete example of integrating custom meta keys for a fictional plugin:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Makiomar\WooOrderDashboard\Helpers\MetaHelper;

class MyPluginServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Add category for our plugin
        MetaHelper::addMetaKeyCategory('my_plugin', 'My Plugin Fields', []);
        
        // Add our custom meta keys
        $metaKeys = [
            '_my_plugin_order_type' => 'Order Type',
            '_my_plugin_priority' => 'Priority Level',
            '_my_plugin_assigned_to' => 'Assigned To',
            '_my_plugin_due_date' => 'Due Date',
            '_my_plugin_notes' => 'Internal Notes',
            '_my_plugin_external_id' => 'External System ID',
        ];
        
        foreach ($metaKeys as $key => $label) {
            MetaHelper::addMetaKey($key, $label, 'my_plugin');
        }
    }
}
```

This will add a new "My Plugin Fields" category to the meta key dropdown with all your custom fields organized and ready for filtering. 