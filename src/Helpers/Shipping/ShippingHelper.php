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
            ->leftJoin('woocommerce_shipping_zone_methodmeta as methodmeta', 'methods.zone_method_id', '=', 'methodmeta.zone_method_id')
            ->select([
                'zones.zone_id',
                'zones.zone_name',
                'methods.zone_method_id',
                'methods.method_id',
                'methods.method_order',
                'methods.is_enabled',
                'methodmeta.meta_key',
                'methodmeta.meta_value',
            ])
            ->orderBy('zones.zone_id')
            ->orderBy('methods.method_order')
            ->get();

        $zones = [];
        foreach ($rows as $row) {
            if (!isset($zones[$row->zone_id])) {
                $zones[$row->zone_id] = [
                    'zone_id' => $row->zone_id,
                    'zone_name' => $row->zone_name,
                    'methods' => [],
                ];
            }
            $methodKey = $row->zone_method_id;
            if (!isset($zones[$row->zone_id]['methods'][$methodKey])) {
                $zones[$row->zone_id]['methods'][$methodKey] = [
                    'zone_method_id' => $row->zone_method_id,
                    'method_id' => $row->method_id,
                    'method_order' => $row->method_order,
                    'is_enabled' => $row->is_enabled,
                    'meta' => [],
                ];
            }
            if ($row->meta_key !== null) {
                $zones[$row->zone_id]['methods'][$methodKey]['meta'][$row->meta_key] = $row->meta_value;
            }
        }
        // Re-index methods as array
        foreach ($zones as &$zone) {
            $zone['methods'] = array_values($zone['methods']);
        }
        return array_values($zones);
    }
} 