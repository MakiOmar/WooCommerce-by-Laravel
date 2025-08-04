# WooCommerce Order Creation Essentials - Permanent Reference

## ⚠️ CRITICAL REMINDER
**This document contains the essential data requirements for creating proper WooCommerce orders. These requirements may change with WooCommerce updates, so always verify against the latest WooCommerce version.**

## Core Order Structure Requirements

### 1. Order Post (wp_posts table)
```sql
post_type = 'shop_order'
post_status = 'wc-processing' (or appropriate status)
post_title = 'Order #12345'
post_content = ''
post_excerpt = ''
```

### 2. Order Meta Data (wp_postmeta table) - CRITICAL
```sql
_order_total          - Total order amount (tax-inclusive) - REQUIRED
_order_tax            - Total tax amount (line items + shipping) - REQUIRED
_order_shipping       - Shipping cost (tax-exclusive) - REQUIRED
_order_shipping_tax   - Tax on shipping only - REQUIRED
_cart_discount        - Discount amount
_cart_discount_tax    - Tax on discount (usually 0)
_prices_include_tax   - 'yes' or 'no' - CRITICAL FOR TAX DISPLAY
_order_currency       - Currency code (e.g., 'SAR')
_order_version        - WooCommerce version
```

## Order Items Structure - CRITICAL META KEYS

### 3. Line Items (order_item_type = 'line_item')
```sql
-- Order Item
order_item_name = 'Product Name'
order_item_type = 'line_item'
order_id = [order_id]

-- Order Item Meta - CRITICAL KEYS
_product_id           - Product ID
_variation_id         - Variation ID (0 for simple products)
_qty                  - Quantity
_tax_class            - Tax class (usually empty)
_line_subtotal        - Line subtotal (tax-exclusive)
_line_subtotal_tax    - Line subtotal tax
_line_total           - Line total (tax-exclusive)
_line_tax             - Line tax amount
_line_tax_data        - Serialized tax data - CRITICAL FOR TAX DISPLAY
_reduced_stock        - '1' if stock reduced
```

### 4. Shipping Items (order_item_type = 'shipping') - CRITICAL
```sql
-- Order Item
order_item_name = 'Shipping Method Name'
order_item_type = 'shipping'
order_id = [order_id]

-- Order Item Meta - CRITICAL KEYS
cost                  - Shipping cost (tax-exclusive) - CRITICAL KEY
total_tax             - Tax on shipping
taxes                 - Serialized tax data - CRITICAL FOR TAX DISPLAY
method_title          - Shipping method title
method_id             - Shipping method ID
instance_id           - Shipping zone instance ID
```

### 5. Tax Items (order_item_type = 'tax') - CRITICAL
```sql
-- Order Item
order_item_name = 'VAT' (or tax label)
order_item_type = 'tax'
order_id = [order_id]

-- Order Item Meta - CRITICAL KEYS
rate_code             - Tax rate code (e.g., 'VAT')
rate_id               - Tax rate ID from woocommerce_tax_rates
label                 - Tax label (e.g., 'VAT')
compound              - '0' for simple tax, '1' for compound
tax_amount            - Tax on line items - CRITICAL KEY
shipping_tax_amount   - Tax on shipping - CRITICAL KEY
rate_percent          - Tax rate percentage
```

## Tax Data Serialization - CRITICAL FORMAT

### 6. Line Item Tax Data
```php
$taxData = serialize([
    'total' => [$taxRateId => $lineTax],
    'subtotal' => [$taxRateId => $lineTax]
]);
```

### 7. Shipping Tax Data
```php
$shippingTaxData = serialize([
    'total' => [$taxRateId => $shippingTax]
]);
```

## Order Stats (wc_order_stats table) - REQUIRED
```sql
order_id              - Order ID
parent_id             - 0 for main orders
date_created          - Order creation timestamp
date_created_gmt      - Order creation timestamp (GMT)
date_paid             - Payment timestamp
num_items_sold        - Total quantity of items
total_sales           - Total order amount (tax-inclusive)
tax_total             - Total tax amount
shipping_total        - Shipping amount (tax-inclusive)
net_total             - Net amount before tax
returning_customer    - 0 or 1
status                - Order status
customer_id           - Customer user ID
date_completed        - NULL or completion timestamp
```

## Tax Lookup (wc_order_tax_lookup table) - REQUIRED
```sql
order_id              - Order ID
tax_rate_id           - Tax rate ID
date_created          - Creation timestamp
shipping_tax          - Tax on shipping
order_tax             - Tax on line items
total_tax             - Total tax amount
```

## Product Lookup (wc_order_product_lookup table) - REQUIRED
```sql
order_id              - Order ID
order_item_id         - Line item ID
product_id            - Product ID
variation_id          - Variation ID
customer_id           - Customer user ID
date_created          - Creation timestamp
product_qty           - Quantity
product_net_revenue   - Net revenue (tax-exclusive)
product_gross_revenue - Gross revenue (tax-inclusive)
coupon_amount         - Coupon discount
tax_amount            - Tax amount
shipping_amount       - Shipping amount per item
shipping_tax_amount   - Shipping tax per item
```

## Critical Implementation Notes

### Tax Calculation Structure
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

### Shipping Total Calculation
**CRITICAL**: WooCommerce calculates shipping total from `cost` + `total_tax`, NOT from a `total` meta key.

### Tax Display Requirements
**CRITICAL**: WooCommerce's `$order->get_tax_totals()` method requires:
- Tax line items with correct meta keys (`tax_amount`, `shipping_tax_amount`)
- Proper tax data serialization
- Correct tax rate IDs

## Common Issues and Solutions

### Issue: Shipping total shows empty
**Cause**: Using `total` meta key instead of `cost` + `total_tax`
**Solution**: Only store `cost` and `total_tax` meta keys

### Issue: VAT totals row not showing
**Cause**: Using `tax_total` instead of `tax_amount` meta keys
**Solution**: Use `tax_amount` and `shipping_tax_amount` meta keys

### Issue: Tax calculations incorrect
**Cause**: Wrong tax data serialization format
**Solution**: Use exact serialization format shown above

### Issue: Order stats missing
**Cause**: Missing wc_order_stats table entries
**Solution**: Always create order stats entries with correct values

## Testing Checklist

When creating orders, verify:
1. ✅ Order post exists with correct post_type and status
2. ✅ All required order meta data is present
3. ✅ Line items have correct meta keys and tax data
4. ✅ Shipping items have `cost` and `total_tax` (NOT `total`)
5. ✅ Tax items have `tax_amount` and `shipping_tax_amount` (NOT `tax_total`)
6. ✅ Tax data is properly serialized
7. ✅ Order stats table has correct entries
8. ✅ Tax lookup table has correct entries
9. ✅ Product lookup table has correct entries
10. ✅ VAT totals display in WooCommerce admin
11. ✅ Shipping totals display correctly
12. ✅ Order totals match expected calculations

## WooCommerce Version Compatibility

**IMPORTANT**: These requirements are based on WooCommerce 9.3.3. When WooCommerce is updated:
1. Check the changelog for any database structure changes
2. Test order creation with the new version
3. Update this document if meta keys or data structures change
4. Verify that all critical functionality still works

## Backup Strategy

Before making changes to order creation code:
1. Test on a staging environment first
2. Create backup orders to compare against
3. Use WooCommerce's debug functions to verify data structure
4. Document any changes in this reference document

---

**Last Updated**: Based on WooCommerce 9.3.3
**Next Review**: After WooCommerce updates
**Critical**: Always test order creation after WooCommerce updates 