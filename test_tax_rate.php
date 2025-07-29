<?php

// Simple test to check tax rate creation
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Database configuration
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'port'      => '3306',
    'database'  => 'woocommerce', // Adjust if needed
    'username'  => 'root',        // Adjust if needed
    'password'  => '',            // Adjust if needed
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => 'wp_',
    'strict'    => true,
    'engine'    => null,
], 'woocommerce');

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "Checking WooCommerce tax configuration...\n";

try {
    // Check if taxes are enabled
    $taxEnabled = Capsule::connection('woocommerce')->table('options')
        ->where('option_name', 'woocommerce_calc_taxes')
        ->value('option_value');
    
    echo "Taxes enabled: " . ($taxEnabled ?? 'not set') . "\n";
    
    // Check for VAT tax rate
    $vatTaxRate = Capsule::connection('woocommerce')->table('woocommerce_tax_rates')
        ->where('tax_rate_name', 'VAT')
        ->where('tax_rate', '15.0000')
        ->first();
    
    if ($vatTaxRate) {
        echo "VAT tax rate found:\n";
        echo "- ID: " . $vatTaxRate->tax_rate_id . "\n";
        echo "- Name: " . $vatTaxRate->tax_rate_name . "\n";
        echo "- Rate: " . $vatTaxRate->tax_rate . "%\n";
        echo "- Shipping: " . $vatTaxRate->tax_rate_shipping . "\n";
    } else {
        echo "VAT tax rate not found\n";
    }
    
    // Check recent orders for tax data
    $recentOrder = Capsule::connection('woocommerce')->table('posts')
        ->where('post_type', 'shop_order')
        ->orderBy('ID', 'desc')
        ->first();
    
    if ($recentOrder) {
        echo "Recent order found: ID " . $recentOrder->ID . "\n";
        
        // Check order meta for tax settings
        $orderMeta = Capsule::connection('woocommerce')->table('postmeta')
            ->where('post_id', $recentOrder->ID)
            ->whereIn('meta_key', ['_prices_include_tax', '_tax_display_cart', '_order_tax', '_total_tax'])
            ->get();
        
        echo "Order tax meta:\n";
        foreach ($orderMeta as $meta) {
            echo "- " . $meta->meta_key . ": " . $meta->meta_value . "\n";
        }
        
        // Check order items for tax data
        $orderItems = Capsule::connection('woocommerce')->table('woocommerce_order_items')
            ->where('order_id', $recentOrder->ID)
            ->get();
        
        echo "Order items:\n";
        foreach ($orderItems as $item) {
            echo "- " . $item->order_item_name . " (" . $item->order_item_type . ")\n";
            
            $itemMeta = Capsule::connection('woocommerce')->table('woocommerce_order_itemmeta')
                ->where('order_item_id', $item->order_item_id)
                ->whereIn('meta_key', ['_line_tax', '_line_tax_data', 'total_tax'])
                ->get();
            
            foreach ($itemMeta as $meta) {
                echo "  " . $meta->meta_key . ": " . $meta->meta_value . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 