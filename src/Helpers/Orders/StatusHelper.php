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
            $statuses = self::getConnection()
                ->table('posts')
                ->select('post_name', 'post_title')
                ->where('post_type', 'wc_order_status')
                ->orderBy('menu_order', 'ASC')
                ->get();

            return $statuses->mapWithKeys(function ($status) {
                // Remove 'wc-' prefix from post_name if it exists
                $statusKey = str_replace('wc-', '', $status->post_name);
                return [$statusKey => $status->post_title];
            })->toArray();
        });
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