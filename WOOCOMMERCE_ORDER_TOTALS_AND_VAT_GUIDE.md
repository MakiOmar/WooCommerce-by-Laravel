# WooCommerce Order Totals and VAT Storage Guide

## Overview
This document explains how WooCommerce stores and fetches order totals, shipping costs, and VAT/tax information, and how we've implemented these in our order creation code.

## WooCommerce Order Structure

### 1. Order Meta Data (wp_postmeta table)
WooCommerce stores order totals in the `wp_postmeta` table with these key meta fields:

```sql
_order_total          - Total order amount (tax-inclusive)
_order_tax            - Total tax amount (line items + shipping)
_order_shipping       - Shipping cost (tax-exclusive)
_order_shipping_tax   - Tax on shipping only
_cart_discount        - Discount amount
_cart_discount_tax    - Tax on discount (usually 0)
_prices_include_tax   - Whether prices include tax ('yes'/'no')
```

### 2. Order Items (wp_woocommerce_order_items table)
WooCommerce creates separate line items for different order components:

#### Line Items (products)
```sql
order_item_type = 'line_item'
- _line_total      - Line item total (tax-exclusive)
- _line_tax        - Tax on this line item
- _line_subtotal   - Line item subtotal (tax-exclusive)
- _line_subtotal_tax - Tax on line item subtotal
- _line_tax_data   - Serialized tax data
```

#### Shipping Items
```sql
order_item_type = 'shipping'
- cost             - Shipping cost (tax-exclusive) - CRITICAL KEY
- total_tax        - Tax on shipping
- taxes            - Serialized tax data
- method_title     - Shipping method name
- method_id        - Shipping method ID
```

#### Tax Items
```sql
order_item_type = 'tax'
- rate_code        - Tax rate code (e.g., 'VAT')
- rate_id          - Tax rate ID from woocommerce_tax_rates
- label            - Tax label (e.g., 'VAT')
- compound         - Whether tax is compound (0/1)
- tax_total        - Tax on line items
- shipping_tax_total - Tax on shipping
- rate_percent     - Tax rate percentage
```

### 3. Order Stats (wc_order_stats table)
WooCommerce maintains analytics data:
```sql
total_sales        - Total order amount (tax-inclusive)
tax_total          - Total tax amount
shipping_total     - Shipping amount (tax-inclusive)
net_total          - Net amount before tax
```

## Our Implementation

### 1. Tax Calculation Structure
We calculate taxes to match WooCommerce's exact structure:

```php
// Line items tax calculation
$lineItemsTax = collect($items)->sum(function ($item) {
    return ($item['price'] * $item['qty']) * 0.15; // 15% VAT
});

// Shipping tax calculation
$shippingExclTax = ($data['shipping'] ?? 0) / 1.15; // Remove 15% tax
$shippingTax = ($data['shipping'] ?? 0) - $shippingExclTax; // Extract tax
$shippingInclTax = $shippingExclTax + $shippingTax; // Tax-inclusive shipping

// Total tax
$totalTax = $lineItemsTax + $shippingTax;
```

### 2. Order Meta Data Creation
```php
$metaData = [
    ['_order_total', $total], // Tax-inclusive total
    ['_order_tax', $totalTax], // Total tax
    ['_order_shipping', $shippingExclTax], // Tax-exclusive shipping
    ['_order_shipping_tax', $shippingTax], // Shipping tax
    ['_prices_include_tax', 'yes'], // Critical for tax display
    // ... other meta fields
];
```

### 3. Shipping Line Item Creation
```php
$shippingItemMeta = [
    ['cost', $shippingExclTax], // Tax-exclusive shipping cost - WooCommerce calculates total from cost + total_tax
    ['total_tax', $shippingTax], // Tax on shipping
    ['taxes', $shippingTaxData], // Serialized tax data
    ['method_title', $shippingMethodTitle],
    ['method_id', $shippingMethodId],
    // ... other shipping meta
];
```

