<?php

namespace Makiomar\WooOrderDashboard\Helpers\Orders;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;

class StatusHelper extends BaseHelper
{
    /**
     * Get all WooCommerce order statuses
     *
     * @return array
     */
    public static function getAllStatuses()
    {
        return self::remember('woo_order_statuses', 3600, function () {
            // Get predefined default statuses from config
            $defaultStatuses = config('woo-order-dashboard.default_order_statuses', []);
            
            // Get dynamic statuses from database
            $dynamicStatuses = self::getConnection()
                ->table('posts')
                ->select('post_name', 'post_title')
                ->where('post_type', 'wc_order_status')
                ->orderBy('menu_order', 'ASC')
                ->get();

            $dbStatuses = $dynamicStatuses->mapWithKeys(function ($status) {
                // Remove 'wc-' prefix from post_name if it exists
                $statusKey = str_replace('wc-', '', $status->post_name);
                return [$statusKey => $status->post_title];
            })->toArray();

            // Merge default statuses with database statuses
            // Database statuses take precedence over default statuses
            $mergedStatuses = array_merge($defaultStatuses, $dbStatuses);

            // Sort by key for consistent ordering
            ksort($mergedStatuses);

            return $mergedStatuses;
        });
    }

    /**
     * Get all statuses with wc- prefix for database queries
     *
     * @return array
     */
    public static function getAllStatusesWithPrefix()
    {
        $statuses = self::getAllStatuses();
        $statusesWithPrefix = [];
        
        foreach ($statuses as $key => $label) {
            $statusesWithPrefix['wc-' . $key] = $label;
        }
        
        return $statusesWithPrefix;
    }

    /**
     * Get status key with wc- prefix
     *
     * @param string $statusKey
     * @return string
     */
    public static function getStatusWithPrefix($statusKey)
    {
        return 'wc-' . $statusKey;
    }

    /**
     * Remove wc- prefix from status key
     *
     * @param string $statusKey
     * @return string
     */
    public static function removeStatusPrefix($statusKey)
    {
        return str_replace('wc-', '', $statusKey);
    }

    /**
     * Get predefined default order statuses from config
     *
     * @return array
     */
    public static function getDefaultStatuses()
    {
        return config('woo-order-dashboard.default_order_statuses', []);
    }

    /**
     * Get order statuses from the database only
     *
     * @return array
     */
    public static function getDatabaseStatuses()
    {
        return self::remember('woo_order_statuses_db_only', 3600, function () {
            $dynamicStatuses = self::getConnection()
                ->table('posts')
                ->select('post_name', 'post_title')
                ->where('post_type', 'wc_order_status')
                ->orderBy('menu_order', 'ASC')
                ->get();

            return $dynamicStatuses->mapWithKeys(function ($status) {
                // Remove 'wc-' prefix from post_name if it exists
                $statusKey = str_replace('wc-', '', $status->post_name);
                return [$statusKey => $status->post_title];
            })->toArray();
        });
    }

    /**
     * Check if a status is a custom status (not in default statuses)
     *
     * @param string $statusKey
     * @return bool
     */
    public static function isCustomStatus($statusKey)
    {
        $defaultStatuses = self::getDefaultStatuses();
        return !array_key_exists($statusKey, $defaultStatuses);
    }

    /**
     * Get status label by key
     *
     * @param string $statusKey
     * @return string
     */
    public static function getStatusLabel($statusKey)
    {
        $statuses = self::getAllStatuses();
        return $statuses[$statusKey] ?? ucfirst($statusKey);
    }

    /**
     * Get all statuses with metadata (custom vs default)
     *
     * @return array
     */
    public static function getAllStatusesWithMetadata()
    {
        $allStatuses = self::getAllStatuses();
        $defaultStatuses = self::getDefaultStatuses();
        
        $statusesWithMetadata = [];
        
        foreach ($allStatuses as $key => $label) {
            $statusesWithMetadata[$key] = [
                'label' => $label,
                'is_custom' => !array_key_exists($key, $defaultStatuses),
                'is_default' => array_key_exists($key, $defaultStatuses),
                'color_class' => config('woo-order-dashboard.status_colors.' . $key, 'secondary'),
            ];
        }
        
        return $statusesWithMetadata;
    }

    /**
     * Check if a status exists
     *
     * @param string $statusKey
     * @return bool
     */
    public static function statusExists($statusKey)
    {
        $statuses = self::getAllStatuses();
        return array_key_exists($statusKey, $statuses);
    }
} 