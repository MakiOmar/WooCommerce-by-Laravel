<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WooCommerceOrderManagementService
{
    protected WooCommerceStockService $stockService;
    protected WooCommerceOrderNotesService $notesService;
    protected WooCommerceCacheService $cacheService;

    public function __construct(
        WooCommerceStockService $stockService,
        WooCommerceOrderNotesService $notesService,
        WooCommerceCacheService $cacheService
    ) {
        $this->stockService = $stockService;
        $this->notesService = $notesService;
        $this->cacheService = $cacheService;
    }

    /**
     * Update order status with comprehensive handling
     *
     * @param int $orderId
     * @param string $newStatus
     * @param string $note
     * @param bool $customerNote
     * @return bool
     */
    public function updateOrderStatus(int $orderId, string $newStatus, string $note = '', bool $customerNote = false): bool
    {
        try {
            DB::beginTransaction();

            // Get current status
            $oldStatus = DB::connection('woocommerce')
                ->table('wp_posts')
                ->where('ID', $orderId)
                ->value('post_status');

            // Remove wc- prefix if present for comparison
            $oldStatusClean = str_replace('wc-', '', $oldStatus);
            $newStatusClean = str_replace('wc-', '', $newStatus);

            // Update order status
            DB::connection('woocommerce')
                ->table('wp_posts')
                ->where('ID', $orderId)
                ->update([
                    'post_status' => $newStatus,
                    'post_modified' => now(),
                    'post_modified_gmt' => now()->utc()
                ]);

            // Handle stock changes
            $this->stockService->handleStockOnStatusChange($orderId, $oldStatusClean, $newStatusClean);

            // Add status change note
            $this->notesService->addStatusChangeNote($orderId, $oldStatusClean, $newStatusClean);

            // Add custom note if provided
            if (!empty($note)) {
                $this->notesService->addOrderNote($orderId, $note, $customerNote);
            }

            // Handle specific status events
            $this->handleStatusSpecificEvents($orderId, $newStatusClean);

            // Clear caches
            $this->cacheService->clearCacheOnOrderStatusChange($orderId);

            DB::commit();

            Log::info("Order {$orderId} status changed from {$oldStatusClean} to {$newStatusClean}");
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to update order {$orderId} status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record payment for an order
     *
     * @param int $orderId
     * @param string $transactionId
     * @param float $amount
     * @param string $paymentMethod
     * @return bool
     */
    public function recordPayment(int $orderId, string $transactionId, float $amount, string $paymentMethod): bool
    {
        try {
            DB::beginTransaction();

            // Update payment meta
            DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->updateOrInsert(
                    ['post_id' => $orderId, 'meta_key' => '_transaction_id'],
                    ['meta_value' => $transactionId]
                );

            DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->updateOrInsert(
                    ['post_id' => $orderId, 'meta_key' => '_paid_date'],
                    ['meta_value' => now()->timestamp]
                );

            // Add payment note
            $this->notesService->addPaymentNote($orderId, $transactionId, $amount, $paymentMethod);

            // Update status to processing if currently pending
            $currentStatus = DB::connection('woocommerce')
                ->table('wp_posts')
                ->where('ID', $orderId)
                ->value('post_status');

            if ($currentStatus === 'wc-pending') {
                $this->updateOrderStatus($orderId, 'wc-processing', 'Payment received successfully.');
            }

            // Clear caches
            $this->cacheService->clearCacheOnOrderUpdate($orderId);

            DB::commit();

            Log::info("Payment recorded for order {$orderId}: {$amount} via {$paymentMethod}");
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to record payment for order {$orderId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record refund for an order
     *
     * @param int $orderId
     * @param float $amount
     * @param string $reason
     * @param string $refundId
     * @return bool
     */
    public function recordRefund(int $orderId, float $amount, string $reason = '', string $refundId = ''): bool
    {
        try {
            DB::beginTransaction();

            // Add refund note
            $this->notesService->addRefundNote($orderId, $amount, $reason, $refundId);

            // Clear caches
            $this->cacheService->clearCacheOnOrderUpdate($orderId);

            DB::commit();

            Log::info("Refund recorded for order {$orderId}: {$amount}");
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to record refund for order {$orderId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add shipping tracking information
     *
     * @param int $orderId
     * @param string $trackingNumber
     * @param string $carrier
     * @return bool
     */
    public function addTrackingInfo(int $orderId, string $trackingNumber, string $carrier = ''): bool
    {
        try {
            DB::beginTransaction();

            // Add shipping note
            $this->notesService->addShippingNote($orderId, $trackingNumber, $carrier);

            // Store tracking info as meta
            DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->updateOrInsert(
                    ['post_id' => $orderId, 'meta_key' => '_tracking_number'],
                    ['meta_value' => $trackingNumber]
                );

            if (!empty($carrier)) {
                DB::connection('woocommerce')
                    ->table('wp_postmeta')
                    ->updateOrInsert(
                        ['post_id' => $orderId, 'meta_key' => '_shipping_carrier'],
                        ['meta_value' => $carrier]
                    );
            }

            // Clear caches
            $this->cacheService->clearCacheOnOrderUpdate($orderId);

            DB::commit();

            Log::info("Tracking info added for order {$orderId}: {$trackingNumber} via {$carrier}");
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to add tracking info for order {$orderId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle status-specific events
     *
     * @param int $orderId
     * @param string $status
     * @return void
     */
    private function handleStatusSpecificEvents(int $orderId, string $status): void
    {
        switch ($status) {
            case 'processing':
                $this->notesService->addOrderNote($orderId, 'Order received and is now being processed.', true);
                break;

            case 'completed':
                // Set completion date
                DB::connection('woocommerce')
                    ->table('wp_postmeta')
                    ->updateOrInsert(
                        ['post_id' => $orderId, 'meta_key' => '_date_completed'],
                        ['meta_value' => now()->timestamp]
                    );

                $this->notesService->addOrderNote($orderId, 'Order marked as complete.', true);
                break;

            case 'on-hold':
                $this->notesService->addOrderNote($orderId, 'Order put on-hold.', true);
                break;

            case 'cancelled':
                $this->notesService->addOrderNote($orderId, 'Order cancelled by customer.', false);
                break;

            case 'refunded':
                $this->notesService->addOrderNote($orderId, 'Order refunded.', true);
                break;

            case 'failed':
                $this->notesService->addOrderNote($orderId, 'Payment failed or was declined.', false);
                break;
        }
    }

    /**
     * Get order summary with stock and note information
     *
     * @param int $orderId
     * @return array
     */
    public function getOrderSummary(int $orderId): array
    {
        try {
            $order = DB::connection('woocommerce')
                ->table('wp_posts')
                ->where('ID', $orderId)
                ->where('post_type', 'shop_order')
                ->first();

            if (!$order) {
                return [];
            }

            $orderMeta = DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->where('post_id', $orderId)
                ->pluck('meta_value', 'meta_key')
                ->toArray();

            $orderItems = DB::connection('woocommerce')
                ->table('wp_woocommerce_order_items')
                ->where('order_id', $orderId)
                ->where('order_item_type', 'line_item')
                ->get();

            $noteCount = $this->notesService->getOrderNoteCount($orderId);
            $isStockReduced = $this->stockService->isOrderStockReduced($orderId);

            return [
                'order' => $order,
                'meta' => $orderMeta,
                'items' => $orderItems,
                'note_count' => $noteCount,
                'stock_reduced' => $isStockReduced,
                'status' => str_replace('wc-', '', $order->post_status)
            ];

        } catch (\Exception $e) {
            Log::error("Failed to get order summary for order {$orderId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get order notes with proper formatting
     *
     * @param int $orderId
     * @param bool $includeCustomerNotes
     * @return array
     */
    public function getOrderNotesFormatted(int $orderId, bool $includeCustomerNotes = true): array
    {
        try {
            $notes = $this->notesService->getOrderNotes($orderId, $includeCustomerNotes);
            
            $formattedNotes = [];
            foreach ($notes as $note) {
                $formattedNotes[] = [
                    'id' => $note->comment_ID,
                    'author' => $note->comment_author,
                    'content' => $note->comment_content,
                    'date' => $note->comment_date,
                    'date_gmt' => $note->comment_date_gmt,
                    'is_customer_note' => $note->meta_key === 'is_customer_note' && $note->meta_value === '1',
                    'user_id' => $note->user_id
                ];
            }

            return $formattedNotes;

        } catch (\Exception $e) {
            Log::error("Failed to get formatted order notes for order {$orderId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if order can be cancelled
     *
     * @param int $orderId
     * @return bool
     */
    public function canCancelOrder(int $orderId): bool
    {
        $status = DB::connection('woocommerce')
            ->table('wp_posts')
            ->where('ID', $orderId)
            ->value('post_status');

        $cancellableStatuses = ['wc-pending', 'wc-processing', 'wc-on-hold'];
        return in_array($status, $cancellableStatuses);
    }

    /**
     * Check if order can be refunded
     *
     * @param int $orderId
     * @return bool
     */
    public function canRefundOrder(int $orderId): bool
    {
        $status = DB::connection('woocommerce')
            ->table('wp_posts')
            ->where('ID', $orderId)
            ->value('post_status');

        $refundableStatuses = ['wc-processing', 'wc-completed'];
        return in_array($status, $refundableStatuses);
    }

    /**
     * Get order status history
     *
     * @param int $orderId
     * @return array
     */
    public function getOrderStatusHistory(int $orderId): array
    {
        try {
            $notes = $this->notesService->getOrderNotes($orderId, false);
            
            $statusHistory = [];
            foreach ($notes as $note) {
                if (strpos($note->comment_content, 'Order status changed from') !== false) {
                    $statusHistory[] = [
                        'date' => $note->comment_date,
                        'note' => $note->comment_content,
                        'author' => $note->comment_author
                    ];
                }
            }

            return $statusHistory;

        } catch (\Exception $e) {
            Log::error("Failed to get order status history for order {$orderId}: " . $e->getMessage());
            return [];
        }
    }
} 