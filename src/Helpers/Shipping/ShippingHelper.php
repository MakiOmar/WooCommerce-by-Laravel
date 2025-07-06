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
            ->table('wpb3_woocommerce_shipping_zones as zones')
            ->join('wpb3_woocommerce_shipping_zone_methods as methods', 'zones.zone_id', '=', 'methods.zone_id')
            ->leftJoin('wpb3_options as options', \DB::raw("options.option_name"), '=', \DB::raw("CONCAT('woocommerce_', methods.method_id, '_', methods.instance_id, '_settings')"))
            ->select([
                'zones.zone_id',
                'zones.zone_name',
                'methods.instance_id',
                'methods.method_id',
                'methods.method_order',
                \DB::raw("CASE methods.is_enabled WHEN 1 THEN 'مفعلة' ELSE 'غير مفعلة' END AS method_status"),
                \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(options.option_value, '$.title')) AS method_title"),
                \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(options.option_value, '$.cost')) AS method_cost"),
            ])
            ->orderBy('zones.zone_id')
            ->orderBy('methods.method_order')
            ->get();

        $methods = [];
        foreach ($rows as $row) {
            $methods[] = [
                'zone_id' => $row->zone_id,
                'zone_name' => $row->zone_name,
                'instance_id' => $row->instance_id,
                'method_id' => $row->method_id,
                'method_order' => $row->method_order,
                'method_status' => $row->method_status,
                'method_title' => $row->method_title,
                'method_cost' => $row->method_cost,
            ];
        }
        return $methods;
    }
} 