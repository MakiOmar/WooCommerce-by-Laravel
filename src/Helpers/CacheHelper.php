<?php

namespace Makiomar\WooOrderDashboard\Helpers;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;

class CacheHelper extends BaseHelper
{
    /**
     * Clear cache by tags.
     * This method provides a public interface to the protected cache clearing functionality.
     *
     * @param string|array $tags
     * @return void
     */
    public function clearByTags($tags)
    {
        static::clearCacheByTags($tags);
    }

    /**
     * Get the database connection name.
     *
     * @return string
     */
    protected function getConnectionName()
    {
        return config('woo-order-dashboard.database.connection', 'woocommerce');
    }

    /**
     * Get table name with proper prefix handling.
     *
     * @param string $table
     * @return string
     */
    protected function getTableName($table)
    {
        $prefix = config('woo-order-dashboard.database.prefix', 'wp_');
        
        if (strpos($table, $prefix) === 0) {
            return $table;
        }
        
        return $prefix . $table;
    }
} 