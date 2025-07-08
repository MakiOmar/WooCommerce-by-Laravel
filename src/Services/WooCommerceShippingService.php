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
        $state = $this->normalizeState($destination['state']);
        $postcode = $this->normalizePostcode($destination['postcode']);
        
        \Log::info("Normalized destination", [
            'original_state' => $destination['state'],
            'normalized_state' => $state,
            'country' => $country,
            'postcode' => $postcode
        ]);
        
        // Get continent code from JSON file
        $continent = $this->getContinentFromCountry($country);
        \Log::info("continent", ['continent' => $continent]);
        
        // Build zone matching query - try exact state match first, then country, then continent
        $zoneId = null;
        
        // First try exact state match
        if ($state) {
            $stateCode = $country . ':' . $state;
            
            $zoneId = DB::connection('woocommerce')->table('woocommerce_shipping_zones as zones')
                ->join('woocommerce_shipping_zone_locations as locations', 'zones.zone_id', '=', 'locations.zone_id')
                ->where('locations.location_type', 'state')
                ->where('locations.location_code', $stateCode)
                ->orderBy('zones.zone_order')
                ->orderBy('zones.zone_id')
                ->value('zones.zone_id');
            
            \Log::info("State match attempt", ['state' => $stateCode, 'zoneId' => $zoneId]);
            
            // If no exact match, try case-insensitive match
            if (!$zoneId) {
                $zoneId = DB::connection('woocommerce')->table('woocommerce_shipping_zones as zones')
                    ->join('woocommerce_shipping_zone_locations as locations', 'zones.zone_id', '=', 'locations.zone_id')
                    ->where('locations.location_type', 'state')
                    ->whereRaw('UPPER(locations.location_code) = ?', [strtoupper($stateCode)])
                    ->orderBy('zones.zone_order')
                    ->orderBy('zones.zone_id')
                    ->value('zones.zone_id');
                
                \Log::info("Case-insensitive state match attempt", ['state' => strtoupper($stateCode), 'zoneId' => $zoneId]);
            }
        }
        
        // If no state match, try country match
        if (!$zoneId) {
            $zoneId = DB::connection('woocommerce')->table('woocommerce_shipping_zones as zones')
                ->join('woocommerce_shipping_zone_locations as locations', 'zones.zone_id', '=', 'locations.zone_id')
                ->where('locations.location_type', 'country')
                ->where('locations.location_code', $country)
                ->orderBy('zones.zone_order')
                ->orderBy('zones.zone_id')
                ->value('zones.zone_id');
            
            \Log::info("Country match attempt", ['country' => $country, 'zoneId' => $zoneId]);
        }
        
        // If no country match, try continent match
        if (!$zoneId && $continent) {
            $zoneId = DB::connection('woocommerce')->table('woocommerce_shipping_zones as zones')
                ->join('woocommerce_shipping_zone_locations as locations', 'zones.zone_id', '=', 'locations.zone_id')
                ->where('locations.location_type', 'continent')
                ->where('locations.location_code', $continent)
                ->orderBy('zones.zone_order')
                ->orderBy('zones.zone_id')
                ->value('zones.zone_id');
            
            \Log::info("Continent match attempt", ['continent' => $continent, 'zoneId' => $zoneId]);
        }
        
        // If still no match, try zones with no specific locations (rest of world)
        if (!$zoneId) {
            $zoneId = DB::connection('woocommerce')->table('woocommerce_shipping_zones as zones')
                ->leftJoin('woocommerce_shipping_zone_locations as locations', 'zones.zone_id', '=', 'locations.zone_id')
                ->whereNull('locations.zone_id')
                ->orderBy('zones.zone_order')
                ->orderBy('zones.zone_id')
                ->value('zones.zone_id');
            
            \Log::info("Rest of world match attempt", ['zoneId' => $zoneId]);
        }
        
        \Log::info("Found zone ID", ['zoneId' => $zoneId, 'country' => $country, 'state' => $state]);
        
        return $zoneId;
    }
    
    /**
     * Get shipping methods for a zone
     */
    protected function getZoneMethods($zoneId)
    {
        // First get the basic method information
        $methods = DB::connection('woocommerce')->table('woocommerce_shipping_zone_methods as m')
            ->where('m.zone_id', $zoneId)
            ->where('m.is_enabled', 1)
            ->orderBy('m.method_order')
            ->select([
                'm.instance_id',
                'm.method_id',
                'm.method_order'
            ])
            ->get();
        
        \Log::info("Basic methods found", ['methods' => $methods->toArray()]);
        
        // Now try to get settings for each method
        foreach ($methods as $method) {
            $settings = $this->getMethodSettings($method->method_id, $method->instance_id);
            $method->settings = $settings;
        }
        
        return $methods;
    }
    
    /**
     * Get method settings from options table
     */
    protected function getMethodSettings($methodId, $instanceId)
    {
        $optionName = "woocommerce_{$methodId}_{$instanceId}_settings";
        
        $setting = DB::connection('woocommerce')->table('options')
            ->where('option_name', $optionName)
            ->value('option_value');
        
        if ($setting) {
            return unserialize($setting);
        }
        
        // Return default settings if not found
        return $this->getDefaultSettings($methodId);
    }
    
    /**
     * Get default settings for a method type
     */
    protected function getDefaultSettings($methodId)
    {
        switch ($methodId) {
            case 'flat_rate':
                return [
                    'title' => 'Flat Rate',
                    'cost' => '0',
                    'tax_status' => 'taxable',
                    'type' => 'order'
                ];
            case 'free_shipping':
                return [
                    'title' => 'Free Shipping',
                    'min_amount' => '0',
                    'requires' => 'min_amount'
                ];
            case 'local_pickup':
                return [
                    'title' => 'Local Pickup',
                    'cost' => '0',
                    'tax_status' => 'none'
                ];
            default:
                return [
                    'title' => ucfirst(str_replace('_', ' ', $methodId)),
                    'cost' => '0'
                ];
        }
    }
    
    /**
     * Calculate shipping rate for a method
     */
    protected function calculateMethodRate($method, $cartItems, $destination)
    {
        $settings = $method->settings ?? [];
        
        \Log::info("Calculating method rate", [
            'method_id' => $method->method_id,
            'instance_id' => $method->instance_id,
            'settings' => $settings
        ]);
        
        switch ($method->method_id) {
            case 'flat_rate':
                return $this->calculateFlatRate($method, $settings, $cartItems);
            case 'free_shipping':
                return $this->calculateFreeShipping($method, $settings, $cartItems);
            case 'local_pickup':
                return $this->calculateLocalPickup($method, $settings);
            case 'redbox_pickup_delivery':
                return $this->calculateRedboxPickupDelivery($method, $settings);
            default:
                return $this->calculateDefaultMethod($method, $settings);
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
     * Calculate Redbox pickup delivery
     */
    protected function calculateRedboxPickupDelivery($method, $settings)
    {
        return [
            'id' => $method->method_id . ':' . $method->instance_id,
            'method_id' => $method->method_id,
            'instance_id' => $method->instance_id,
            'title' => $settings['title'] ?? 'Redbox Pickup Delivery',
            'cost' => floatval($settings['cost'] ?? 0),
            'tax_status' => $settings['tax_status'] ?? 'taxable'
        ];
    }
    
    /**
     * Calculate default method for unknown shipping methods
     */
    protected function calculateDefaultMethod($method, $settings)
    {
        return [
            'id' => $method->method_id . ':' . $method->instance_id,
            'method_id' => $method->method_id,
            'instance_id' => $method->instance_id,
            'title' => $settings['title'] ?? ucfirst(str_replace('_', ' ', $method->method_id)),
            'cost' => floatval($settings['cost'] ?? 0),
            'tax_status' => $settings['tax_status'] ?? 'taxable'
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
    
    /**
     * Normalize state name for matching
     */
    protected function normalizeState($state)
    {
        if (empty($state)) {
            return '';
        }
        
        // Remove extra spaces and normalize
        $state = trim($state);
        
        // Common state name mappings for Saudi Arabia
        $stateMappings = [
            // English to English variations
            'RIYADH' => 'Riyadh',
            'JEDDAH' => 'Jeddah',
            'DAMMAM' => 'Dammam',
            'MECCA' => 'Makkah',
            'MEDINA' => 'Madinah',
            'TAIF' => 'Taif',
            'ABHA' => 'Abha',
            'KHOBAR' => 'Khobar',
            'JUBEIL' => 'Jubail',
            'QATIF' => 'Qatif',
            'HOFUF' => 'Hofuf',
            'BURAYDAH' => 'Buraidah',
            'TABUK' => 'Tabuk',
            'HAIL' => 'Hail',
            'JIZAN' => 'Gizan',
            'NAJRAN' => 'Najran',
            'ABQAIQ' => 'Abqaiq',
            'RAS TANURA' => 'Ras Tanura',
            'YANBU' => 'Yanbu',
            'RABIGH' => 'Rabigh',
            'SAFWWA' => 'Safwa',
            'DHAHRAN' => 'Dhahran',
            'AL KHOBAR' => 'Khobar',
            'AL KHUBAR' => 'Khobar',
            
            // Arabic state names (common variations)
            'الرياض' => 'Riyadh',
            'جدة' => 'Jeddah',
            'الدمام' => 'Dammam',
            'مكة' => 'Makkah',
            'المدينة' => 'Madinah',
            'الطائف' => 'Taif',
            'أبها' => 'Abha',
            'الخبر' => 'Khobar',
            'الجبيل' => 'Jubail',
            'القطيف' => 'Qatif',
            'الهفوف' => 'Hofuf',
            'بريدة' => 'Buraidah',
            'تبوك' => 'Tabuk',
            'حائل' => 'Hail',
            'جيزان' => 'Gizan',
            'نجران' => 'Najran',
            'بقيق' => 'Abqaiq',
            'رأس تنورة' => 'Ras Tanura',
            'ينبع' => 'Yanbu',
            'رابغ' => 'Rabigh',
            'صفوى' => 'Safwa',
            'الظهران' => 'Dhahran',
        ];
        
        $upperState = strtoupper($state);
        if (isset($stateMappings[$upperState])) {
            return $stateMappings[$upperState];
        }
        
        // If no mapping found, return the original state
        return $state;
    }
} 