### 4. Tax Line Item Creation
```php
$taxItemMeta = [
    ['rate_code', 'VAT'],
    ['rate_id', $taxRateId],
    ['label', 'VAT'],
    ['compound', '0'],
    ['tax_total', $lineItemsTax], // Tax on line items
    ['shipping_tax_total', $shippingTax], // Tax on shipping
    ['rate_percent', '15.00'],
];
```

### 5. Order Stats Creation
```php
DB::connection('woocommerce')->table('wc_order_stats')->insert([
    'order_id' => $order->ID,
    'total_sales' => $total, // Tax-inclusive total
    'tax_total' => $totalTax, // Total tax
    'shipping_total' => $shippingInclTax, // Tax-inclusive shipping
    'net_total' => $subtotal - ($data['discount'] ?? 0), // Net before tax
    // ... other stats
]);
```

## Key Fixes Implemented

### 1. Shipping Meta Key Fix
**Problem**: Shipping total showed empty because WooCommerce doesn't use a `total` meta key for shipping items. Instead, it calculates the total from `cost` + `total_tax`.

**Solution**: Only store the correct meta keys that WooCommerce expects:
```php
['cost', $shippingExclTax], // Shipping cost (tax-exclusive)
['total_tax', $shippingTax], // Tax on shipping
```

### 2. Order Stats Shipping Total Fix
**Problem**: Order stats table was using raw shipping data instead of calculated tax-inclusive amount.

**Solution**: Use the calculated tax-inclusive shipping amount:
```php
'shipping_total' => $shippingInclTax, // Instead of $data['shipping']
```

### 3. Tax Data Serialization
**Problem**: WooCommerce expects specific serialized format for tax data.

**Solution**: Use correct serialization format:
```php
$taxData = serialize([
    'total' => [$taxRateId => $lineTax],
    'subtotal' => [$taxRateId => $lineTax]
]);
```

## View Display Logic

The order items view calculates totals as follows:

```php
// Get shipping from line items first, fallback to meta for legacy orders
$shippingFromLineItems = $order->items->where('order_item_type', 'shipping')->sum(function($item) {
    $cost = $item->meta->where('meta_key', 'cost')->first()->meta_value ?? 0;
    $tax = $item->meta->where('meta_key', 'total_tax')->first()->meta_value ?? 0;
    return $cost + $tax;
});

// Calculate total VAT from line items and shipping
$lineItemsVAT = $order->items->where('order_item_type', 'line_item')->sum(function($item) {
    return $item->meta->where('meta_key', '_line_tax')->first()->meta_value ?? 0;
});
$shippingVAT = $order->items->where('order_item_type', 'shipping')->sum(function($item) {
    return $item->meta->where('meta_key', 'total_tax')->first()->meta_value ?? 0;
});
$totalVAT = $lineItemsVAT + $shippingVAT;
```

## Testing the Implementation

To verify that shipping totals and VAT are working correctly:

1. **Create a new order** with shipping cost
2. **Check the order details** in WooCommerce admin
3. **Verify shipping line item** shows correct cost and tax
4. **Check order totals** match expected calculations
5. **Review order stats** in WooCommerce analytics

## Common Issues and Solutions

### Issue: Shipping shows 0.00
**Cause**: Missing or incorrect meta key for shipping cost
**Solution**: Ensure both `'cost'` and `'total'` meta keys are set

### Issue: VAT not displaying
**Cause**: Missing tax line items or incorrect tax data serialization
**Solution**: Create proper tax line items with correct meta data

### Issue: Totals don't match
**Cause**: Inconsistent tax calculations or wrong meta values
**Solution**: Double-check all calculations and ensure tax-inclusive vs tax-exclusive values are used correctly

## Conclusion

By following WooCommerce's exact data structure and meta key requirements, we ensure that:
- Shipping totals display correctly
- VAT calculations are accurate
- Order totals match expected values
- WooCommerce admin displays all information properly
- Analytics and reporting work correctly 