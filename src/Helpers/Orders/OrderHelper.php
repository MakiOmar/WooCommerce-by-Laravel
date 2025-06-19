<?php

namespace Makiomar\WooOrderDashboard\Helpers\Orders;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OrderHelper extends BaseHelper
{
    /**
     * Cache tags for order-related data
     */
    private const CACHE_TAG_ORDERS = 'woo_orders';
    private const CACHE_TAG_ORDER_ITEMS = 'woo_order_items';
    private const CACHE_TAG_ORDER_META = 'woo_order_meta';

    /**
     * Get orders with optimized query and caching
     *
     * @param array $filters
     * @return Collection
     */
    public static function getOrders(array $filters = [])
    {
        $cacheKey = 'orders_' . md5(serialize($filters));
        
        return static::rememberWithTags([self::CACHE_TAG_ORDERS], $cacheKey, self::CACHE_MEDIUM, function () use ($filters) {
            $query = static::getConnection()
                ->table(static::getTableName('posts'))
                ->where('post_type', 'shop_order');
            
            // Apply filters using WooCommerce's existing indexes
            if (!empty($filters['status'])) {
                if (is_array($filters['status']) && count($filters['status']) > 50) {
                    static::whereInChunked($query, 'post_status', $filters['status']);
                } else {
                    $query->whereIn('post_status', (array) $filters['status']);
                }
            }
            
            if (!empty($filters['date_from'])) {
                $query->where('post_date', '>=', $filters['date_from']);
            }
            
            if (!empty($filters['date_to'])) {
                $query->where('post_date', '<=', $filters['date_to']);
            }

            return static::optimizeQuery($query)->get();
        });
    }

    /**
     * Get order items with optimized performance
     *
     * @param int $orderId
     * @return Collection
     */
    public static function getOrderItems($orderId)
    {
        $cacheKey = "order_items_{$orderId}";
        
        return static::rememberWithTags(
            [self::CACHE_TAG_ORDERS, self::CACHE_TAG_ORDER_ITEMS], 
            $cacheKey, 
            self::CACHE_MEDIUM,
            function () use ($orderId) {
                $query = static::getConnection()
                    ->table(static::getTableName('woocommerce_order_items'))
                    ->where('order_id', $orderId);
                
                return static::optimizeQuery($query)->get();
            }
        );
    }

    /**
     * Process bulk order updates efficiently
     *
     * @param array $orderIds
     * @param array $data
     * @return void
     */
    public static function bulkUpdateOrders(array $orderIds, array $data)
    {
        static::executeWithRetry(function () use ($orderIds, $data) {
            static::processInChunks(
                static::getConnection()
                    ->table(static::getTableName('posts'))
                    ->whereIn('ID', $orderIds),
                function ($chunk) use ($data) {
                    foreach ($chunk as $order) {
                        static::getConnection()
                            ->table(static::getTableName('posts'))
                            ->where('ID', $order->ID)
                            ->update($data);
                    }
                }
            );
        });

        // Clear relevant caches
        static::clearCacheByTags([self::CACHE_TAG_ORDERS]);
    }

    /**
     * Get order meta with performance optimization
     *
     * @param int $orderId
     * @param string|null $key
     * @return Collection
     */
    public static function getOrderMeta($orderId, $key = null)
    {
        $cacheKey = "order_meta_{$orderId}" . ($key ? "_{$key}" : '');
        
        return static::rememberWithTags(
            [self::CACHE_TAG_ORDERS, self::CACHE_TAG_ORDER_META],
            $cacheKey,
            self::CACHE_SHORT,
            function () use ($orderId, $key) {
                $query = static::getConnection()
                    ->table(static::getTableName('postmeta'))
                    ->where('post_id', $orderId);

                if ($key) {
                    $query->where('meta_key', $key);
                }

                return static::optimizeQuery($query)->get();
            }
        );
    }

