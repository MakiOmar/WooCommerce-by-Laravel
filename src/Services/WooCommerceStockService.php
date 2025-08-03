<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WooCommerceStockService
{
    /**
     * Stock reducing statuses - when order moves to these statuses, stock should be reduced
     */
    private const STOCK_REDUCING_STATUSES = ['on-hold', 'processing', 'completed'];

    /**
     * Stock restoring statuses - when order moves to these statuses, stock should be restored
     */
    private const STOCK_RESTORING_STATUSES = ['cancelled', 'refunded', 'failed'];

    /**
     * Reduce stock for an order
     *
     * @param int $orderId
     * @return bool
     */
    public function reduceOrderStock(int $orderId): bool
    {
        try {
            // Check if stock already reduced
            $stockReduced = DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->where('post_id', $orderId)
                ->where('meta_key', '_order_stock_reduced')
                ->value('meta_value');

            if ($stockReduced === 'yes') {
                Log::info("Stock already reduced for order {$orderId}");
                return true; // Already reduced
            }

            // Get all line items
            $lineItems = DB::connection('woocommerce')
                ->table('wp_woocommerce_order_items')
                ->where('order_id', $orderId)
                ->where('order_item_type', 'line_item')
                ->get();

            foreach ($lineItems as $item) {
                $this->reduceStockForItem($item->order_item_id);
            }

            // Mark stock as reduced
            DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->insert([
                    'post_id' => $orderId,
                    'meta_key' => '_order_stock_reduced',
                    'meta_value' => 'yes'
                ]);

            Log::info("Stock reduced successfully for order {$orderId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to reduce stock for order {$orderId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Restore stock for an order
     *
     * @param int $orderId
     * @return bool
     */
    public function restoreOrderStock(int $orderId): bool
    {
        try {
            // Check if stock was reduced
            $stockReduced = DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->where('post_id', $orderId)
                ->where('meta_key', '_order_stock_reduced')
                ->value('meta_value');

            if ($stockReduced !== 'yes') {
                Log::info("Stock wasn't reduced for order {$orderId}, nothing to restore");
                return true; // Stock wasn't reduced
            }

            // Get all line items with reduced stock
            $lineItems = DB::connection('woocommerce')
                ->table('wp_woocommerce_order_items')
                ->where('order_id', $orderId)
                ->where('order_item_type', 'line_item')
                ->get();

            foreach ($lineItems as $item) {
                $this->restoreStockForItem($item->order_item_id);
            }

            // Remove stock reduced flag
            DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->where('post_id', $orderId)
                ->where('meta_key', '_order_stock_reduced')
                ->delete();

            Log::info("Stock restored successfully for order {$orderId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to restore stock for order {$orderId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle stock changes when order status changes
     *
     * @param int $orderId
     * @param string $oldStatus
     * @param string $newStatus
     * @return bool
     */
    public function handleStockOnStatusChange(int $orderId, string $oldStatus, string $newStatus): bool
    {
        $oldReducesStock = in_array($oldStatus, self::STOCK_REDUCING_STATUSES);
        $newReducesStock = in_array($newStatus, self::STOCK_REDUCING_STATUSES);
        $newRestoresStock = in_array($newStatus, self::STOCK_RESTORING_STATUSES);

        if (!$oldReducesStock && $newReducesStock) {
            // Moving to stock-reducing status
            Log::info("Order {$orderId} moving to stock-reducing status: {$newStatus}");
            return $this->reduceOrderStock($orderId);
        } elseif ($oldReducesStock && $newRestoresStock) {
            // Moving to stock-restoring status
            Log::info("Order {$orderId} moving to stock-restoring status: {$newStatus}");
            return $this->restoreOrderStock($orderId);
        }

        return true; // No stock change needed
    }

    /**
     * Reduce stock for a specific order item
     *
     * @param int $itemId
     * @return bool
     */
    private function reduceStockForItem(int $itemId): bool
    {
        try {
            $productId = DB::connection('woocommerce')
                ->table('wp_woocommerce_order_itemmeta')
                ->where('order_item_id', $itemId)
                ->where('meta_key', '_product_id')
                ->value('meta_value');

            $variationId = DB::connection('woocommerce')
                ->table('wp_woocommerce_order_itemmeta')
                ->where('order_item_id', $itemId)
                ->where('meta_key', '_variation_id')
                ->value('meta_value');

            $quantity = DB::connection('woocommerce')
                ->table('wp_woocommerce_order_itemmeta')
                ->where('order_item_id', $itemId)
                ->where('meta_key', '_qty')
                ->value('meta_value');

            // Use variation ID if it exists, otherwise use product ID
            $stockProductId = $variationId > 0 ? $variationId : $productId;

            // Check if product manages stock
            $manageStock = DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->where('post_id', $stockProductId)
                ->where('meta_key', '_manage_stock')
                ->value('meta_value');

            if ($manageStock !== 'yes') {
                return true; // Product doesn't manage stock
            }

            // Get current stock
            $currentStock = DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->where('post_id', $stockProductId)
                ->where('meta_key', '_stock')
                ->value('meta_value');

            $newStock = max(0, $currentStock - $quantity);

            // Update stock
            DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->where('post_id', $stockProductId)
                ->where('meta_key', '_stock')
                ->update(['meta_value' => $newStock]);

            // Update stock status if needed
            if ($newStock <= 0) {
                DB::connection('woocommerce')
                    ->table('wp_postmeta')
                    ->where('post_id', $stockProductId)
                    ->where('meta_key', '_stock_status')
                    ->update(['meta_value' => 'outofstock']);
            }

            // Record the reduction in order item meta
            DB::connection('woocommerce')
                ->table('wp_woocommerce_order_itemmeta')
                ->insert([
                    'order_item_id' => $itemId,
                    'meta_key' => '_reduced_stock',
                    'meta_value' => $quantity
                ]);

            Log::info("Stock reduced for product {$stockProductId}: {$currentStock} -> {$newStock} (reduced by {$quantity})");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to reduce stock for item {$itemId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Restore stock for a specific order item
     *
     * @param int $itemId
     * @return bool
     */
    private function restoreStockForItem(int $itemId): bool
    {
        try {
            $reducedStock = DB::connection('woocommerce')
                ->table('wp_woocommerce_order_itemmeta')
                ->where('order_item_id', $itemId)
                ->where('meta_key', '_reduced_stock')
                ->value('meta_value');

            if (!$reducedStock) {
                return true; // No stock was reduced for this item
            }

            $productId = DB::connection('woocommerce')
                ->table('wp_woocommerce_order_itemmeta')
                ->where('order_item_id', $itemId)
                ->where('meta_key', '_product_id')
                ->value('meta_value');

            $variationId = DB::connection('woocommerce')
                ->table('wp_woocommerce_order_itemmeta')
                ->where('order_item_id', $itemId)
                ->where('meta_key', '_variation_id')
                ->value('meta_value');

            $stockProductId = $variationId > 0 ? $variationId : $productId;

            // Restore stock
            $currentStock = DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->where('post_id', $stockProductId)
                ->where('meta_key', '_stock')
                ->value('meta_value');

            $newStock = $currentStock + $reducedStock;

            DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->where('post_id', $stockProductId)
                ->where('meta_key', '_stock')
                ->update(['meta_value' => $newStock]);

            // Update stock status
            if ($newStock > 0) {
                DB::connection('woocommerce')
                    ->table('wp_postmeta')
                    ->where('post_id', $stockProductId)
                    ->where('meta_key', '_stock_status')
                    ->update(['meta_value' => 'instock']);
            }

            // Remove reduced stock meta
            DB::connection('woocommerce')
                ->table('wp_woocommerce_order_itemmeta')
                ->where('order_item_id', $itemId)
                ->where('meta_key', '_reduced_stock')
                ->delete();

            Log::info("Stock restored for product {$stockProductId}: {$currentStock} -> {$newStock} (restored by {$reducedStock})");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to restore stock for item {$itemId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get stock status for a product
     *
     * @param int $productId
     * @return array
     */
    public function getProductStockStatus(int $productId): array
    {
        $manageStock = DB::connection('woocommerce')
            ->table('wp_postmeta')
            ->where('post_id', $productId)
            ->where('meta_key', '_manage_stock')
            ->value('meta_value');

        if ($manageStock !== 'yes') {
            return [
                'manages_stock' => false,
                'stock_quantity' => null,
                'stock_status' => 'instock'
            ];
        }

        $stockQuantity = DB::connection('woocommerce')
            ->table('wp_postmeta')
            ->where('post_id', $productId)
            ->where('meta_key', '_stock')
            ->value('meta_value');

        $stockStatus = DB::connection('woocommerce')
            ->table('wp_postmeta')
            ->where('post_id', $productId)
            ->where('meta_key', '_stock_status')
            ->value('meta_value');

        return [
            'manages_stock' => true,
            'stock_quantity' => (int) $stockQuantity,
            'stock_status' => $stockStatus
        ];
    }

    /**
     * Check if order has stock reduced
     *
     * @param int $orderId
     * @return bool
     */
    public function isOrderStockReduced(int $orderId): bool
    {
        $stockReduced = DB::connection('woocommerce')
            ->table('wp_postmeta')
            ->where('post_id', $orderId)
            ->where('meta_key', '_order_stock_reduced')
            ->value('meta_value');

        return $stockReduced === 'yes';
    }

    /**
     * Get stock reducing statuses
     *
     * @return array
     */
    public function getStockReducingStatuses(): array
    {
        return self::STOCK_REDUCING_STATUSES;
    }

    /**
     * Get stock restoring statuses
     *
     * @return array
     */
    public function getStockRestoringStatuses(): array
    {
        return self::STOCK_RESTORING_STATUSES;
    }
} 