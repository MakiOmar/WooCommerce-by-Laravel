<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class WooCommerceShippingService
{
    public function __construct() {}
    
    /**
     * Get available shipping methods for a destination
     */
    public function getShippingMethods($destination, $cartItems = [])
    {
        // Find matching shipping zone
        $zoneId = $this->findShippingZone($destination);
        \Log::info("zoneId", ['zoneId' => $zoneId]);
        if (!$zoneId) {
            return collect([]);
        }
        
        // Get shipping methods for the zone
        $methods = $this->getZoneMethods($zoneId);
        
        // Calculate rates for each method
        $rates = collect();
        foreach ($methods as $method) {
            $rate = $this->calculateMethodRate($method, $cartItems, $destination);
            if ($rate) {
                $rates->push($rate);
            }
        }
        
        return $rates;
    }
    
    /**
     * Find shipping zone for destination
     */
    protected function findShippingZone($destination)
    {
        $country = strtoupper($destination['country']);
        $state = strtoupper($destination['state']);
        $postcode = $this->normalizePostcode($destination['postcode']);
        
        // Get continent code from JSON file
        $continent = $this->getContinentFromCountry($country);
        \Log::info("continent", ['continent' => $continent]);
        // Build zone matching query
        $zoneId = DB::connection('woocommerce')->table('woocommerce_shipping_zones as zones')
            ->leftJoin('woocommerce_shipping_zone_locations as locations', function($join) {
                $join->on('zones.zone_id', '=', 'locations.zone_id')
                     ->where('location_type', '!=', 'postcode');
            })
            ->where(function($query) use ($country, $state, $continent) {
                $query->where(function($q) use ($country) {
                    $q->where('location_type', 'country')
                      ->where('location_code', $country);
                })
                ->orWhere(function($q) use ($country, $state) {
                    $q->where('location_type', 'state')
                      ->where('location_code', $country . ':' . $state);
                })
                ->orWhere(function($q) use ($continent) {
                    $q->where('location_type', 'continent')
                      ->where('location_code', $continent);
                })
                ->orWhereNull('location_type');
            })
            ->orderBy('zone_order')
            ->orderBy('zones.zone_id')
            ->value('zones.zone_id');
        
        return $zoneId;
    }
    
    /**
     * Get shipping methods for a zone
     */
    protected function getZoneMethods($zoneId)
    {
        return DB::connection('woocommerce')->table('woocommerce_shipping_zone_methods as m')
            ->join('options as s', function($join) {
                $join->whereRaw('s.option_name = CONCAT("woocommerce_", m.method_id, "_", m.instance_id, "_settings")');
            })
            ->where('m.zone_id', $zoneId)
            ->where('m.is_enabled', 1)
            ->orderBy('m.method_order')
            ->select([
                'm.instance_id',
                'm.method_id',
                'm.method_order',
                's.option_value as settings'
            ])
            ->get();
    }
    
    /**
     * Calculate shipping rate for a method
     */
    protected function calculateMethodRate($method, $cartItems, $destination)
    {
        $settings = unserialize($method->settings);
        
        switch ($method->method_id) {
            case 'flat_rate':
                return $this->calculateFlatRate($method, $settings, $cartItems);
            case 'free_shipping':
                return $this->calculateFreeShipping($method, $settings, $cartItems);
            case 'local_pickup':
                return $this->calculateLocalPickup($method, $settings);
            default:
                return null;
        }
    }
    
    /**
     * Calculate flat rate shipping
     */
    protected function calculateFlatRate($method, $settings, $cartItems)
    {
        $cost = 0;
        
        // Base cost
        if (!empty($settings['cost'])) {
            $cost += $this->evaluateCost($settings['cost'], $cartItems);
        }
        
        // Shipping class costs
        if (!empty($settings['class_costs'])) {
            $shippingClasses = $this->getCartShippingClasses($cartItems);
            
            foreach ($shippingClasses as $classId => $classData) {
                $classCostKey = 'class_cost_' . $classId;
                if (isset($settings[$classCostKey]) && $settings[$classCostKey] !== '') {
                    $classCost = $this->evaluateCost($settings[$classCostKey], $classData);
                    
                    if ($settings['type'] === 'class') {
                        $cost += $classCost;
                    } else { // order type
                        $cost = max($cost, $classCost);
                    }
                }
            }
        }
        
        return [
            'id' => $method->method_id . ':' . $method->instance_id,
            'method_id' => $method->method_id,
            'instance_id' => $method->instance_id,
            'title' => $settings['title'] ?? 'Flat Rate',
            'cost' => $cost,
            'tax_status' => $settings['tax_status'] ?? 'taxable'
        ];
    }
    
    /**
     * Calculate free shipping
     */
    protected function calculateFreeShipping($method, $settings, $cartItems)
    {
        $subtotal = collect($cartItems)->sum('line_total');
        $minAmount = floatval($settings['min_amount'] ?? 0);
        
        if ($subtotal >= $minAmount) {
            return [
                'id' => $method->method_id . ':' . $method->instance_id,
                'method_id' => $method->method_id,
                'instance_id' => $method->instance_id,
                'title' => $settings['title'] ?? 'Free Shipping',
                'cost' => 0,
                'tax_status' => 'none'
            ];
        }
        
        return null;
    }
    
    /**
     * Calculate local pickup
     */
    protected function calculateLocalPickup($method, $settings)
    {
        return [
            'id' => $method->method_id . ':' . $method->instance_id,
            'method_id' => $method->method_id,
            'instance_id' => $method->instance_id,
            'title' => $settings['title'] ?? 'Local Pickup',
            'cost' => floatval($settings['cost'] ?? 0),
            'tax_status' => $settings['tax_status'] ?? 'none'
        ];
    }
    
    /**
     * Evaluate cost expression
     */
    protected function evaluateCost($costString, $cartItems)
    {
        $totalQty = collect($cartItems)->sum('quantity');
        $totalCost = collect($cartItems)->sum('line_total');
        
        $costString = str_replace('[qty]', $totalQty, $costString);
        $costString = str_replace('[cost]', $totalCost, $costString);
        
        // Basic math evaluation (consider using a proper math library)
        return $this->safeEval($costString);
    }
    
    /**
     * Safe math evaluation
     */
    protected function safeEval($expression)
    {
        // Remove potentially dangerous characters
        $expression = preg_replace('/[^0-9+\-*/().]/', '', $expression);
        
        try {
            return eval("return $expression;");
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get shipping classes for cart items
     */
    protected function getCartShippingClasses($cartItems)
    {
        $classes = [];
        
        foreach ($cartItems as $item) {
            $classId = $item['shipping_class_id'] ?? 0;
            if (!isset($classes[$classId])) {
                $classes[$classId] = [
                    'items' => [],
                    'quantity' => 0,
                    'cost' => 0
                ];
            }
            $classes[$classId]['items'][] = $item;
            $classes[$classId]['quantity'] += $item['quantity'];
            $classes[$classId]['cost'] += $item['line_total'];
        }
        
        return $classes;
    }
    
    /**
     * Get continent code from country code using JSON data
     */
    protected function getContinentFromCountry($countryCode)
    {
        static $continentData = null;
        
        if ($continentData === null) {
            // Try package data file first
            $jsonPath = __DIR__ . '/../Data/continent-country.json';
            
            // Fallback to published file
            if (!file_exists($jsonPath)) {
                $jsonPath = storage_path('app/woo-order-dashboard/continent-country.json');
            }
            
            if (file_exists($jsonPath)) {
                $jsonContent = file_get_contents($jsonPath);
                $continentData = json_decode($jsonContent, true);
            } else {
                \Log::warning('Continent-country.json file not found. Tried: ' . __DIR__ . '/../Data/continent-country.json and ' . storage_path('app/woo-order-dashboard/continent-country.json'));
                $continentData = [];
            }
        }
        
        return $continentData[$countryCode] ?? null;
    }
    
    /**
     * Normalize postcode
     */
    protected function normalizePostcode($postcode)
    {
        return strtoupper(trim($postcode));
    }
} 