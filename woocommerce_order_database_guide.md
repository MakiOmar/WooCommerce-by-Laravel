# WooCommerce Order Database Structure - Complete Guide

## Overview
This guide covers all the required database tables, fields, and meta keys needed to manually insert WooCommerce orders using the traditional post-type system (not HPOS). Each section explains what data is required and why it's important.

## Table of Contents
1. [Core Order Data (`wp_posts`)](#core-order-data)
2. [Order Meta Data (`wp_postmeta`)](#order-meta-data)
3. [Order Items (`wp_woocommerce_order_items`)](#order-items)
4. [Order Item Meta (`wp_woocommerce_order_itemmeta`)](#order-item-meta)
5. [Payment Tokens (if applicable)](#payment-tokens)
6. [Download Permissions (for digital products)](#download-permissions)
7. [Comments/Notes (`wp_comments`)](#order-notes)
8. [Example Implementation](#example-implementation)

---

## Core Order Data (`wp_posts`)

The order itself is stored as a custom post type in the `wp_posts` table.

### Required Fields:

| Field | Value | Purpose |
|-------|--------|---------|
| `post_author` | Customer user ID (or 0 for guest) | Links order to customer account |
| `post_date` | Order creation date | When order was placed |
| `post_date_gmt` | Order creation date (GMT) | GMT timestamp for consistency |
| `post_content` | '' (empty string) | Not used for orders |
| `post_title` | Order number/ID | Human readable order identifier |
| `post_excerpt` | Customer order notes | Notes from customer at checkout |
| `post_status` | Order status | `wc-pending`, `wc-processing`, `wc-on-hold`, etc. |
| `comment_status` | 'open' | Allows order notes |
| `ping_status` | 'closed' | Not used for orders |
| `post_password` | '' | Not used for orders |
| `post_name` | Unique slug | URL-friendly version of order number |
| `to_ping` | '' | Not used |
| `pinged` | '' | Not used |
| `post_modified` | Last modified date | When order was last updated |
| `post_modified_gmt` | Last modified date (GMT) | GMT timestamp |
| `post_content_filtered` | '' | Not used |
| `post_parent` | 0 | Orders don't have parents |
| `guid` | Full URL to order | WordPress GUID |
| `menu_order` | 0 | Not used for orders |
| `post_type` | 'shop_order' | Identifies this as a WooCommerce order |
| `post_mime_type` | '' | Not used |
| `comment_count` | Number of order notes | Count of order notes/comments |

---

## Order Meta Data (`wp_postmeta`)

All order-specific data is stored in post meta. Here are ALL the meta keys you need:

### Billing Information
```
_billing_first_name     - Customer's billing first name
_billing_last_name      - Customer's billing last name
_billing_company        - Customer's billing company
_billing_address_1      - Billing address line 1
_billing_address_2      - Billing address line 2
_billing_city           - Billing city
_billing_state          - Billing state/province
_billing_postcode       - Billing postal code
_billing_country        - Billing country code (e.g., 'US')
_billing_email          - Billing email address
_billing_phone          - Billing phone number
```

### Shipping Information
```
_shipping_first_name    - Customer's shipping first name
_shipping_last_name     - Customer's shipping last name
_shipping_company       - Customer's shipping company
_shipping_address_1     - Shipping address line 1
_shipping_address_2     - Shipping address line 2
_shipping_city          - Shipping city
_shipping_state         - Shipping state/province
_shipping_postcode      - Shipping postal code
_shipping_country       - Shipping country code
```

### Order Totals & Financial Data
```
_order_key              - Unique order key for security
_order_currency         - Currency code (e.g., 'USD')
_prices_include_tax     - 'yes' or 'no' - whether prices include tax
_order_total            - Final order total
_cart_discount          - Cart discount amount
_cart_discount_tax      - Tax on cart discount
_order_shipping         - Shipping cost
_order_shipping_tax     - Tax on shipping
_order_tax              - Total tax amount
_order_version          - WooCommerce version when order created
```

### Payment Information
```
_payment_method         - Payment gateway ID (e.g., 'stripe', 'paypal')
_payment_method_title   - Human readable payment method name
_transaction_id         - Payment transaction ID from gateway
_paid_date              - Timestamp when payment was completed
```

### Customer Information
```
_customer_user          - WordPress user ID (0 for guests)
_customer_ip_address    - Customer's IP address
_customer_user_agent    - Customer's browser user agent
```

### Order Management
```
_created_via            - How order was created ('checkout', 'admin', etc.)
_date_completed         - Completion timestamp
_date_paid              - Payment timestamp
_cart_hash              - Hash of cart contents for validation
_order_stock_reduced    - 'yes' when stock has been reduced
```

### Tax Information
```
_order_tax_data         - Serialized array of tax data
```

### Downloadable Products (if applicable)
```
_download_permissions_granted - 'yes' when download permissions set
```

---

## Order Items (`wp_woocommerce_order_items`)

Each product/fee/shipping/tax in an order gets a row in this table.

### Required Fields:

| Field | Purpose | Values |
|-------|---------|---------|
| `order_item_id` | Auto-increment ID | Primary key |
| `order_item_name` | Display name | Product name, shipping method name, etc. |
| `order_item_type` | Type of item | `line_item`, `shipping`, `tax`, `fee`, `coupon` |
| `order_id` | Links to order | Post ID from wp_posts |

### Item Types:
- `line_item` - Products/variations
- `shipping` - Shipping methods
- `tax` - Tax lines
- `fee` - Additional fees
- `coupon` - Applied coupons

---

## Order Item Meta (`wp_woocommerce_order_itemmeta`)

Each order item has associated metadata.

### For Line Items (Products):
```
_product_id             - Product post ID
_variation_id           - Variation ID (0 for simple products)
_qty                    - Quantity purchased
_line_subtotal          - Subtotal before discounts
_line_subtotal_tax      - Tax on subtotal
_line_total             - Total after discounts
_line_tax               - Tax on line total
_line_tax_data          - Serialized tax data
_reduced_stock          - Amount of stock reduced
```

### Product Variation Meta (for variable products):
```
pa_color                - Attribute: Color (example)
pa_size                 - Attribute: Size (example)
[Any custom attributes with 'pa_' prefix]
```

### For Shipping Items:
```
method_id               - Shipping method ID
instance_id             - Shipping zone instance ID
cost                    - Shipping cost
total_tax               - Tax on shipping
taxes                   - Serialized tax data
```

### For Tax Items:
```
rate_id                 - Tax rate ID
label                   - Tax label
compound                - Whether tax is compound
tax_amount              - Tax amount
shipping_tax_amount     - Shipping tax amount
rate_code               - Tax rate code
rate_percent            - Tax percentage
```

### For Fee Items:
```
_fee_amount             - Fee amount
_line_total             - Fee total
_line_tax               - Tax on fee
_tax_class              - Tax class for fee
_tax_status             - Taxable status
```

### For Coupon Items:
```
discount_amount         - Discount amount
discount_amount_tax     - Tax on discount
```

---

## Payment Tokens (Optional)

If using payment tokens for saved payment methods:

### `wp_woocommerce_payment_tokens`
```
token_id                - Auto-increment ID
gateway_id              - Payment gateway ID
token                   - Encrypted token
user_id                 - User ID
type                    - Token type (CC, eCheck, etc.)
is_default              - Whether this is default payment method
```

### `wp_woocommerce_payment_tokenmeta`
```
meta_id                 - Auto-increment ID
payment_token_id        - Links to token
meta_key                - Meta key (card_type, last4, expiry_month, etc.)
meta_value              - Meta value
```

---

## Download Permissions

For digital/downloadable products:

### `wp_woocommerce_downloadable_product_permissions`
```
permission_id           - Auto-increment ID
download_id             - Download ID from product
product_id              - Product post ID
order_id                - Order post ID
order_key               - Order key for security
user_email              - Customer email
user_id                 - Customer user ID (0 for guests)
downloads_remaining     - Remaining download count
access_granted          - Grant timestamp
access_expires          - Expiry timestamp (NULL for no expiry)
download_count          - Current download count
```

---

## Order Notes (`wp_comments`)

Order notes are stored as comments linked to the order post.

### Required Fields:
```
comment_ID              - Auto-increment ID
comment_post_ID         - Order post ID
comment_author          - Author name ('WooCommerce' for system notes)
comment_author_email    - Author email (empty for system)
comment_author_url      - Author URL (empty)
comment_author_IP       - IP address
comment_date            - Note timestamp
comment_date_gmt        - Note timestamp (GMT)
comment_content         - Note content
comment_karma           - Not used (0)
comment_approved        - '1' for approved
comment_agent           - User agent
comment_type            - 'order_note'
comment_parent          - Parent comment ID (0)
user_id                 - User ID who created note
```

### Order Note Meta (`wp_commentmeta`):
```
is_customer_note        - '1' if visible to customer, '0' if private
```

---

## Example Implementation

Here's a basic Laravel implementation structure:

### 1. Create Order Post
```php
$orderId = DB::table('wp_posts')->insertGetId([
    'post_author' => $customerId,
    'post_date' => now(),
    'post_date_gmt' => now()->utc(),
    'post_content' => '',
    'post_title' => 'Order #' . $orderNumber,
    'post_excerpt' => $customerNotes,
    'post_status' => 'wc-pending',
    'comment_status' => 'open',
    'ping_status' => 'closed',
    'post_password' => '',
    'post_name' => 'order-' . $orderNumber,
    'post_type' => 'shop_order',
    'comment_count' => 0
]);
```

### 2. Add Order Meta
```php
$orderMeta = [
    '_order_key' => wp_generate_password(13, false),
    '_order_currency' => 'USD',
    '_order_total' => $total,
    '_billing_first_name' => $billingData['first_name'],
    // ... all other meta fields
];

foreach ($orderMeta as $key => $value) {
    DB::table('wp_postmeta')->insert([
        'post_id' => $orderId,
        'meta_key' => $key,
        'meta_value' => $value
    ]);
}
```

### 3. Add Order Items
```php
foreach ($products as $product) {
    $itemId = DB::table('wp_woocommerce_order_items')->insertGetId([
        'order_item_name' => $product['name'],
        'order_item_type' => 'line_item',
        'order_id' => $orderId
    ]);

    // Add item meta
    $itemMeta = [
        '_product_id' => $product['id'],
        '_qty' => $product['quantity'],
        '_line_total' => $product['total'],
        // ... other item meta
    ];

    foreach ($itemMeta as $key => $value) {
        DB::table('wp_woocommerce_order_itemmeta')->insert([
            'order_item_id' => $itemId,
            'meta_key' => $key,
            'meta_value' => $value
        ]);
    }
}
```

---

## Important Considerations

### 1. Order Status Values
Always prefix order statuses with 'wc-':
- `wc-pending`
- `wc-processing` 
- `wc-on-hold`
- `wc-completed`
- `wc-cancelled`
- `wc-refunded`
- `wc-failed`

### 2. Currency and Decimals  
Ensure monetary values are stored with proper decimal precision (usually 2 decimal places).

### 3. Serialized Data
Some meta values are serialized PHP arrays (like tax data). Handle these carefully.

### 4. Stock Management
If `_order_stock_reduced` is not set to 'yes', WooCommerce may try to reduce stock again.

### 5. Order Key Security
The `_order_key` should be a unique, unpredictable string for security.

### 6. Timestamps
Use proper MySQL datetime format: 'Y-m-d H:i:s'

### 7. Hooks and Actions
Manual insertion bypasses WooCommerce hooks. You may need to manually trigger stock reduction, emails, etc.

---

## Stock Management

WooCommerce handles stock reduction/increment based on order status changes. When manually creating orders, you need to handle this yourself.

### Stock Reduction Logic

Stock should be reduced when an order moves to a status that "holds" inventory:

**Stock Reducing Statuses:**
- `wc-on-hold`
- `wc-processing` 
- `wc-completed`

**Stock Restoring Statuses:**
- `wc-cancelled`
- `wc-refunded`
- `wc-failed`

### Implementation

#### 1. Reduce Stock Function
```php
public function reduceOrderStock($orderId)
{
    // Check if stock already reduced
    $stockReduced = DB::table('wp_postmeta')
        ->where('post_id', $orderId)
        ->where('meta_key', '_order_stock_reduced')
        ->value('meta_value');
    
    if ($stockReduced === 'yes') {
        return; // Already reduced
    }
    
    // Get all line items
    $lineItems = DB::table('wp_woocommerce_order_items')
        ->where('order_id', $orderId)
        ->where('order_item_type', 'line_item')
        ->get();
    
    foreach ($lineItems as $item) {
        $productId = DB::table('wp_woocommerce_order_itemmeta')
            ->where('order_item_id', $item->order_item_id)
            ->where('meta_key', '_product_id')
            ->value('meta_value');
            
        $variationId = DB::table('wp_woocommerce_order_itemmeta')
            ->where('order_item_id', $item->order_item_id)
            ->where('meta_key', '_variation_id')
            ->value('meta_value');
            
        $quantity = DB::table('wp_woocommerce_order_itemmeta')
            ->where('order_item_id', $item->order_item_id)
            ->where('meta_key', '_qty')
            ->value('meta_value');
        
        // Use variation ID if it exists, otherwise use product ID
        $stockProductId = $variationId > 0 ? $variationId : $productId;
        
        // Check if product manages stock
        $manageStock = DB::table('wp_postmeta')
            ->where('post_id', $stockProductId)
            ->where('meta_key', '_manage_stock')
            ->value('meta_value');
            
        if ($manageStock === 'yes') {
            // Get current stock
            $currentStock = DB::table('wp_postmeta')
                ->where('post_id', $stockProductId)
                ->where('meta_key', '_stock')
                ->value('meta_value');
            
            $newStock = max(0, $currentStock - $quantity);
            
            // Update stock
            DB::table('wp_postmeta')
                ->where('post_id', $stockProductId)
                ->where('meta_key', '_stock')
                ->update(['meta_value' => $newStock]);
            
            // Update stock status if needed
            if ($newStock <= 0) {
                DB::table('wp_postmeta')
                    ->where('post_id', $stockProductId)
                    ->where('meta_key', '_stock_status')
                    ->update(['meta_value' => 'outofstock']);
            }
            
            // Record the reduction in order item meta
            DB::table('wp_woocommerce_order_itemmeta')->insert([
                'order_item_id' => $item->order_item_id,
                'meta_key' => '_reduced_stock',
                'meta_value' => $quantity
            ]);
        }
    }
    
    // Mark stock as reduced
    DB::table('wp_postmeta')->insert([
        'post_id' => $orderId,
        'meta_key' => '_order_stock_reduced',
        'meta_value' => 'yes'
    ]);
}
```

#### 2. Restore Stock Function
```php
public function restoreOrderStock($orderId)
{
    // Check if stock was reduced
    $stockReduced = DB::table('wp_postmeta')
        ->where('post_id', $orderId)
        ->where('meta_key', '_order_stock_reduced')
        ->value('meta_value');
    
    if ($stockReduced !== 'yes') {
        return; // Stock wasn't reduced
    }
    
    // Get all line items with reduced stock
    $lineItems = DB::table('wp_woocommerce_order_items')
        ->where('order_id', $orderId)
        ->where('order_item_type', 'line_item')
        ->get();
    
    foreach ($lineItems as $item) {
        $reducedStock = DB::table('wp_woocommerce_order_itemmeta')
            ->where('order_item_id', $item->order_item_id)
            ->where('meta_key', '_reduced_stock')
            ->value('meta_value');
            
        if (!$reducedStock) continue;
        
        $productId = DB::table('wp_woocommerce_order_itemmeta')
            ->where('order_item_id', $item->order_item_id)
            ->where('meta_key', '_product_id')
            ->value('meta_value');
            
        $variationId = DB::table('wp_woocommerce_order_itemmeta')
            ->where('order_item_id', $item->order_item_id)
            ->where('meta_key', '_variation_id')
            ->value('meta_value');
        
        $stockProductId = $variationId > 0 ? $variationId : $productId;
        
        // Restore stock
        $currentStock = DB::table('wp_postmeta')
            ->where('post_id', $stockProductId)
            ->where('meta_key', '_stock')
            ->value('meta_value');
        
        $newStock = $currentStock + $reducedStock;
        
        DB::table('wp_postmeta')
            ->where('post_id', $stockProductId)
            ->where('meta_key', '_stock')
            ->update(['meta_value' => $newStock]);
        
        // Update stock status
        if ($newStock > 0) {
            DB::table('wp_postmeta')
                ->where('post_id', $stockProductId)
                ->where('meta_key', '_stock_status')
                ->update(['meta_value' => 'instock']);
        }
        
        // Remove reduced stock meta
        DB::table('wp_woocommerce_order_itemmeta')
            ->where('order_item_id', $item->order_item_id)
            ->where('meta_key', '_reduced_stock')
            ->delete();
    }
    
    // Remove stock reduced flag
    DB::table('wp_postmeta')
        ->where('post_id', $orderId)
        ->where('meta_key', '_order_stock_reduced')
        ->delete();
}
```

---

## Order Notes & Status Change Events

Order notes provide audit trail and customer communication. Different events trigger different types of notes.

### Order Note Types

1. **System Notes** - Internal tracking (not visible to customer)
2. **Customer Notes** - Visible to customer in their account
3. **Private Notes** - Admin-only notes

### Status Change Notes

#### Implementation for Status Changes
```php
public function updateOrderStatus($orderId, $newStatus, $note = '', $customerNote = false)
{
    $oldStatus = DB::table('wp_posts')
        ->where('ID', $orderId)
        ->value('post_status');
    
    // Update order status
    DB::table('wp_posts')
        ->where('ID', $orderId)
        ->update([
            'post_status' => $newStatus,
            'post_modified' => now(),
            'post_modified_gmt' => now()->utc()
        ]);
    
    // Handle stock changes
    $this->handleStockOnStatusChange($orderId, $oldStatus, $newStatus);
    
    // Add status change note
    $this->addOrderNote($orderId, $this->getStatusChangeMessage($oldStatus, $newStatus), false);
    
    // Add custom note if provided
    if (!empty($note)) {
        $this->addOrderNote($orderId, $note, $customerNote);
    }
    
    // Handle specific status events
    $this->handleStatusSpecificEvents($orderId, $newStatus);
}

private function handleStockOnStatusChange($orderId, $oldStatus, $newStatus)
{
    $stockReducingStatuses = ['wc-on-hold', 'wc-processing', 'wc-completed'];
    $stockRestoringStatuses = ['wc-cancelled', 'wc-refunded', 'wc-failed'];
    
    $oldReducesStock = in_array($oldStatus, $stockReducingStatuses);
    $newReducesStock = in_array($newStatus, $stockReducingStatuses);
    $newRestoresStock = in_array($newStatus, $stockRestoringStatuses);
    
    if (!$oldReducesStock && $newReducesStock) {
        // Moving to stock-reducing status
        $this->reduceOrderStock($orderId);
        $this->addOrderNote($orderId, 'Stock levels reduced.', false);
    } elseif ($oldReducesStock && $newRestoresStock) {
        // Moving to stock-restoring status
        $this->restoreOrderStock($orderId);
        $this->addOrderNote($orderId, 'Stock levels restored.', false);
    }
}

private function getStatusChangeMessage($oldStatus, $newStatus)
{
    $statusLabels = [
        'wc-pending' => 'Pending payment',
        'wc-processing' => 'Processing',
        'wc-on-hold' => 'On hold',
        'wc-completed' => 'Completed',
        'wc-cancelled' => 'Cancelled',
        'wc-refunded' => 'Refunded',
        'wc-failed' => 'Failed'
    ];
    
    $oldLabel = $statusLabels[$oldStatus] ?? $oldStatus;
    $newLabel = $statusLabels[$newStatus] ?? $newStatus;
    
    return "Order status changed from {$oldLabel} to {$newLabel}.";
}
```

### Add Order Note Function
```php
public function addOrderNote($orderId, $note, $isCustomerNote = false, $addedBy = 'system')
{
    $commentId = DB::table('wp_comments')->insertGetId([
        'comment_post_ID' => $orderId,
        'comment_author' => $addedBy === 'system' ? 'WooCommerce' : $addedBy,
        'comment_author_email' => '',
        'comment_author_url' => '',
        'comment_author_IP' => request()->ip() ?? '',
        'comment_date' => now(),
        'comment_date_gmt' => now()->utc(),
        'comment_content' => $note,
        'comment_karma' => 0,
        'comment_approved' => '1',
        'comment_agent' => request()->userAgent() ?? '',
        'comment_type' => 'order_note',
        'comment_parent' => 0,
        'user_id' => auth()->id() ?? 0
    ]);
    
    // Add customer note meta
    DB::table('wp_commentmeta')->insert([
        'comment_id' => $commentId,
        'meta_key' => 'is_customer_note',
        'meta_value' => $isCustomerNote ? '1' : '0'
    ]);
    
    // Update comment count
    DB::table('wp_posts')
        ->where('ID', $orderId)
        ->increment('comment_count');
    
    return $commentId;
}
```

### Status-Specific Event Handling
```php
private function handleStatusSpecificEvents($orderId, $status)
{
    switch ($status) {
        case 'wc-processing':
            $this->addOrderNote($orderId, 'Order received and is now being processed.', true);
            // Trigger processing email
            $this->triggerOrderEmail($orderId, 'processing');
            break;
            
        case 'wc-completed':
            // Set completion date
            DB::table('wp_postmeta')->updateOrInsert(
                ['post_id' => $orderId, 'meta_key' => '_date_completed'],
                ['meta_value' => now()->timestamp]
            );
            
            $this->addOrderNote($orderId, 'Order marked as complete.', true);
            
            // Grant download permissions for digital products
            $this->grantDownloadPermissions($orderId);
            
            // Trigger completion email
            $this->triggerOrderEmail($orderId, 'completed');
            break;
            
        case 'wc-on-hold':
            $this->addOrderNote($orderId, 'Order put on-hold.', true);
            break;
            
        case 'wc-cancelled':
            $this->addOrderNote($orderId, 'Order cancelled by customer.', false);
            // Revoke download permissions
            $this->revokeDownloadPermissions($orderId);
            break;
            
        case 'wc-refunded':
            $this->addOrderNote($orderId, 'Order refunded.', true);
            // Revoke download permissions
            $this->revokeDownloadPermissions($orderId);
            break;
            
        case 'wc-failed':
            $this->addOrderNote($orderId, 'Payment failed or was declined.', false);
            break;
    }
}
```

### Payment Event Notes
```php
public function recordPayment($orderId, $transactionId, $amount, $paymentMethod)
{
    // Update payment meta
    DB::table('wp_postmeta')->updateOrInsert(
        ['post_id' => $orderId, 'meta_key' => '_transaction_id'],
        ['meta_value' => $transactionId]
    );
    
    DB::table('wp_postmeta')->updateOrInsert(
        ['post_id' => $orderId, 'meta_key' => '_paid_date'],
        ['meta_value' => now()->timestamp]
    );
    
    // Add payment note
    $note = "Payment of $" . number_format($amount, 2) . " received via {$paymentMethod}. Transaction ID: {$transactionId}";
    $this->addOrderNote($orderId, $note, false);
    
    // Update status to processing if currently pending
    $currentStatus = DB::table('wp_posts')->where('ID', $orderId)->value('post_status');
    if ($currentStatus === 'wc-pending') {
        $this->updateOrderStatus($orderId, 'wc-processing', 'Payment received successfully.');
    }
}
```

### Refund Event Notes
```php
public function recordRefund($orderId, $amount, $reason = '', $refundId = '')
{
    $note = "Refunded $" . number_format($amount, 2);
    
    if (!empty($reason)) {
        $note .= " - Reason: {$reason}";
    }
    
    if (!empty($refundId)) {
        $note .= " (Refund ID: {$refundId})";
    }
    
    $this->addOrderNote($orderId, $note, true);
}
```

### Shipping Event Notes
```php
public function addTrackingInfo($orderId, $trackingNumber, $carrier = '')
{
    $note = "Tracking number: {$trackingNumber}";
    
    if (!empty($carrier)) {
        $note = "Order shipped via {$carrier}. " . $note;
    }
    
    $this->addOrderNote($orderId, $note, true);
    
    // Store tracking info as meta
    DB::table('wp_postmeta')->updateOrInsert(
        ['post_id' => $orderId, 'meta_key' => '_tracking_number'],
        ['meta_value' => $trackingNumber]
    );
    
    if (!empty($carrier)) {
        DB::table('wp_postmeta')->updateOrInsert(
            ['post_id' => $orderId, 'meta_key' => '_shipping_carrier'],
            ['meta_value' => $carrier]
        );
    }
}
```

### Download Permissions Management
```php
private function grantDownloadPermissions($orderId)
{
    // Get downloadable items
    $downloadableItems = DB::table('wp_woocommerce_order_items as oi')
        ->join('wp_woocommerce_order_itemmeta as oim', 'oi.order_item_id', '=', 'oim.order_item_id')
        ->where('oi.order_id', $orderId)
        ->where('oi.order_item_type', 'line_item')
        ->where('oim.meta_key', '_product_id')
        ->get();
    
    foreach ($downloadableItems as $item) {
        $productId = $item->meta_value;
        
        // Check if product is downloadable
        $isDownloadable = DB::table('wp_postmeta')
            ->where('post_id', $productId)
            ->where('meta_key', '_downloadable')
            ->value('meta_value');
        
        if ($isDownloadable === 'yes') {
            // Grant permissions logic here
            $this->createDownloadPermissions($orderId, $productId);
        }
    }
    
    // Mark permissions as granted
    DB::table('wp_postmeta')->updateOrInsert(
        ['post_id' => $orderId, 'meta_key' => '_download_permissions_granted'],
        ['meta_value' => 'yes']
    );
}

private function revokeDownloadPermissions($orderId)
{
    DB::table('wp_woocommerce_downloadable_product_permissions')
        ->where('order_id', $orderId)
        ->delete();
    
    DB::table('wp_postmeta')
        ->where('post_id', $orderId)
        ->where('meta_key', '_download_permissions_granted')
        ->delete();
    
    $this->addOrderNote($orderId, 'Download permissions revoked.', false);
}
```

### Complete Status Change Handler
```php
public function handleOrderStatusChange($orderId, $newStatus, $note = '', $customerNote = false)
{
    try {
        DB::beginTransaction();
        
        $this->updateOrderStatus($orderId, $newStatus, $note, $customerNote);
        
        DB::commit();
        
        // Log successful status change
        Log::info("Order {$orderId} status changed to {$newStatus}");
        
    } catch (\Exception $e) {
        DB::rollback();
        
        // Log error and add error note
        Log::error("Failed to update order {$orderId} status: " . $e->getMessage());
        $this->addOrderNote($orderId, "Status change failed: " . $e->getMessage(), false);
        
        throw $e;
    }
}
```

---

## Cache Invalidation & Performance

WooCommerce heavily caches order data. When manually inserting orders, you must clear relevant caches.

### Clear Order Caches
```php
public function clearOrderCaches($orderId)
{
    // Clear WooCommerce order transients
    DB::table('wp_options')
        ->where('option_name', 'like', '_transient_wc_order_%' . $orderId . '%')
        ->delete();
    
    // Clear timeout transients
    DB::table('wp_options')
        ->where('option_name', 'like', '_transient_timeout_wc_order_%' . $orderId . '%')
        ->delete();
    
    // Clear order count transients
    DB::table('wp_options')
        ->where('option_name', 'like', '_transient_wc_order_count_%')
        ->delete();
    
    // Clear customer order caches if customer exists
    $customerId = DB::table('wp_postmeta')
        ->where('post_id', $orderId)
        ->where('meta_key', '_customer_user')
        ->value('meta_value');
    
    if ($customerId > 0) {
        DB::table('wp_options')
            ->where('option_name', 'like', '_transient_wc_customer_' . $customerId . '%')
            ->delete();
    }
}

public function clearProductStockCaches($productIds)
{
    foreach ($productIds as $productId) {
        // Clear product stock status transients
        DB::table('wp_options')
            ->where('option_name', 'like', '_transient_wc_product_' . $productId . '%')
            ->delete();
        
        DB::table('wp_options')
            ->where('option_name', 'like', '_transient_timeout_wc_product_' . $productId . '%')
            ->delete();
    }
    
    // Clear general stock transients
    DB::table('wp_options')
        ->where('option_name', 'like', '_transient_wc_low_stock_count%')
        ->delete();
    
    DB::table('wp_options')
        ->where('option_name', 'like', '_transient_wc_outofstock_count%')
        ->delete();
}
```

---

## Timezone Accuracy & Date Handling

Proper timezone handling is crucial for order timestamps and WooCommerce compatibility.

### Carbon Implementation
```php
use Carbon\Carbon;

public function getProperTimestamps()
{
    // Get site timezone from WordPress options
    $timezone = DB::table('wp_options')
        ->where('option_name', 'timezone_string')
        ->value('option_value') ?: 'UTC';
    
    // If timezone_string is empty, check gmt_offset
    if (empty($timezone)) {
        $gmtOffset = DB::table('wp_options')
            ->where('option_name', 'gmt_offset')
            ->value('option_value');
        $timezone = $gmtOffset >= 0 ? "+{$gmtOffset}" : $gmtOffset;
    }
    
    $now = Carbon::now($timezone);
    
    return [
        'local' => $now->format('Y-m-d H:i:s'),
        'gmt' => $now->utc()->format('Y-m-d H:i:s'),
        'timestamp' => $now->timestamp
    ];
}

// Usage in order creation
public function createOrderWithProperTimestamps($orderData)
{
    $timestamps = $this->getProperTimestamps();
    
    $orderId = DB::table('wp_posts')->insertGetId([
        'post_author' => $orderData['customer_id'],
        'post_date' => $timestamps['local'],
        'post_date_gmt' => $timestamps['gmt'],
        'post_modified' => $timestamps['local'],
        'post_modified_gmt' => $timestamps['gmt'],
        // ... other fields
    ]);
    
    return $orderId;
}
```

---

## Enhanced Order Notes with Proper Meta

Improved order notes function with better timezone handling and meta management.

### Complete Order Notes Implementation
```php
public function addOrderNoteWithMeta($orderId, $note, $isCustomerNote = false, $addedBy = 'system', $notifyCustomer = false)
{
    $timestamps = $this->getProperTimestamps();
    
    $noteId = DB::table('wp_comments')->insertGetId([
        'comment_post_ID' => $orderId,
        'comment_author' => $addedBy === 'system' ? 'WooCommerce' : $addedBy,
        'comment_author_email' => '',
        'comment_author_url' => '',
        'comment_author_IP' => request()->ip() ?? '127.0.0.1',
        'comment_date' => $timestamps['local'],
        'comment_date_gmt' => $timestamps['gmt'],
        'comment_content' => $note,
        'comment_karma' => 0,
        'comment_approved' => '1',
        'comment_agent' => substr(request()->userAgent() ?? '', 0, 255),
        'comment_type' => 'order_note',
        'comment_parent' => 0,
        'user_id' => auth()->id() ?? 0
    ]);
    
    // Add customer visibility meta
    DB::table('wp_commentmeta')->insert([
        'comment_id' => $noteId,
        'meta_key' => 'is_customer_note',
        'meta_value' => $isCustomerNote ? '1' : '0'
    ]);
    
    // Add notification meta if specified
    if ($notifyCustomer && $isCustomerNote) {
        DB::table('wp_commentmeta')->insert([
            'comment_id' => $noteId,
            'meta_key' => 'is_customer_notified',
            'meta_value' => '1'
        ]);
    }
    
    // Update comment count
    DB::table('wp_posts')
        ->where('ID', $orderId)
        ->increment('comment_count');
    
    return $noteId;
}
```

---

## Complete Tax Calculation Example

Here's a comprehensive example of creating an order with proper tax calculations and storage.

### Tax Calculation Helper Functions
```php
public function calculateTaxes($items, $shippingCost, $customerData)
{
    // Get tax settings
    $taxSettings = $this->getTaxSettings();
    $taxRates = $this->getTaxRates($customerData['country'], $customerData['state']);
    
    $taxData = [
        'items' => [],
        'shipping' => [],
        'totals' => [
            'tax_total' => 0,
            'shipping_tax_total' => 0
        ]
    ];
    
    foreach ($items as $item) {
        $itemTax = $this->calculateItemTax($item, $taxRates, $taxSettings);
        $taxData['items'][$item['product_id']] = $itemTax;
        $taxData['totals']['tax_total'] += $itemTax['total_tax'];
    }
    
    if ($shippingCost > 0) {
        $shippingTax = $this->calculateShippingTax($shippingCost, $taxRates, $taxSettings);
        $taxData['shipping'] = $shippingTax;
        $taxData['totals']['shipping_tax_total'] = $shippingTax['total_tax'];
    }
    
    return $taxData;
}

private function getTaxSettings()
{
    $settings = DB::table('wp_options')
        ->whereIn('option_name', [
            'woocommerce_calc_taxes',
            'woocommerce_prices_include_tax',
            'woocommerce_tax_round_at_subtotal',
            'woocommerce_tax_display_cart'
        ])
        ->pluck('option_value', 'option_name');
    
    return [
        'calc_taxes' => $settings['woocommerce_calc_taxes'] === 'yes',
        'prices_include_tax' => $settings['woocommerce_prices_include_tax'] === 'yes',
        'round_at_subtotal' => $settings['woocommerce_tax_round_at_subtotal'] === 'yes',
        'display_cart' => $settings['woocommerce_tax_display_cart'] ?? 'excl'
    ];
}

private function getTaxRates($country, $state = '')
{
    return DB::table('wp_woocommerce_tax_rates')
        ->where(function($query) use ($country, $state) {
            $query->where('tax_rate_country', '')
                  ->orWhere('tax_rate_country', $country);
        })
        ->where(function($query) use ($state) {
            $query->where('tax_rate_state', '')
                  ->orWhere('tax_rate_state', $state);
        })
        ->orderBy('tax_rate_priority')
        ->orderBy('tax_rate_order')
        ->get();
}

private function calculateItemTax($item, $taxRates, $taxSettings)
{
    $taxableAmount = $item['line_total'];
    $taxData = [
        'total_tax' => 0,
        'subtotal_tax' => 0,
        'tax_data' => []
    ];
    
    if (!$taxSettings['calc_taxes']) {
        return $taxData;
    }
    
    foreach ($taxRates as $rate) {
        if ($this->productMatchesTaxClass($item['product_id'], $rate->tax_rate_class)) {
            $taxAmount = $this->calculateTaxAmount($taxableAmount, $rate->tax_rate, $taxSettings);
            
            $taxData['tax_data'][$rate->tax_rate_id] = [
                'rate_id' => $rate->tax_rate_id,
                'rate' => $rate->tax_rate,
                'amount' => $taxAmount,
                'compound' => $rate->tax_rate_compound
            ];
            
            $taxData['total_tax'] += $taxAmount;
            $taxData['subtotal_tax'] += $this->calculateTaxAmount($item['line_subtotal'], $rate->tax_rate, $taxSettings);
        }
    }
    
    return $taxData;
}

private function calculateTaxAmount($amount, $rate, $taxSettings)
{
    if ($taxSettings['prices_include_tax']) {
        // Tax inclusive calculation
        $taxAmount = ($amount * $rate) / (100 + $rate);
    } else {
        // Tax exclusive calculation
        $taxAmount = ($amount * $rate) / 100;
    }
    
    return $taxSettings['round_at_subtotal'] ? $taxAmount : round($taxAmount, 4);
}
```

### Complete Order Creation with Tax
```php
public function createOrderWithTax($orderData)
{
    try {
        DB::beginTransaction();
        
        $timestamps = $this->getProperTimestamps();
        
        // Calculate taxes
        $taxData = $this->calculateTaxes(
            $orderData['items'], 
            $orderData['shipping_cost'], 
            $orderData['customer']
        );
        
        // Calculate totals
        $subtotal = array_sum(array_column($orderData['items'], 'line_total'));
        $taxTotal = $taxData['totals']['tax_total'];
        $shippingTax = $taxData['totals']['shipping_tax_total'];
        $total = $subtotal + $orderData['shipping_cost'] + $taxTotal + $shippingTax;
        
        // Create order post
        $orderId = DB::table('wp_posts')->insertGetId([
            'post_author' => $orderData['customer']['user_id'],
            'post_date' => $timestamps['local'],
            'post_date_gmt' => $timestamps['gmt'],
            'post_content' => '',
            'post_title' => 'Order #' . $orderData['order_number'],
            'post_excerpt' => $orderData['customer_notes'] ?? '',
            'post_status' => 'wc-pending',
            'comment_status' => 'open',
            'ping_status' => 'closed',
            'post_password' => '',
            'post_name' => 'order-' . $orderData['order_number'],
            'post_type' => 'shop_order',
            'post_modified' => $timestamps['local'],
            'post_modified_gmt' => $timestamps['gmt'],
            'comment_count' => 0
        ]);
        
        // Add order meta with tax data
        $orderMeta = [
            '_order_key' => wp_generate_password(13, false),
            '_order_currency' => $orderData['currency'],
            '_prices_include_tax' => $this->getTaxSettings()['prices_include_tax'] ? 'yes' : 'no',
            '_order_total' => number_format($total, 2, '.', ''),
            '_order_tax' => number_format($taxTotal, 2, '.', ''),
            '_order_shipping' => number_format($orderData['shipping_cost'], 2, '.', ''),
            '_order_shipping_tax' => number_format($shippingTax, 2, '.', ''),
            '_cart_discount' => '0.00',
            '_cart_discount_tax' => '0.00',
            '_order_version' => WC()->version ?? '6.0.0',
            '_customer_user' => $orderData['customer']['user_id'],
            '_customer_ip_address' => request()->ip(),
            '_customer_user_agent' => request()->userAgent(),
            '_created_via' => 'api',
            '_billing_first_name' => $orderData['billing']['first_name'],
            '_billing_last_name' => $orderData['billing']['last_name'],
            '_billing_company' => $orderData['billing']['company'] ?? '',
            '_billing_address_1' => $orderData['billing']['address_1'],
            '_billing_address_2' => $orderData['billing']['address_2'] ?? '',
            '_billing_city' => $orderData['billing']['city'],
            '_billing_state' => $orderData['billing']['state'],
            '_billing_postcode' => $orderData['billing']['postcode'],
            '_billing_country' => $orderData['billing']['country'],
            '_billing_email' => $orderData['billing']['email'],
            '_billing_phone' => $orderData['billing']['phone'] ?? '',
            '_shipping_first_name' => $orderData['shipping']['first_name'] ?? $orderData['billing']['first_name'],
            '_shipping_last_name' => $orderData['shipping']['last_name'] ?? $orderData['billing']['last_name'],
            '_shipping_company' => $orderData['shipping']['company'] ?? '',
            '_shipping_address_1' => $orderData['shipping']['address_1'] ?? $orderData['billing']['address_1'],
            '_shipping_address_2' => $orderData['shipping']['address_2'] ?? '',
            '_shipping_city' => $orderData['shipping']['city'] ?? $orderData['billing']['city'],
            '_shipping_state' => $orderData['shipping']['state'] ?? $orderData['billing']['state'],
            '_shipping_postcode' => $orderData['shipping']['postcode'] ?? $orderData['billing']['postcode'],
            '_shipping_country' => $orderData['shipping']['country'] ?? $orderData['billing']['country'],
            '_payment_method' => $orderData['payment_method'],
            '_payment_method_title' => $orderData['payment_method_title'],
        ];
        
        foreach ($orderMeta as $key => $value) {
            DB::table('wp_postmeta')->insert([
                'post_id' => $orderId,
                'meta_key' => $key,
                'meta_value' => $value
            ]);
        }
        
        // Add line items with tax
        foreach ($orderData['items'] as $item) {
            $itemId = DB::table('wp_woocommerce_order_items')->insertGetId([
                'order_item_name' => $item['name'],
                'order_item_type' => 'line_item',
                'order_id' => $orderId
            ]);
            
            $itemTax = $taxData['items'][$item['product_id']];
            
            $itemMeta = [
                '_product_id' => $item['product_id'],
                '_variation_id' => $item['variation_id'] ?? 0,
                '_qty' => $item['quantity'],
                '_line_subtotal' => number_format($item['line_subtotal'], 2, '.', ''),
                '_line_subtotal_tax' => number_format($itemTax['subtotal_tax'], 2, '.', ''),
                '_line_total' => number_format($item['line_total'], 2, '.', ''),
                '_line_tax' => number_format($itemTax['total_tax'], 2, '.', ''),
                '_line_tax_data' => serialize([
                    'total' => array_column($itemTax['tax_data'], 'amount', 'rate_id'),
                    'subtotal' => array_column($itemTax['tax_data'], 'amount', 'rate_id') // Simplified for example
                ])
            ];
            
            foreach ($itemMeta as $key => $value) {
                DB::table('wp_woocommerce_order_itemmeta')->insert([
                    'order_item_id' => $itemId,
                    'meta_key' => $key,
                    'meta_value' => $value
                ]);
            }
        }
        
        // Add shipping item with tax
        if ($orderData['shipping_cost'] > 0) {
            $shippingItemId = DB::table('wp_woocommerce_order_items')->insertGetId([
                'order_item_name' => $orderData['shipping_method_title'],
                'order_item_type' => 'shipping',
                'order_id' => $orderId
            ]);
            
            $shippingMeta = [
                'method_id' => $orderData['shipping_method_id'],
                'instance_id' => $orderData['shipping_instance_id'] ?? '',
                'cost' => number_format($orderData['shipping_cost'], 2, '.', ''),
                'total_tax' => number_format($shippingTax, 2, '.', ''),
                'taxes' => serialize(['total' => [$taxData['shipping']['rate_id'] ?? 0 => $shippingTax]])
            ];
            
            foreach ($shippingMeta as $key => $value) {
                DB::table('wp_woocommerce_order_itemmeta')->insert([
                    'order_item_id' => $shippingItemId,
                    'meta_key' => $key,
                    'meta_value' => $value
                ]);
            }
        }
        
        // Add tax items
        foreach ($this->consolidateTaxRates($taxData) as $rateId => $taxInfo) {
            $taxItemId = DB::table('wp_woocommerce_order_items')->insertGetId([
                'order_item_name' => $taxInfo['label'],
                'order_item_type' => 'tax',
                'order_id' => $orderId
            ]);
            
            $taxItemMeta = [
                'rate_id' => $rateId,
                'label' => $taxInfo['label'],
                'compound' => $taxInfo['compound'] ? '1' : '0',
                'tax_amount' => number_format($taxInfo['tax_amount'], 2, '.', ''),
                'shipping_tax_amount' => number_format($taxInfo['shipping_tax_amount'], 2, '.', ''),
                'rate_code' => $taxInfo['rate_code'],
                'rate_percent' => $taxInfo['rate_percent']
            ];
            
            foreach ($taxItemMeta as $key => $value) {
                DB::table('wp_woocommerce_order_itemmeta')->insert([
                    'order_item_id' => $taxItemId,
                    'meta_key' => $key,
                    'meta_value' => $value
                ]);
            }
        }
        
        // Clear caches
        $this->clearOrderCaches($orderId);
        $this->clearProductStockCaches(array_column($orderData['items'], 'product_id'));
        
        // Add creation note
        $this->addOrderNoteWithMeta($orderId, 'Order created via API.', false);
        
        DB::commit();
        
        return $orderId;
        
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }
}

private function consolidateTaxRates($taxData)
{
    $consolidated = [];
    
    // Consolidate item taxes
    foreach ($taxData['items'] as $itemTaxes) {
        foreach ($itemTaxes['tax_data'] as $rateId => $taxInfo) {
            if (!isset($consolidated[$rateId])) {
                $rate = DB::table('wp_woocommerce_tax_rates')->find($rateId);
                $consolidated[$rateId] = [
                    'label' => $rate->tax_rate_name,
                    'compound' => $rate->tax_rate_compound,
                    'tax_amount' => 0,
                    'shipping_tax_amount' => 0,
                    'rate_code' => $rate->tax_rate_code,
                    'rate_percent' => $rate->tax_rate
                ];
            }
            $consolidated[$rateId]['tax_amount'] += $taxInfo['amount'];
        }
    }
    
    // Add shipping taxes
    if (!empty($taxData['shipping']['rate_id'])) {
        $rateId = $taxData['shipping']['rate_id'];
        if (isset($consolidated[$rateId])) {
            $consolidated[$rateId]['shipping_tax_amount'] = $taxData['shipping']['total_tax'];
        }
    }
    
    return $consolidated;
}
```

---

## Validation Checklist

Before going live, ensure:

- [ ] All required meta keys are present
- [ ] Order totals calculate correctly
- [ ] Stock is properly reduced
- [ ] Customer can view order in their account
- [ ] Order appears correctly in WooCommerce admin
- [ ] Emails are sent (if required)
- [ ] Tax calculations are accurate
- [ ] Shipping data is complete
- [ ] Payment status is correct

---

This comprehensive guide covers all the database requirements for manually creating WooCommerce orders. Always test thoroughly in a development environment before implementing in production.