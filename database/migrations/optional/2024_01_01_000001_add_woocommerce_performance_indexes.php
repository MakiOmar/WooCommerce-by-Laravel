<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddWoocommercePerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('woo-order-dashboard.db_prefix', 'wp_');
        $connection = DB::connection('woocommerce');

        // First, check existing indexes to avoid duplicates
        $this->safelyCreateIndexes($connection, $prefix);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = config('woo-order-dashboard.db_prefix', 'wp_');
        $connection = DB::connection('woocommerce');

        // Only remove indexes we created
        $this->safelyRemoveIndexes($connection, $prefix);
    }

    /**
     * Safely create indexes checking for existing ones first
     *
     * @param \Illuminate\Database\Connection $connection
     * @param string $prefix
     * @return void
     */
    protected function safelyCreateIndexes($connection, $prefix)
    {
        // Check if we're dealing with MySQL/MariaDB
        $isMySQL = in_array($connection->getDriverName(), ['mysql', 'mariadb']);
        
        try {
            // Start transaction
            $connection->beginTransaction();

            // Posts table indexes
            $this->createIndexIfNotExists($connection, $prefix . 'posts', 'type_status_date', 
                'CREATE INDEX type_status_date ON ' . $prefix . 'posts(post_type, post_status, post_date) ALGORITHM=INPLACE LOCK=NONE');

            // Only add post_parent index if it doesn't conflict with WooCommerce
            if (!$this->indexExists($connection, $prefix . 'posts', 'post_parent')) {
                $this->createIndexIfNotExists($connection, $prefix . 'posts', 'woo_dashboard_parent', 
                    'CREATE INDEX woo_dashboard_parent ON ' . $prefix . 'posts(post_parent, post_type) ALGORITHM=INPLACE LOCK=NONE');
            }

            // Postmeta table - only add if doesn't exist
            if (!$this->indexExists($connection, $prefix . 'postmeta', 'post_meta_key')) {
                $this->createIndexIfNotExists($connection, $prefix . 'postmeta', 'woo_dashboard_meta', 
                    'CREATE INDEX woo_dashboard_meta ON ' . $prefix . 'postmeta(post_id, meta_key(50)) ALGORITHM=INPLACE LOCK=NONE');
            }

            // Order items table - check WooCommerce version first
            if ($this->isWooCommerceTableAccessible($connection, $prefix . 'woocommerce_order_items')) {
                $this->createIndexIfNotExists($connection, $prefix . 'woocommerce_order_items', 'woo_dashboard_order_item', 
                    'CREATE INDEX woo_dashboard_order_item ON ' . $prefix . 'woocommerce_order_items(order_id, order_item_type) ALGORITHM=INPLACE LOCK=NONE');
            }

            // Order itemmeta table - check WooCommerce version first
            if ($this->isWooCommerceTableAccessible($connection, $prefix . 'woocommerce_order_itemmeta')) {
                $this->createIndexIfNotExists($connection, $prefix . 'woocommerce_order_itemmeta', 'woo_dashboard_order_item_meta', 
                    'CREATE INDEX woo_dashboard_order_item_meta ON ' . $prefix . 'woocommerce_order_itemmeta(order_item_id, meta_key(50)) ALGORITHM=INPLACE LOCK=NONE');
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            
            // Log the error but don't stop the migration
            \Log::error('Failed to create some indexes: ' . $e->getMessage());
            
            // Throw exception only in development
            if (app()->environment('local', 'development')) {
                throw $e;
            }
        }
    }

    /**
     * Safely remove our custom indexes
     *
     * @param \Illuminate\Database\Connection $connection
     * @param string $prefix
     * @return void
     */
    protected function safelyRemoveIndexes($connection, $prefix)
    {
        try {
            $connection->beginTransaction();

            // Only remove indexes we created
            $this->dropIndexIfExists($connection, $prefix . 'posts', 'woo_dashboard_parent');
            $this->dropIndexIfExists($connection, $prefix . 'postmeta', 'woo_dashboard_meta');
            $this->dropIndexIfExists($connection, $prefix . 'woocommerce_order_items', 'woo_dashboard_order_item');
            $this->dropIndexIfExists($connection, $prefix . 'woocommerce_order_itemmeta', 'woo_dashboard_order_item_meta');

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            \Log::error('Failed to remove some indexes: ' . $e->getMessage());
        }
    }

    /**
     * Check if an index exists
     *
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     * @param string $indexName
     * @return bool
     */
    protected function indexExists($connection, $table, $indexName)
    {
        $indexes = $connection->select(
            "SHOW INDEXES FROM {$table} WHERE Key_name = ?",
            [$indexName]
        );

        return count($indexes) > 0;
    }

    /**
     * Create index if it doesn't exist
     *
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     * @param string $indexName
     * @param string $createStatement
     * @return void
     */
    protected function createIndexIfNotExists($connection, $table, $indexName, $createStatement)
    {
        if (!$this->indexExists($connection, $table, $indexName)) {
            try {
                $connection->statement($createStatement);
            } catch (\Exception $e) {
                // Log the error but continue with other indexes
                \Log::warning("Failed to create index {$indexName} on {$table}: " . $e->getMessage());
            }
        }
    }

    /**
     * Drop index if it exists
     *
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     * @param string $indexName
     * @return void
     */
    protected function dropIndexIfExists($connection, $table, $indexName)
    {
        if ($this->indexExists($connection, $table, $indexName)) {
            try {
                $connection->statement("DROP INDEX {$indexName} ON {$table}");
            } catch (\Exception $e) {
                // Log the error but continue with other indexes
                \Log::warning("Failed to drop index {$indexName} on {$table}: " . $e->getMessage());
            }
        }
    }

    /**
     * Check if WooCommerce table is accessible
     *
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     * @return bool
     */
    protected function isWooCommerceTableAccessible($connection, $table)
    {
        try {
            $connection->select("SELECT 1 FROM {$table} LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
} 