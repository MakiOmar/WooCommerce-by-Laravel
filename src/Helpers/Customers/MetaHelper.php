<?php

namespace Makiomar\WooOrderDashboard\Helpers\Customers;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;

class MetaHelper extends BaseHelper
{
    /**
     * Get all meta data for a customer
     *
     * @param int $customerId
     * @return array
     */
    public static function getAllMeta($customerId)
    {
        return self::remember("woo_customer_meta_{$customerId}", 3600, function () use ($customerId) {
            $meta = self::getConnection()
                ->table(self::getPrefix() . 'usermeta')
                ->where('user_id', $customerId)
                ->get()
                ->pluck('meta_value', 'meta_key')
                ->toArray();

            return $meta;
        });
    }

    /**
     * Get customer's billing information
     *
     * @param int $customerId
     * @return array
     */
    public static function getBillingInfo($customerId)
    {
        return self::remember("woo_customer_billing_{$customerId}", 3600, function () use ($customerId) {
            $billingFields = [
                'billing_first_name',
                'billing_last_name',
                'billing_company',
                'billing_address_1',
                'billing_address_2',
                'billing_city',
                'billing_state',
                'billing_postcode',
                'billing_country',
                'billing_email',
                'billing_phone'
            ];

            $meta = self::getConnection()
                ->table(self::getPrefix() . 'usermeta')
                ->where('user_id', $customerId)
                ->whereIn('meta_key', $billingFields)
                ->get()
                ->pluck('meta_value', 'meta_key')
                ->toArray();

            return [
                'first_name' => $meta['billing_first_name'] ?? '',
                'last_name' => $meta['billing_last_name'] ?? '',
                'company' => $meta['billing_company'] ?? '',
                'address_1' => $meta['billing_address_1'] ?? '',
                'address_2' => $meta['billing_address_2'] ?? '',
                'city' => $meta['billing_city'] ?? '',
                'state' => $meta['billing_state'] ?? '',
                'postcode' => $meta['billing_postcode'] ?? '',
                'country' => $meta['billing_country'] ?? '',
                'email' => $meta['billing_email'] ?? '',
                'phone' => $meta['billing_phone'] ?? ''
            ];
        });
    }

    /**
     * Get customer's shipping information
     *
     * @param int $customerId
     * @return array
     */
    public static function getShippingInfo($customerId)
    {
        return self::remember("woo_customer_shipping_{$customerId}", 3600, function () use ($customerId) {
            $shippingFields = [
                'shipping_first_name',
                'shipping_last_name',
                'shipping_company',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_state',
                'shipping_postcode',
                'shipping_country'
            ];

            $meta = self::getConnection()
                ->table(self::getPrefix() . 'usermeta')
                ->where('user_id', $customerId)
                ->whereIn('meta_key', $shippingFields)
                ->get()
                ->pluck('meta_value', 'meta_key')
                ->toArray();

            return [
                'first_name' => $meta['shipping_first_name'] ?? '',
                'last_name' => $meta['shipping_last_name'] ?? '',
                'company' => $meta['shipping_company'] ?? '',
                'address_1' => $meta['shipping_address_1'] ?? '',
                'address_2' => $meta['shipping_address_2'] ?? '',
                'city' => $meta['shipping_city'] ?? '',
                'state' => $meta['shipping_state'] ?? '',
                'postcode' => $meta['shipping_postcode'] ?? '',
                'country' => $meta['shipping_country'] ?? ''
            ];
        });
    }

    /**
     * Get customer's last order date
     *
     * @param int $customerId
     * @return string|null
     */
    public static function getLastOrderDate($customerId)
    {
        return self::remember("woo_customer_last_order_{$customerId}", 1800, function () use ($customerId) {
            $lastOrder = self::getConnection()
                ->table(self::getPrefix() . 'posts as p')
                ->join(self::getPrefix() . 'postmeta as pm', 'p.ID', '=', 'pm.post_id')
                ->where('p.post_type', 'shop_order')
                ->where('pm.meta_key', '_customer_user')
                ->where('pm.meta_value', $customerId)
                ->orderBy('p.post_date', 'DESC')
                ->select('p.post_date')
                ->first();

            return $lastOrder ? $lastOrder->post_date : null;
        });
    }

    /**
     * Get customer's order count and total spent
     *
     * @param int $customerId
     * @return array
     */
    public static function getOrderStats($customerId)
    {
        return self::remember("woo_customer_order_stats_{$customerId}", 3600, function () use ($customerId) {
            $stats = self::getConnection()
                ->table(self::getPrefix() . 'posts as p')
                ->join(self::getPrefix() . 'postmeta as pm_user', function($join) use ($customerId) {
                    $join->on('p.ID', '=', 'pm_user.post_id')
                         ->where('pm_user.meta_key', '_customer_user')
                         ->where('pm_user.meta_value', $customerId);
                })
                ->join(self::getPrefix() . 'postmeta as pm_total', function($join) {
                    $join->on('p.ID', '=', 'pm_total.post_id')
                         ->where('pm_total.meta_key', '_order_total');
                })
                ->where('p.post_type', 'shop_order')
                ->where('p.post_status', 'wc-completed')
                ->selectRaw('COUNT(*) as order_count, SUM(CAST(pm_total.meta_value AS DECIMAL(10,2))) as total_spent')
                ->first();

            return [
                'order_count' => (int) $stats->order_count,
                'total_spent' => (float) $stats->total_spent
            ];
        });
    }
} 