<?php

namespace Makiomar\WooOrderDashboard\Helpers\Gateways;

use Illuminate\Support\Facades\DB;
use Makiomar\WooOrderDashboard\Helpers\BaseHelper;

class PaymentGatewayHelper extends BaseHelper
{
    public function getEnabledPaymentGateways()
    {
        return static::remember('payment_gateways', static::CACHE_MEDIUM, function () {
            $tableName = $this->getTableName('options');
            
            $enabledGateways = DB::connection($this->getConnectionName())
                ->table($tableName)
                ->selectRaw("
                    REPLACE(option_name, 'woocommerce_', '') as gateway_id,
                    REPLACE(REPLACE(option_name, 'woocommerce_', ''), '_settings', '') as gateway_name,
                    option_value
                ")
                ->where('option_name', 'LIKE', 'woocommerce_%_settings')
                ->where('option_value', 'LIKE', '%"enabled";s:3:"yes"%')
                ->where('option_name', 'NOT LIKE', '%woocommerce_checkout_settings%')
                ->where('option_name', 'NOT LIKE', '%woocommerce_cart_settings%')
                ->orderBy('option_name')
                ->get();

            $gateways = [];
            foreach ($enabledGateways as $gateway) {
                $settings = unserialize($gateway->option_value);
                $gateways[$gateway->gateway_id] = [
                    'title' => $settings['title'] ?? ucfirst(str_replace('_', ' ', $gateway->gateway_name)),
                    'description' => $settings['description'] ?? '',
                    'gateway_name' => $gateway->gateway_name,
                ];
            }

            return $gateways;
        });
    }

    /**
     * Get the database connection name
     *
     * @return string
     */
    protected function getConnectionName()
    {
        return config('woo-order-dashboard.database.connection', 'woocommerce');
    }

    /**
     * Get table name with proper prefix handling
     *
     * @param string $table
     * @return string
     */
    protected function getTableName($table)
    {
        $prefix = config('woo-order-dashboard.database.prefix', 'wp_');
        
        // Check if the table already has the prefix
        if (strpos($table, $prefix) === 0) {
            return $table;
        }
        
        return $prefix . $table;
    }
} 