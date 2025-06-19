<?php

namespace Makiomar\WooOrderDashboard\Helpers\Products;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;

class CategoryHelper extends BaseHelper
{
    /**
     * Get all product categories
     *
     * @param bool $hierarchical Whether to return categories in a hierarchical structure
     * @return array
     */
    public static function getAllCategories($hierarchical = false)
    {
        return self::remember('woo_product_categories_' . ($hierarchical ? 'tree' : 'flat'), 3600, function () use ($hierarchical) {
            $categories = self::getConnection()
                ->table(self::getTableName('terms') . ' as t')
                ->join(self::getTableName('term_taxonomy') . ' as tt', 't.term_id', '=', 'tt.term_id')
                ->leftJoin(self::getTableName('termmeta') . ' as tm', 't.term_id', '=', 'tm.term_id')
                ->where('tt.taxonomy', 'product_cat')
                ->select([
                    't.term_id as id',
                    't.name',
                    't.slug',
                    'tt.description',
                    'tt.parent',
                    'tt.count',
                    'tm.meta_value as thumbnail_id'
                ])
                ->get();

            if (!$hierarchical) {
                return $categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'parent' => $category->parent,
                        'count' => $category->count,
                        'thumbnail_id' => $category->thumbnail_id
                    ];
                })->toArray();
            }

            return self::buildCategoryTree($categories->toArray());
        });
    }

    /**
     * Get category by ID
     *
     * @param int $categoryId
     * @return array|null
     */
    public static function getCategory($categoryId)
    {
        return self::remember("woo_product_category_{$categoryId}", 3600, function () use ($categoryId) {
            $category = self::getConnection()
                ->table(self::getTableName('terms') . ' as t')
                ->join(self::getTableName('term_taxonomy') . ' as tt', 't.term_id', '=', 'tt.term_id')
                ->leftJoin(self::getTableName('termmeta') . ' as tm', 't.term_id', '=', 'tm.term_id')
                ->where('tt.taxonomy', 'product_cat')
                ->where('t.term_id', $categoryId)
                ->select([
                    't.term_id as id',
                    't.name',
                    't.slug',
                    'tt.description',
                    'tt.parent',
                    'tt.count',
                    'tm.meta_value as thumbnail_id'
                ])
                ->first();

            if (!$category) {
                return null;
            }

            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'parent' => $category->parent,
                'count' => $category->count,
                'thumbnail_id' => $category->thumbnail_id
            ];
        });
    }

    /**
     * Get products in a category
     *
     * @param int $categoryId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getCategoryProducts($categoryId, $limit = 10, $offset = 0)
    {
        $key = "woo_category_products_{$categoryId}_{$limit}_{$offset}";
        
        return self::remember($key, 1800, function () use ($categoryId, $limit, $offset) {
            $products = self::getConnection()
                ->table(self::getTableName('term_relationships') . ' as tr')
                ->join(self::getTableName('posts') . ' as p', 'tr.object_id', '=', 'p.ID')
                ->where('tr.term_taxonomy_id', $categoryId)
                ->where('p.post_type', 'product')
                ->where('p.post_status', 'publish')
                ->select([
                    'p.ID as id',
                    'p.post_title as name',
                    'p.post_name as slug',
                    'p.post_excerpt as short_description'
                ])
                ->offset($offset)
                ->limit($limit)
                ->get();

            return $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'short_description' => $product->short_description
                ];
            })->toArray();
        });
    }

    /**
     * Build hierarchical category tree
     *
     * @param array $categories
     * @param int $parentId
     * @return array
     */
    protected static function buildCategoryTree($categories, $parentId = 0)
    {
        $branch = [];

        foreach ($categories as $category) {
            if ($category->parent == $parentId) {
                $children = self::buildCategoryTree($categories, $category->id);
                
                $branch[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'count' => $category->count,
                    'thumbnail_id' => $category->thumbnail_id,
                    'children' => $children
                ];
            }
        }

        return $branch;
    }
} 