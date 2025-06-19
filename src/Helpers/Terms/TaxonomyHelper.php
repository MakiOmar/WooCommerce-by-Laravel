<?php

namespace Makiomar\WooOrderDashboard\Helpers\Terms;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;

class TaxonomyHelper extends BaseHelper
{
    /**
     * Get terms for a taxonomy
     *
     * @param string $taxonomy
     * @return array
     */
    public static function getTerms($taxonomy)
    {
        return self::remember("woo_terms_{$taxonomy}", 3600, function () use ($taxonomy) {
            $terms = self::getConnection()
                ->table(self::getPrefix() . 'terms as t')
                ->join(self::getPrefix() . 'term_taxonomy as tt', 't.term_id', '=', 'tt.term_id')
                ->where('tt.taxonomy', $taxonomy)
                ->select('t.term_id', 't.name', 't.slug', 'tt.description', 'tt.parent', 'tt.count')
                ->orderBy('t.name', 'ASC')
                ->get();

            return $terms->map(function ($term) {
                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'description' => $term->description,
                    'parent' => $term->parent,
                    'count' => $term->count
                ];
            })->toArray();
        });
    }

    /**
     * Get term by ID
     *
     * @param int $termId
     * @return array|null
     */
    public static function getTerm($termId)
    {
        return self::remember("woo_term_{$termId}", 3600, function () use ($termId) {
            $term = self::getConnection()
                ->table(self::getPrefix() . 'terms as t')
                ->join(self::getPrefix() . 'term_taxonomy as tt', 't.term_id', '=', 'tt.term_id')
                ->where('t.term_id', $termId)
                ->select('t.term_id', 't.name', 't.slug', 'tt.taxonomy', 'tt.description', 'tt.parent', 'tt.count')
                ->first();

            if (!$term) {
                return null;
            }

            return [
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'taxonomy' => $term->taxonomy,
                'description' => $term->description,
                'parent' => $term->parent,
                'count' => $term->count
            ];
        });
    }
} 