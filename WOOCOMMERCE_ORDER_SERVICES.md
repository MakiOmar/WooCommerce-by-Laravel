# WooCommerce Order Management Services

This document explains how to use the new WooCommerce order management services that provide comprehensive order handling capabilities.

## Overview

The package now includes four main services for WooCommerce order management:

1. **WooCommerceStockService** - Handles stock reduction and restoration
2. **WooCommerceOrderNotesService** - Manages order notes with customer visibility
3. **WooCommerceCacheService** - Handles cache invalidation
4. **WooCommerceOrderManagementService** - Main service that integrates all functionality

## Services

### WooCommerceStockService

Handles automatic stock management based on order status changes.

```php
use Makiomar\WooOrderDashboard\Services\WooCommerceStockService;

$stockService = app(WooCommerceStockService::class);

// Reduce stock for an order
$stockService->reduceOrderStock($orderId);

// Restore stock for an order
$stockService->restoreOrderStock($orderId);

// Handle stock changes when status changes
$stockService->handleStockOnStatusChange($orderId, $oldStatus, $newStatus);

// Check if order has stock reduced
$isReduced = $stockService->isOrderStockReduced($orderId);

// Get stock status for a product
$stockStatus = $stockService->getProductStockStatus($productId);
```

**Stock Reducing Statuses**: `on-hold`, `processing`, `completed`
**Stock Restoring Statuses**: `cancelled`, `refunded`, `failed`

### WooCommerceOrderNotesService

Manages order notes with proper timezone handling and customer visibility control.

```php
use Makiomar\WooOrderDashboard\Services\WooCommerceOrderNotesService;

$notesService = app(WooCommerceOrderNotesService::class);

// Add a general order note
$noteId = $notesService->addOrderNote($orderId, 'Order note content', false);

// Add a customer-visible note
$noteId = $notesService->addOrderNote($orderId, 'Customer note content', true);

// Add status change note
$notesService->addStatusChangeNote($orderId, $oldStatus, $newStatus);

// Add payment note
$notesService->addPaymentNote($orderId, $transactionId, $amount, $paymentMethod);

// Add refund note
$notesService->addRefundNote($orderId, $amount, $reason, $refundId);

// Add shipping note
$notesService->addShippingNote($orderId, $trackingNumber, $carrier);

// Get order notes
$notes = $notesService->getOrderNotes($orderId, $includeCustomerNotes);

// Get note count
$count = $notesService->getOrderNoteCount($orderId);
```

### WooCommerceCacheService

Handles comprehensive cache invalidation for WooCommerce data.

```php
use Makiomar\WooOrderDashboard\Services\WooCommerceCacheService;

$cacheService = app(WooCommerceCacheService::class);

// Clear all order cache
$cacheService->clearOrderCache();

// Clear specific order cache
$cacheService->clearOrderCacheById($orderId);

// Clear customer order caches
$cacheService->clearCustomerOrderCaches($customerId);

// Clear product stock caches
$cacheService->clearProductStockCaches($productIds);

// Clear cache on specific events
$cacheService->clearCacheOnOrderCreate();
$cacheService->clearCacheOnOrderUpdate($orderId);
$cacheService->clearCacheOnOrderDelete($orderIds);
$cacheService->clearCacheOnOrderStatusChange($orderId);

// Clear all WooCommerce cache
$cacheService->clearAllWooCommerceCache();
```

### WooCommerceOrderManagementService

Main service that integrates all functionality for comprehensive order management.

```php
use Makiomar\WooOrderDashboard\Services\WooCommerceOrderManagementService;

$orderService = app(WooCommerceOrderManagementService::class);

// Update order status with full handling
$success = $orderService->updateOrderStatus($orderId, $newStatus, $note, $customerNote);

// Record payment
$orderService->recordPayment($orderId, $transactionId, $amount, $paymentMethod);

// Record refund
$orderService->recordRefund($orderId, $amount, $reason, $refundId);

// Add tracking information
$orderService->addTrackingInfo($orderId, $trackingNumber, $carrier);

// Get order summary
$summary = $orderService->getOrderSummary($orderId);

// Get formatted order notes
$notes = $orderService->getOrderNotesFormatted($orderId, $includeCustomerNotes);

// Check order capabilities
$canCancel = $orderService->canCancelOrder($orderId);
$canRefund = $orderService->canRefundOrder($orderId);

// Get order status history
$history = $orderService->getOrderStatusHistory($orderId);
```

