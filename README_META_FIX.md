
# Laravel to WooCommerce Order Integration

This integration is designed to generate WooCommerce-compatible order data using Laravel, storing the order, its meta, line items, and shipping in a structure that mirrors native WooCommerce behavior.

---

## ✅ Purpose

Ensure programmatically created orders via Laravel are:

- Fully compatible with WooCommerce core behavior
- Accurate in tax, totals, and shipping calculations
- Able to trigger WooCommerce-based workflows like:
  - Email notifications
  - Stock reduction
  - Order notes and reports

---

## 📦 Stored Structure

### 1. `post_meta`

Contains essential order metadata. The following keys **must** be present and match WooCommerce expectations:

| Key | Example | Required |
|-----|---------|----------|
| `_order_key` | `wc_xyz123abc` | ✅ |
| `_customer_user` | `28886` | ✅ |
| `_payment_method` | `bacs` | ✅ |
| `_payment_method_title` | `الحوالة البنكية` | ✅ |
| `_order_total` | `207.00` | ✅ |
| `_order_currency` | `SAR` | ✅ |
| `_prices_include_tax` | `no` | ✅ |
| `_cart_discount` | `0` | ✅ |
| `_cart_discount_tax` | `0` | ✅ |
| `_order_shipping` | `21.74` | ✅ |
| `_order_shipping_tax` | `3.26` | ✅ |
| `_order_tax` | `23.74` | ✅ |
| `_total_tax` | `27.00` | ✅ |
| `_shipping_method` | `flat_rate:72` | ✅ |
| `_shipping_method_title` | `سمسا (2-5 أيام عمل)` | ✅ |
| `_shipping_email` | `customer@example.com` | Optional |
| `_billing_*`, `_shipping_*` | Name, address, phone, etc. | ✅ |
| `_billing_address_index` | Single-line version of billing | ✅ |
| `_shipping_address_index` | Single-line version of shipping | ✅ |
| `_customer_ip_address` | `IP Address` | ✅ |
| `_customer_user_agent` | Browser user-agent | ✅ |
| `_created_via` | `admin`, `checkout` | ✅ |
| `_new_order_email_sent` | `false` or `true` | ✅ |
| `_order_stock_reduced` | `no` or `yes` | ✅ |
| `_recorded_sales`, `_recorded_coupon_usage_counts` | `no` | ✅ |
| `_edit_lock` | `timestamp:user_id` | ✅ |
| `_order_version` | WooCommerce version | ✅ |
| `_tax_display_cart` | `incl` or `excl` | Recommended |
| `_tax_display_shop` | `incl` or `excl` | Recommended |
| `_tax_display_totals` | `itemized` or `single` | Recommended |
| `_display_totals_ex_tax` | `no` | Recommended |
| `_cart_hash` | md5 of cart items | Optional |
| `_date_paid` | `Y-m-d H:i:s` | Recommended |
| `_date_completed` | `Y-m-d H:i:s` or empty | Optional |

---

### 2. `line_items`

Each product in the order must include:

```json
{
  "product_name": "مروكي دقة صغير سوبر",
  "quantity": 1,
  "subtotal": "158.26",
  "total": "158.26",
  "subtotal_tax": "23.74",
  "total_tax": "23.74",
  "meta_data": [
    {
      "key": "_reduced_stock",
      "value": "1"
    },
    {
      "key": "pa_الحجم",
      "value": "25 جرام"
    }
  ]
}
```

#### Required keys:
- `product_name`
- `quantity`
- `subtotal`, `total`
- `subtotal_tax`, `total_tax`
- `meta_data`: Must contain `_reduced_stock` and any variation attributes.

---

### 3. `shipping`

Structure should include:

```json
{
  "method_title": "سمسا (2-5 أيام عمل)",
  "total": "21.74",
  "total_tax": "3.26",
  "meta_data": [
    {
      "key": "Items",
      "value": "Product × Qty"
    },
    {
      "key": "wpo_shipping_method_id",
      "value": "flat_rate:72"
    }
  ]
}
```

---

### 4. `totals`

Ensure consistency with the values in `post_meta`:

```json
{
  "subtotal": 158.26,
  "total": "207.00",
  "total_tax": "27",
  "shipping_total": "21.74",
  "shipping_tax": "3.26",
  "discount_total": "0",
  "discount_tax": "0"
}
```

---

## ⚠️ Common Issues to Avoid

- ❌ Negative discounts in `_cart_discount`
- ❌ Double entries for `_cart_discount`
- ❌ Percent-encoded meta keys (decode `pa_الحجم` to Arabic form)
- ❌ Mismatch between calculated tax fields (e.g. `_total_tax` ≠ `_order_tax` + `_shipping_tax`)
- ❌ Missing WooCommerce behavioral meta like `_order_stock_reduced`, `_new_order_email_sent`

---

## ✅ Optional WooCommerce Enhancements

- Include `_wc_cancel_key` for manual cancellations
- Add custom keys like `whatsapp_notifications`, `sms_notifications`, or `odoo_order_number` if used by plugins
- Add `_download_permissions_granted` for downloadable products

---

## 🔄 Suggested Improvements

- Create a helper or factory method in Laravel to auto-generate correct meta keys
- Add unit tests comparing Laravel-generated order with a real Woo order JSON
- Optionally, sync WooCommerce `_order_version` dynamically using `get_option('woocommerce_db_version')`

---

## 📎 Notes

- All meta values must be stored as arrays in WooCommerce (`[value]`)
- Always use localized titles in `_payment_method_title`, `_shipping_method_title`
- Set `_prices_include_tax` to `yes` only if your prices include tax in display
