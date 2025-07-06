<?php

namespace Makiomar\WooOrderDashboard\Helpers\Shipping;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;

class ShippingHelper extends BaseHelper
{
    /**
     * Get all shipping zones with their methods and meta
     *
     * @return array
     */
    public static function getAllShippingMethods()
    {
        $rows = self::getConnection()
            ->table('woocommerce_shipping_zones')
            ->join('woocommerce_shipping_zone_methods', 'woocommerce_shipping_zones.zone_id', '=', 'woocommerce_shipping_zone_methods.zone_id')
            ->leftJoin('options', \DB::raw("options.option_name"), '=', \DB::raw("CONCAT('woocommerce_', woocommerce_shipping_zone_methods.method_id, '_', woocommerce_shipping_zone_methods.instance_id, '_settings')"))
            ->select([
                'woocommerce_shipping_zones.zone_id',
                'woocommerce_shipping_zones.zone_name',
                'woocommerce_shipping_zone_methods.instance_id',
                'woocommerce_shipping_zone_methods.method_id',
                'woocommerce_shipping_zone_methods.method_order',
                'woocommerce_shipping_zone_methods.is_enabled',
                \DB::raw("CASE woocommerce_shipping_zone_methods.is_enabled WHEN 1 THEN 'مفعلة' ELSE 'غير مفعلة' END AS method_status"),
                \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(options.option_value, '$.title')) AS method_title"),
                \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(options.option_value, '$.cost')) AS method_cost"),
            ])
            ->orderBy('woocommerce_shipping_zones.zone_id')
            ->orderBy('woocommerce_shipping_zone_methods.method_order')
            ->get();

        $methods = [];
        foreach ($rows as $row) {
            $methods[] = [
                'zone_id' => $row->zone_id,
                'zone_name' => $row->zone_name,
                'instance_id' => $row->instance_id,
                'method_id' => $row->method_id,
                'method_order' => $row->method_order,
                'is_enabled' => $row->is_enabled,
                'method_status' => $row->method_status,
                'method_title' => $row->method_title,
                'method_cost' => $row->method_cost,
            ];
        }
        return $methods;
    }
} 