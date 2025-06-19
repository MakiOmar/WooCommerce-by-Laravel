<?php

namespace Makiomar\WooOrderDashboard\Helpers\Products;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;

class ProductHelper extends BaseHelper
{
    /**
     * Get product by ID
     *
     * @param int $productId
     * @return array|null
     */
    public static function getProduct($productId)
    {
        return self::remember("woo_product_{$productId}", 3600, function () use ($productId) {
            $product = self::getConnection()
                ->table(self::getPrefix() . 'posts as p')
                ->leftJoin(self::getPrefix() . 'postmeta as pm', 'p.ID', '=', 'pm.post_id')
                ->where('p.ID', $productId)
                ->where('p.post_type', 'product')
                ->select('p.*', 'pm.meta_key', 'pm.meta_value')
                ->get();

            if ($product->isEmpty()) {
                return null;
            }

            // Transform the product data
            return self::transformProductData($product);
        });
    }

    /**
     * Transform product database data into a structured array
     *
     * @param \Illuminate\Support\Collection $data
     * @return array
     */
    protected static function transformProductData($data)
    {
        $product = [
            'id' => $data->first()->ID,
            'name' => $data->first()->post_title,
            'slug' => $data->first()->post_name,
            'type' => 'simple', // Default type
            'status' => $data->first()->post_status,
            'meta' => []
        ];

        // Process meta data
        foreach ($data as $row) {
            if ($row->meta_key && $row->meta_value) {
                $product['meta'][$row->meta_key] = $row->meta_value;
            }
        }

        return $product;
    }
} 