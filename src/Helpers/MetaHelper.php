<?php

namespace Makiomar\WooOrderDashboard\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class MetaHelper extends BaseHelper
{
    /**
     * Get all available meta keys for filtering
     *
     * @return array
     */
    public static function getAvailableMetaKeys()
    {
        return Config::get('woo-order-dashboard.meta_keys', []);
    }

    /**
     * Get meta keys organized by categories
     *
     * @return array
     */
    public static function getMetaKeyCategories()
    {
        return Config::get('woo-order-dashboard.meta_key_categories', []);
    }

    /**
     * Add a new meta key to the configuration
     *
     * @param string $key
     * @param string $label
     * @param string|null $category
     * @return bool
     */
    public static function addMetaKey($key, $label, $category = null)
    {
        $metaKeys = self::getAvailableMetaKeys();
        $metaKeys[$key] = $label;
        
        // Update the config
        Config::set('woo-order-dashboard.meta_keys', $metaKeys);
        
        // Add to category if specified
        if ($category) {
            $categories = self::getMetaKeyCategories();
            if (isset($categories[$category])) {
                $categories[$category]['keys'][] = $key;
                Config::set('woo-order-dashboard.meta_key_categories', $categories);
            }
        }
        
        // Clear cache
        self::clearMetaKeysCache();
        
        return true;
    }

    /**
     * Remove a meta key from the configuration
     *
     * @param string $key
     * @return bool
     */
    public static function removeMetaKey($key)
    {
        $metaKeys = self::getAvailableMetaKeys();
        
        if (isset($metaKeys[$key])) {
            unset($metaKeys[$key]);
            Config::set('woo-order-dashboard.meta_keys', $metaKeys);
            
            // Remove from all categories
            $categories = self::getMetaKeyCategories();
            foreach ($categories as $categoryKey => $categoryData) {
                if (in_array($key, $categoryData['keys'])) {
                    $categories[$categoryKey]['keys'] = array_diff($categoryData['keys'], [$key]);
                }
            }
            Config::set('woo-order-dashboard.meta_key_categories', $categories);
            
            // Clear cache
            self::clearMetaKeysCache();
            
            return true;
        }
        
        return false;
    }

    /**
     * Add a new category for meta keys
     *
     * @param string $categoryKey
     * @param string $categoryLabel
     * @param array $keys
     * @return bool
     */
    public static function addMetaKeyCategory($categoryKey, $categoryLabel, $keys = [])
    {
        $categories = self::getMetaKeyCategories();
        $categories[$categoryKey] = [
            'label' => $categoryLabel,
            'keys' => $keys
        ];
        
        Config::set('woo-order-dashboard.meta_key_categories', $categories);
        self::clearMetaKeysCache();
        
        return true;
    }

    /**
     * Remove a category and its meta keys
     *
     * @param string $categoryKey
     * @return bool
     */
    public static function removeMetaKeyCategory($categoryKey)
    {
        $categories = self::getMetaKeyCategories();
        
        if (isset($categories[$categoryKey])) {
            unset($categories[$categoryKey]);
            Config::set('woo-order-dashboard.meta_key_categories', $categories);
            self::clearMetaKeysCache();
            
            return true;
        }
        
        return false;
    }

    /**
     * Get meta keys for a specific category
     *
     * @param string $category
     * @return array
     */
    public static function getMetaKeysByCategory($category)
    {
        $categories = self::getMetaKeyCategories();
        
        if (isset($categories[$category])) {
            return $categories[$category]['keys'];
        }
        
        return [];
    }

    /**
     * Get the display label for a meta key
     *
     * @param string $key
     * @return string
     */
    public static function getMetaKeyLabel($key)
    {
        $metaKeys = self::getAvailableMetaKeys();
        
        return $metaKeys[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Get all unique meta keys from the database (for discovery)
     *
     * @return array
     */
    public static function discoverMetaKeys()
    {
        $cacheKey = 'woo_discovered_meta_keys';
        
        return Cache::remember($cacheKey, 3600, function () {
            $connection = self::getWooCommerceConnection();
            $prefix = self::getTablePrefix();
            
            $metaKeys = $connection->table($prefix . 'postmeta')
                ->where('meta_key', 'LIKE', '_%')
                ->distinct()
                ->pluck('meta_key')
                ->toArray();
            
            return array_values(array_filter($metaKeys));
        });
    }

    /**
     * Get meta keys with their usage count
     *
     * @return array
     */
    public static function getMetaKeysWithCount()
    {
        $cacheKey = 'woo_meta_keys_with_count';
        
        return Cache::remember($cacheKey, 3600, function () {
            $connection = self::getWooCommerceConnection();
            $prefix = self::getTablePrefix();
            
            $metaKeys = $connection->table($prefix . 'postmeta')
                ->where('meta_key', 'LIKE', '_%')
                ->selectRaw('meta_key, COUNT(*) as count')
                ->groupBy('meta_key')
                ->orderBy('count', 'desc')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->meta_key => $item->count];
                })
                ->toArray();
            
            return $metaKeys;
        });
    }

    /**
     * Clear meta keys cache
     */
    public static function clearMetaKeysCache()
    {
        Cache::forget('woo_discovered_meta_keys');
        Cache::forget('woo_meta_keys_with_count');
    }

    /**
     * Get the database connection for WooCommerce
     *
     * @return \Illuminate\Database\Connection
     */
    protected static function getWooCommerceConnection()
    {
        return \DB::connection('woocommerce');
    }

    /**
     * Get the table prefix
     *
     * @return string
     */
    protected static function getTablePrefix()
    {
        return Config::get('woo-order-dashboard.db_prefix', 'wp_');
    }
} 