## Usage Examples

### Complete Order Status Update

```php
use Makiomar\WooOrderDashboard\Services\WooCommerceOrderManagementService;

$orderService = app(WooCommerceOrderManagementService::class);

// This will:
// 1. Update the order status
// 2. Handle stock changes automatically
// 3. Add status change notes
// 4. Handle status-specific events
// 5. Clear relevant caches
$success = $orderService->updateOrderStatus(
    $orderId, 
    'wc-processing', 
    'Order is now being processed', 
    true // Customer note
);
```

### Payment Recording

```php
$orderService->recordPayment(
    $orderId,
    'txn_123456789',
    99.99,
    'Stripe'
);
```

### Refund Processing

```php
$orderService->recordRefund(
    $orderId,
    50.00,
    'Customer requested partial refund',
    'ref_987654321'
);
```

### Shipping Tracking

```php
$orderService->addTrackingInfo(
    $orderId,
    '1Z999AA1234567890',
    'UPS'
);
```

## Integration with Controllers

You can inject these services into your controllers:

```php
use Makiomar\WooOrderDashboard\Services\WooCommerceOrderManagementService;

class OrdersController extends Controller
{
    protected WooCommerceOrderManagementService $orderService;

    public function __construct(WooCommerceOrderManagementService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function updateStatus(Request $request, $orderId)
    {
        $success = $this->orderService->updateOrderStatus(
            $orderId,
            $request->input('status'),
            $request->input('note'),
            $request->boolean('customer_note')
        );

        return response()->json(['success' => $success]);
    }
}
```

## Error Handling

All services include comprehensive error handling and logging:

```php
try {
    $success = $orderService->updateOrderStatus($orderId, $newStatus);
    
    if (!$success) {
        // Handle failure
        Log::error("Failed to update order status");
    }
} catch (\Exception $e) {
    // Handle exception
    Log::error("Exception updating order status: " . $e->getMessage());
}
```

## Testing

The package includes comprehensive tests for all services:

```bash
# Run the WooCommerce order management tests
php artisan test --filter=WooCommerceOrderManagementTest
```

## Configuration

The services use the existing WooCommerce database connection configuration. Ensure your `config/database.php` includes:

```php
'woocommerce' => [
    'driver' => 'mysql',
    'host' => env('WOO_DB_HOST', '127.0.0.1'),
    'port' => env('WOO_DB_PORT', '3306'),
    'database' => env('WOO_DB_DATABASE'),
    'username' => env('WOO_DB_USERNAME'),
    'password' => env('WOO_DB_PASSWORD'),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => env('WOO_DB_PREFIX', 'wp_'),
    'strict' => true,
    'engine' => null,
],
```

## Benefits

1. **Automatic Stock Management**: Stock is automatically reduced/restored based on order status changes
2. **Comprehensive Order Notes**: Full order note system with customer visibility control
3. **Proper Cache Management**: Automatic cache invalidation ensures data consistency
4. **Timezone Handling**: Proper timezone-aware timestamps using Carbon
5. **Transaction Safety**: All operations use database transactions for data integrity
6. **Error Handling**: Comprehensive error handling and logging
7. **Test Coverage**: Full test coverage for all services

## Migration from Previous Version

If you were using manual order status updates, you can now use the integrated service:

```php
// Old way
DB::table('wp_posts')->where('ID', $orderId)->update(['post_status' => $newStatus]);

// New way
$orderService->updateOrderStatus($orderId, $newStatus, $note, $customerNote);
```

This ensures all related operations (stock, notes, cache) are handled automatically. 