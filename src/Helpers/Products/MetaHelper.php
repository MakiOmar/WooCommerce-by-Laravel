<?php

namespace Makiomar\WooOrderDashboard\Helpers\Products;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;

class MetaHelper extends BaseHelper
{
    /**
     * Get all meta data for a product
     *
     * @param int $productId
     * @return array
     */
    public static function getAllMeta($productId)
    {
        return self::remember("woo_product_meta_{$productId}", 3600, function () use ($productId) {
            $meta = self::getConnection()
                ->table(self::getTableName('postmeta'))
                ->where('post_id', $productId)
                ->get()
                ->pluck('meta_value', 'meta_key')
                ->toArray();

            return $meta;
        });
    }

    /**
     * Get product price data
     *
     * @param int $productId
     * @return array
     */
    public static function getPriceData($productId)
    {
        return self::remember("woo_product_price_{$productId}", 3600, function () use ($productId) {
            $priceFields = [
                '_price',
                '_regular_price',
                '_sale_price',
                '_sale_price_dates_from',
                '_sale_price_dates_to'
            ];

            $meta = self::getConnection()
                ->table(self::getTableName('postmeta'))
                ->where('post_id', $productId)
                ->whereIn('meta_key', $priceFields)
                ->get()
                ->pluck('meta_value', 'meta_key')
                ->toArray();

            return [
                'price' => $meta['_price'] ?? null,
                'regular_price' => $meta['_regular_price'] ?? null,
                'sale_price' => $meta['_sale_price'] ?? null,
                'sale_start' => $meta['_sale_price_dates_from'] ?? null,
                'sale_end' => $meta['_sale_price_dates_to'] ?? null,
                'on_sale' => !empty($meta['_sale_price']) && 
                            (!isset($meta['_sale_price_dates_to']) || $meta['_sale_price_dates_to'] > time())
            ];
        });
    }

    /**
     * Get product stock data
     *
     * @param int $productId
     * @return array
     */
    public static function getStockData($productId)
    {
        return self::remember("woo_product_stock_{$productId}", 1800, function () use ($productId) {
            $stockFields = [
                '_manage_stock',
                '_stock',
                '_stock_status',
                '_backorders',
                '_low_stock_amount'
            ];

            $meta = self::getConnection()
                ->table(self::getTableName('postmeta'))
                ->where('post_id', $productId)
                ->whereIn('meta_key', $stockFields)
                ->get()
                ->pluck('meta_value', 'meta_key')
                ->toArray();

            return [
                'manage_stock' => $meta['_manage_stock'] ?? 'no',
                'stock' => $meta['_stock'] ?? null,
                'stock_status' => $meta['_stock_status'] ?? 'instock',
                'backorders' => $meta['_backorders'] ?? 'no',
                'low_stock_amount' => $meta['_low_stock_amount'] ?? null
            ];
        });
    }

    /**
     * Get product dimensions and weight
     *
     * @param int $productId
     * @return array
     */
    public static function getDimensionsData($productId)
    {
        return self::remember("woo_product_dimensions_{$productId}", 3600, function () use ($productId) {
            $fields = [
                '_weight',
                '_length',
                '_width',
                '_height'
            ];

            $meta = self::getConnection()
                ->table(self::getTableName('postmeta'))
                ->where('post_id', $productId)
                ->whereIn('meta_key', $fields)
                ->get()
                ->pluck('meta_value', 'meta_key')
                ->toArray();

            return [
                'weight' => $meta['_weight'] ?? null,
                'length' => $meta['_length'] ?? null,
                'width' => $meta['_width'] ?? null,
                'height' => $meta['_height'] ?? null
            ];
        });
    }

    /**
     * Get product gallery image IDs
     *
     * @param int $productId
     * @return array
     */
    public static function getGalleryImageIds($productId)
    {
        return self::remember("woo_product_gallery_{$productId}", 3600, function () use ($productId) {
            $gallery = self::getConnection()
                ->table(self::getTableName('postmeta'))
                ->where('post_id', $productId)
                ->where('meta_key', '_product_image_gallery')
                ->value('meta_value');

            return $gallery ? explode(',', $gallery) : [];
        });
    }

    /**
     * Get product attributes
     *
     * @param int $productId
     * @return array
     */
    public static function getAttributes($productId)
    {
        return self::remember("woo_product_attributes_{$productId}", 3600, function () use ($productId) {
            $attributes = self::getConnection()
                ->table(self::getTableName('postmeta'))
                ->where('post_id', $productId)
                ->where('meta_key', '_product_attributes')
                ->value('meta_value');

            return $attributes ? unserialize($attributes) : [];
        });
    }
} 