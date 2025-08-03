<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WooCommerceOrderNotesService
{
    /**
     * Add an order note
     *
     * @param int $orderId
     * @param string $note
     * @param bool $isCustomerNote
     * @param string $addedBy
     * @param bool $notifyCustomer
     * @return int|null
     */
    public function addOrderNote(
        int $orderId, 
        string $note, 
        bool $isCustomerNote = false, 
        string $addedBy = 'system',
        bool $notifyCustomer = false
    ): ?int {
        try {
            $timestamps = $this->getProperTimestamps();

            $noteId = DB::connection('woocommerce')
                ->table('wp_comments')
                ->insertGetId([
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
            DB::connection('woocommerce')
                ->table('wp_commentmeta')
                ->insert([
                    'comment_id' => $noteId,
                    'meta_key' => 'is_customer_note',
                    'meta_value' => $isCustomerNote ? '1' : '0'
                ]);

            // Add notification meta if specified
            if ($notifyCustomer && $isCustomerNote) {
                DB::connection('woocommerce')
                    ->table('wp_commentmeta')
                    ->insert([
                        'comment_id' => $noteId,
                        'meta_key' => 'is_customer_notified',
                        'meta_value' => '1'
                    ]);
            }

            // Update comment count
            DB::connection('woocommerce')
                ->table('wp_posts')
                ->where('ID', $orderId)
                ->increment('comment_count');

            Log::info("Order note added for order {$orderId}: {$note}");
            return $noteId;

        } catch (\Exception $e) {
            Log::error("Failed to add order note for order {$orderId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get proper timestamps with timezone handling
     *
     * @return array
     */
    private function getProperTimestamps(): array
    {
        // Get site timezone from WordPress options
        $timezone = DB::connection('woocommerce')
            ->table('wp_options')
            ->where('option_name', 'timezone_string')
            ->value('option_value');

        // If timezone_string is empty, check gmt_offset
        if (empty($timezone)) {
            $gmtOffset = DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'gmt_offset')
                ->value('option_value');
            $timezone = $gmtOffset >= 0 ? "+{$gmtOffset}" : $gmtOffset;
        }

        // Default to UTC if no timezone found
        if (empty($timezone)) {
            $timezone = 'UTC';
        }

        $now = Carbon::now($timezone);

        return [
            'local' => $now->format('Y-m-d H:i:s'),
            'gmt' => $now->utc()->format('Y-m-d H:i:s'),
            'timestamp' => $now->timestamp
        ];
    }

    /**
     * Get order notes
     *
     * @param int $orderId
     * @param bool $includeCustomerNotes
     * @return \Illuminate\Support\Collection
     */
    public function getOrderNotes(int $orderId, bool $includeCustomerNotes = true): \Illuminate\Support\Collection
    {
        $query = DB::connection('woocommerce')
            ->table('wp_comments as c')
            ->leftJoin('wp_commentmeta as cm', 'c.comment_ID', '=', 'cm.comment_id')
            ->where('c.comment_post_ID', $orderId)
            ->where('c.comment_type', 'order_note')
            ->orderBy('c.comment_date', 'desc');

        if (!$includeCustomerNotes) {
            $query->where(function($q) {
                $q->where('cm.meta_key', '!=', 'is_customer_note')
                  ->orWhere('cm.meta_value', '!=', '1')
                  ->orWhereNull('cm.meta_key');
            });
        }

        return $query->get([
            'c.comment_ID',
            'c.comment_author',
            'c.comment_date',
            'c.comment_date_gmt',
            'c.comment_content',
            'c.user_id',
            'cm.meta_key',
            'cm.meta_value'
        ]);
    }

    /**
     * Add status change note
     *
     * @param int $orderId
     * @param string $oldStatus
     * @param string $newStatus
     * @return int|null
     */
    public function addStatusChangeNote(int $orderId, string $oldStatus, string $newStatus): ?int
    {
        $message = $this->getStatusChangeMessage($oldStatus, $newStatus);
        return $this->addOrderNote($orderId, $message, false);
    }

    /**
     * Add payment note
     *
     * @param int $orderId
     * @param string $transactionId
     * @param float $amount
     * @param string $paymentMethod
     * @return int|null
     */
    public function addPaymentNote(int $orderId, string $transactionId, float $amount, string $paymentMethod): ?int
    {
        $note = "Payment of $" . number_format($amount, 2) . " received via {$paymentMethod}. Transaction ID: {$transactionId}";
        return $this->addOrderNote($orderId, $note, false);
    }

    /**
     * Add refund note
     *
     * @param int $orderId
     * @param float $amount
     * @param string $reason
     * @param string $refundId
     * @return int|null
     */
    public function addRefundNote(int $orderId, float $amount, string $reason = '', string $refundId = ''): ?int
    {
        $note = "Refunded $" . number_format($amount, 2);

        if (!empty($reason)) {
            $note .= " - Reason: {$reason}";
        }

        if (!empty($refundId)) {
            $note .= " (Refund ID: {$refundId})";
        }

        return $this->addOrderNote($orderId, $note, true);
    }

    /**
     * Add shipping note
     *
     * @param int $orderId
     * @param string $trackingNumber
     * @param string $carrier
     * @return int|null
     */
    public function addShippingNote(int $orderId, string $trackingNumber, string $carrier = ''): ?int
    {
        $note = "Tracking number: {$trackingNumber}";

        if (!empty($carrier)) {
            $note = "Order shipped via {$carrier}. " . $note;
        }

        return $this->addOrderNote($orderId, $note, true);
    }

    /**
     * Add stock change note
     *
     * @param int $orderId
     * @param string $action
     * @return int|null
     */
    public function addStockChangeNote(int $orderId, string $action): ?int
    {
        $note = $action === 'reduced' ? 'Stock levels reduced.' : 'Stock levels restored.';
        return $this->addOrderNote($orderId, $note, false);
    }

    /**
     * Get status change message
     *
     * @param string $oldStatus
     * @param string $newStatus
     * @return string
     */
    private function getStatusChangeMessage(string $oldStatus, string $newStatus): string
    {
        $statusLabels = [
            'pending' => 'Pending payment',
            'processing' => 'Processing',
            'on-hold' => 'On hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'failed' => 'Failed'
        ];

        $oldLabel = $statusLabels[$oldStatus] ?? $oldStatus;
        $newLabel = $statusLabels[$newStatus] ?? $newStatus;

        return "Order status changed from {$oldLabel} to {$newLabel}.";
    }

    /**
     * Delete order note
     *
     * @param int $noteId
     * @return bool
     */
    public function deleteOrderNote(int $noteId): bool
    {
        try {
            // Get order ID before deleting
            $orderId = DB::connection('woocommerce')
                ->table('wp_comments')
                ->where('comment_ID', $noteId)
                ->value('comment_post_ID');

            // Delete comment meta
            DB::connection('woocommerce')
                ->table('wp_commentmeta')
                ->where('comment_id', $noteId)
                ->delete();

            // Delete comment
            DB::connection('woocommerce')
                ->table('wp_comments')
                ->where('comment_ID', $noteId)
                ->delete();

            // Update comment count
            if ($orderId) {
                DB::connection('woocommerce')
                    ->table('wp_posts')
                    ->where('ID', $orderId)
                    ->decrement('comment_count');
            }

            Log::info("Order note {$noteId} deleted");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to delete order note {$noteId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update order note
     *
     * @param int $noteId
     * @param string $note
     * @param bool $isCustomerNote
     * @return bool
     */
    public function updateOrderNote(int $noteId, string $note, bool $isCustomerNote = false): bool
    {
        try {
            // Update comment content
            DB::connection('woocommerce')
                ->table('wp_comments')
                ->where('comment_ID', $noteId)
                ->update([
                    'comment_content' => $note
                ]);

            // Update customer note meta
            DB::connection('woocommerce')
                ->table('wp_commentmeta')
                ->where('comment_id', $noteId)
                ->where('meta_key', 'is_customer_note')
                ->update([
                    'meta_value' => $isCustomerNote ? '1' : '0'
                ]);

            Log::info("Order note {$noteId} updated");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to update order note {$noteId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get note count for an order
     *
     * @param int $orderId
     * @param bool $includeCustomerNotes
     * @return int
     */
    public function getOrderNoteCount(int $orderId, bool $includeCustomerNotes = true): int
    {
        $query = DB::connection('woocommerce')
            ->table('wp_comments as c')
            ->leftJoin('wp_commentmeta as cm', 'c.comment_ID', '=', 'cm.comment_id')
            ->where('c.comment_post_ID', $orderId)
            ->where('c.comment_type', 'order_note');

        if (!$includeCustomerNotes) {
            $query->where(function($q) {
                $q->where('cm.meta_key', '!=', 'is_customer_note')
                  ->orWhere('cm.meta_value', '!=', '1')
                  ->orWhereNull('cm.meta_key');
            });
        }

        return $query->count();
    }
} 