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
            ->table('woocommerce_shipping_zones as zones')
            ->join('woocommerce_shipping_zone_methods as methods', 'zones.zone_id', '=', 'methods.zone_id')
            ->leftJoin('options as options', \DB::raw("options.option_name"), '=', \DB::raw("CONCAT('woocommerce_', methods.method_id, '_', methods.instance_id, '_settings')"))
            ->select([
                'zones.zone_id',
                'zones.zone_name',
                'methods.instance_id',
                'methods.method_id',
                'methods.method_order',
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
                'method_title' => $row->method_title,
                'method_cost' => $row->method_cost,
            ];
        }
        return $methods;
    }
} 