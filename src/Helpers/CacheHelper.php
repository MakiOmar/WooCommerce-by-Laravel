<?php

namespace Makiomar\WooOrderDashboard\Helpers;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;
use Illuminate\Support\Facades\Cache;

class CacheHelper extends BaseHelper
{
    /**
     * Clear cache by tags.
     * This method provides a public interface to the protected cache clearing functionality.
     *
     * @param string|array $tags
     * @return void
     */
    public function clearByTags($tags)
    {
        static::clearCacheByTags($tags);
    }

    /**
     * Get the database connection name.
     *
     * @return string
     */
    protected function getConnectionName()
    {
        return config('woo-order-dashboard.database.connection', 'woocommerce');
    }

    /**
     * Get table name with proper prefix handling.
     *
     * @param string $table
     * @return string
     */
    protected function getTableName($table)
    {
        $prefix = config('woo-order-dashboard.database.prefix', 'wp_');
        
        if (strpos($table, $prefix) === 0) {
            return $table;
        }
        
        return $prefix . $table;
    }

    /**
     * Clear all order-related cache
     */
    public static function clearOrderCache()
    {
        // Clear order list cache (all filter combinations)
        Cache::flush();
        
        // Alternative: Clear specific cache patterns if needed
        // $this->clearOrderListCache();
        // $this->clearOrderDetailCache();
    }

    /**
     * Clear specific order cache by ID
     */
    public static function clearOrderCacheById($orderId)
    {
        $cacheKey = 'woo_order_' . $orderId;
        Cache::forget($cacheKey);
    }

    /**
     * Clear order list cache (all filter combinations)
     */
    public static function clearOrderListCache()
    {
        // Since we use filter-based cache keys, we need to clear all
        // This is a simple approach - in production you might want to track cache keys
        Cache::flush();
    }

    /**
     * Clear status cache
     */
    public static function clearStatusCache()
    {
        Cache::forget('woo_order_statuses');
    }

    /**
     * Clear all WooCommerce dashboard cache
     */
    public static function clearAllWooCommerceCache()
    {
        Cache::flush();
    }

    /**
     * Clear cache when order status changes
     */
    public static function clearCacheOnOrderStatusChange($orderId)
    {
        // Clear specific order cache
        self::clearOrderCacheById($orderId);
        
        // Clear order list cache since status affects filtering
        self::clearOrderListCache();
        
        // Clear status cache if it includes counts
        self::clearStatusCache();
    }

    /**
     * Clear cache when order is updated
     */
    public static function clearCacheOnOrderUpdate($orderId)
    {
        // Clear specific order cache
        self::clearOrderCacheById($orderId);
        
        // Clear order list cache since any update might affect display
        self::clearOrderListCache();
    }

    /**
     * Clear cache when order is created
     */
    public static function clearCacheOnOrderCreate()
    {
        // Clear order list cache since new order affects pagination and counts
        self::clearOrderListCache();
        
        // Clear any statistics cache
        self::clearStatisticsCache();
    }

    /**
     * Clear cache when order is deleted
     */
    public static function clearCacheOnOrderDelete($orderIds = [])
    {
        // Clear specific order caches if IDs provided
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                self::clearOrderCacheById($orderId);
            }
        }
        
        // Clear order list cache since deletion affects pagination and counts
        self::clearOrderListCache();
        
        // Clear any statistics cache
        self::clearStatisticsCache();
    }

    /**
     * Clear statistics cache
     */
    public static function clearStatisticsCache()
    {
        // Clear any cached statistics
        Cache::forget('woo_order_stats');
        Cache::forget('woo_order_stats_day');
        Cache::forget('woo_order_stats_week');
        Cache::forget('woo_order_stats_month');
        Cache::forget('woo_order_stats_year');
    }
} 