    /**
     * Get order statistics with optimized performance
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public static function getOrderStats($dateFrom, $dateTo)
    {
        $cacheKey = "order_stats_{$dateFrom}_{$dateTo}";
        
        return static::rememberWithTags(self::CACHE_TAG_ORDERS, $cacheKey, self::CACHE_MEDIUM, function () use ($dateFrom, $dateTo) {
            $query = static::getConnection()
                ->table(static::getTableName('posts'))
                ->where('post_type', 'shop_order')
                ->whereBetween('post_date', [$dateFrom, $dateTo]);

            $optimizedQuery = static::optimizeQuery($query);

            return [
                'total_orders' => $optimizedQuery->count(),
                'status_counts' => $optimizedQuery->groupBy('post_status')
                    ->selectRaw('post_status, COUNT(*) as count')
                    ->get()
                    ->pluck('count', 'post_status')
                    ->toArray()
            ];
        });
    }

    /**
     * Analyze query performance
     *
     * @param array $filters
     * @return array
     */
    public static function analyzeQueryPerformance(array $filters = [])
    {
        $query = static::getConnection()
            ->table(static::getTableName('posts'))
            ->where('post_type', 'shop_order');

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                $query->where($key, $value);
            }
        }

        return static::explainQuery($query);
    }

    /**
     * Get order notes
     *
     * @param int $orderId
     * @return array
     */
    public static function getOrderNotes($orderId)
    {
        return self::remember("woo_order_notes_{$orderId}", 3600, function () use ($orderId) {
            $notes = self::getConnection()
                ->table(self::getTableName('comments'))
                ->where('comment_post_ID', $orderId)
                ->where('comment_type', 'order_note')
                ->orderBy('comment_date', 'DESC')
                ->get();

            return $notes->map(function ($note) {
                return [
                    'id' => $note->comment_ID,
                    'content' => $note->comment_content,
                    'date' => $note->comment_date,
                    'is_customer_note' => strpos($note->comment_content, '[Customer]') !== false,
                    'added_by' => $note->comment_author
                ];
            })->toArray();
        });
    }

    /**
     * Get order statistics for a given period
     *
     * @param string $period 'day', 'week', 'month', 'year'
     * @param string|null $status Filter by status
     * @return array
     */
    public static function getOrderStatsForPeriod($period = 'day', $status = null)
    {
        $key = "woo_order_stats_{$period}" . ($status ? "_{$status}" : '');
        
        return self::remember($key, 3600, function () use ($period, $status) {
            $query = self::getConnection()
                ->table(self::getTableName('posts') . ' as p')
                ->leftJoin(self::getTableName('postmeta') . ' as pm', function($join) {
                    $join->on('p.ID', '=', 'pm.post_id')
                         ->where('pm.meta_key', '_order_total');
                })
                ->where('p.post_type', 'shop_order');

            // Add status filter if provided
            if ($status) {
                $query->where('p.post_status', 'wc-' . $status);
            }

            // Add date range based on period
            $startDate = match($period) {
                'day' => Carbon::today(),
                'week' => Carbon::now()->startOfWeek(),
                'month' => Carbon::now()->startOfMonth(),
                'year' => Carbon::now()->startOfYear(),
                default => Carbon::today()
            };

            $query->where('p.post_date', '>=', $startDate);

            $results = $query->selectRaw('
                COUNT(*) as total_orders,
                SUM(CAST(pm.meta_value AS DECIMAL(10,2))) as total_revenue,
                MIN(CAST(pm.meta_value AS DECIMAL(10,2))) as min_order_value,
                MAX(CAST(pm.meta_value AS DECIMAL(10,2))) as max_order_value,
                AVG(CAST(pm.meta_value AS DECIMAL(10,2))) as avg_order_value
            ')->first();

            return [
                'period' => $period,
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'total_orders' => (int) $results->total_orders,
                'total_revenue' => (float) $results->total_revenue,
                'min_order_value' => (float) $results->min_order_value,
                'max_order_value' => (float) $results->max_order_value,
                'avg_order_value' => (float) $results->avg_order_value
            ];
        });
    }

    /**
     * Get recent orders
     *
     * @param int $limit
     * @param string|null $status
     * @return array
     */
    public static function getRecentOrders($limit = 10, $status = null)
    {
        $key = "woo_recent_orders_" . ($status ? "{$status}_" : '') . $limit;
        
        return self::remember($key, 300, function () use ($limit, $status) {
            $query = self::getConnection()
                ->table(self::getTableName('posts') . ' as p')
                ->leftJoin(self::getTableName('postmeta') . ' as total', function($join) {
                    $join->on('p.ID', '=', 'total.post_id')
                         ->where('total.meta_key', '_order_total');
                })
                ->leftJoin(self::getTableName('postmeta') . ' as billing_email', function($join) {
                    $join->on('p.ID', '=', 'billing_email.post_id')
                         ->where('billing_email.meta_key', '_billing_email');
                })
                ->where('p.post_type', 'shop_order');

            if ($status) {
                $query->where('p.post_status', 'wc-' . $status);
            }

            $orders = $query->select([
                'p.ID as id',
                'p.post_date as date',
                'p.post_status as status',
                'total.meta_value as total',
                'billing_email.meta_value as email'
            ])
            ->orderBy('p.post_date', 'DESC')
            ->limit($limit)
            ->get();

            return $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'date' => $order->date,
                    'status' => str_replace('wc-', '', $order->status),
                    'total' => (float) $order->total,
                    'email' => $order->email
                ];
            })->toArray();
        });
    }
} 