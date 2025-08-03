<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WooCommerceCacheService
{
    /**
     * Clear all order-related cache
     *
     * @return bool
     */
    public function clearOrderCache(): bool
    {
        try {
            // Clear WooCommerce order transients
            DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'like', '_transient_wc_order_%')
                ->delete();

            // Clear timeout transients
            DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'like', '_transient_timeout_wc_order_%')
                ->delete();

            // Clear order count transients
            DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'like', '_transient_wc_order_count_%')
                ->delete();

            // Clear Laravel cache
            Cache::forget('woo_orders_list');
            Cache::forget('woo_orders_statistics');
            Cache::forget('woo_order_statuses');

            Log::info('Order cache cleared successfully');
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to clear order cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear specific order cache
     *
     * @param int $orderId
     * @return bool
     */
    public function clearOrderCacheById(int $orderId): bool
    {
        try {
            // Clear specific order transients
            DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'like', '_transient_wc_order_' . $orderId . '%')
                ->delete();

            DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'like', '_transient_timeout_wc_order_' . $orderId . '%')
                ->delete();

            // Clear Laravel cache for specific order
            Cache::forget("woo_order_{$orderId}");
            Cache::forget("woo_order_meta_{$orderId}");
            Cache::forget("woo_order_items_{$orderId}");

            Log::info("Order cache cleared for order {$orderId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to clear order cache for order {$orderId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear customer order caches
     *
     * @param int $customerId
     * @return bool
     */
    public function clearCustomerOrderCaches(int $customerId): bool
    {
        try {
            // Clear customer order transients
            DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'like', '_transient_wc_customer_' . $customerId . '%')
                ->delete();

            // Clear Laravel cache
            Cache::forget("woo_customer_orders_{$customerId}");
            Cache::forget("woo_customer_{$customerId}");

            Log::info("Customer order cache cleared for customer {$customerId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to clear customer order cache for customer {$customerId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear product stock caches
     *
     * @param array $productIds
     * @return bool
     */
    public function clearProductStockCaches(array $productIds): bool
    {
        try {
            foreach ($productIds as $productId) {
                // Clear product stock status transients
                DB::connection('woocommerce')
                    ->table('wp_options')
                    ->where('option_name', 'like', '_transient_wc_product_' . $productId . '%')
                    ->delete();

                DB::connection('woocommerce')
                    ->table('wp_options')
                    ->where('option_name', 'like', '_transient_timeout_wc_product_' . $productId . '%')
                    ->delete();

                // Clear Laravel cache
                Cache::forget("woo_product_{$productId}");
                Cache::forget("woo_product_stock_{$productId}");
            }

            // Clear general stock transients
            DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'like', '_transient_wc_low_stock_count%')
                ->delete();

            DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'like', '_transient_wc_outofstock_count%')
                ->delete();

            Log::info('Product stock cache cleared for products: ' . implode(', ', $productIds));
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to clear product stock cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cache on order creation
     *
     * @return bool
     */
    public function clearCacheOnOrderCreate(): bool
    {
        try {
            $this->clearOrderCache();
            $this->clearOrderListCache();
            $this->clearStatisticsCache();

            Log::info('Cache cleared on order creation');
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to clear cache on order creation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cache on order update
     *
     * @param int $orderId
     * @return bool
     */
    public function clearCacheOnOrderUpdate(int $orderId): bool
    {
        try {
            $this->clearOrderCacheById($orderId);
            $this->clearOrderListCache();

            // Get customer ID to clear customer cache
            $customerId = DB::connection('woocommerce')
                ->table('wp_postmeta')
                ->where('post_id', $orderId)
                ->where('meta_key', '_customer_user')
                ->value('meta_value');

            if ($customerId > 0) {
                $this->clearCustomerOrderCaches($customerId);
            }

            Log::info("Cache cleared on order update for order {$orderId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to clear cache on order update for order {$orderId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cache on order deletion
     *
     * @param array $orderIds
     * @return bool
     */
    public function clearCacheOnOrderDelete(array $orderIds): bool
    {
        try {
            foreach ($orderIds as $orderId) {
                $this->clearOrderCacheById($orderId);
            }

            $this->clearOrderListCache();
            $this->clearStatisticsCache();

            Log::info('Cache cleared on order deletion for orders: ' . implode(', ', $orderIds));
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to clear cache on order deletion: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cache on order status change
     *
     * @param int $orderId
     * @return bool
     */
    public function clearCacheOnOrderStatusChange(int $orderId): bool
    {
        try {
            $this->clearOrderCacheById($orderId);
            $this->clearOrderListCache();
            $this->clearStatusCache();

            Log::info("Cache cleared on order status change for order {$orderId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to clear cache on order status change for order {$orderId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear order list cache
     *
     * @return bool
     */
    public function clearOrderListCache(): bool
    {
        try {
            Cache::forget('woo_orders_list');
            Cache::forget('woo_orders_paginated');

            Log::info('Order list cache cleared');
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to clear order list cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear status cache
     *
     * @return bool
     */
    public function clearStatusCache(): bool
    {
        try {
            Cache::forget('woo_order_statuses');
            Cache::forget('woo_order_statuses_with_metadata');

            Log::info('Status cache cleared');
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to clear status cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear statistics cache
     *
     * @return bool
     */
    public function clearStatisticsCache(): bool
    {
        try {
            Cache::forget('woo_orders_statistics');
            Cache::forget('woo_orders_count_by_status');

            Log::info('Statistics cache cleared');
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to clear statistics cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all WooCommerce dashboard cache
     *
     * @return bool
     */
    public function clearAllWooCommerceCache(): bool
    {
        try {
            $this->clearOrderCache();
            $this->clearOrderListCache();
            $this->clearStatusCache();
            $this->clearStatisticsCache();

            // Clear all WooCommerce transients
            DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'like', '_transient_wc_%')
                ->delete();

            DB::connection('woocommerce')
                ->table('wp_options')
                ->where('option_name', 'like', '_transient_timeout_wc_%')
                ->delete();

            Log::info('All WooCommerce dashboard cache cleared');
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to clear all WooCommerce cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cache for specific products
     *
     * @param array $productIds
     * @return bool
     */
    public function clearProductCache(array $productIds): bool
    {
        try {
            foreach ($productIds as $productId) {
                Cache::forget("woo_product_{$productId}");
                Cache::forget("woo_product_meta_{$productId}");
            }

            Log::info('Product cache cleared for products: ' . implode(', ', $productIds));
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to clear product cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cache for specific customers
     *
     * @param array $customerIds
     * @return bool
     */
    public function clearCustomerCache(array $customerIds): bool
    {
        try {
            foreach ($customerIds as $customerId) {
                Cache::forget("woo_customer_{$customerId}");
                Cache::forget("woo_customer_orders_{$customerId}");
            }

            Log::info('Customer cache cleared for customers: ' . implode(', ', $customerIds));
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to clear customer cache: ' . $e->getMessage());
            return false;
        }
    }
} 