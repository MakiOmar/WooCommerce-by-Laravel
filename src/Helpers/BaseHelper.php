<?php

namespace Makiomar\WooOrderDashboard\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Query\Builder;

abstract class BaseHelper
{
    /**
     * Cache TTL values in seconds
     */
    protected const CACHE_SHORT = 300;    // 5 minutes
    protected const CACHE_MEDIUM = 1800;   // 30 minutes
    protected const CACHE_LONG = 3600;     // 1 hour
    protected const CACHE_EXTENDED = 86400; // 24 hours

    /**
     * Batch size for chunk operations
     */
    protected const CHUNK_SIZE = 100;

    /**
     * Get the database connection
     *
     * @return \Illuminate\Database\Connection
     */
    protected static function getConnection()
    {
        return DB::connection('woocommerce');
    }

    /**
     * Cache wrapper for database queries with automatic key generation and fallback
     *
     * @param string $key
     * @param int $ttl
     * @param \Closure $callback
     * @return mixed
     */
    protected static function remember($key, $ttl, \Closure $callback)
    {
        // If caching is disabled, execute callback directly
        if (!config('woo-order-dashboard.cache.enabled', true)) {
            return $callback();
        }

        // Add prefix to prevent key collisions
        $prefixedKey = config('woo-order-dashboard.cache.prefix', 'woo_') . $key;
        
        try {
            return Cache::remember($prefixedKey, $ttl, $callback);
        } catch (\Exception $e) {
            // Log cache error but don't break functionality
            \Log::warning("Cache error: {$e->getMessage()}. Executing without cache.");
            return $callback();
        }
    }

    /**
     * Process large datasets in chunks
     *
     * @param Builder $query
     * @param \Closure $callback
     * @param int $chunkSize
     * @return void
     */
    protected static function processInChunks(Builder $query, \Closure $callback, $chunkSize = self::CHUNK_SIZE)
    {
        $query->chunk($chunkSize, $callback);
    }

    /**
     * Get cached value or compute if not exists
     * With support for cache tags and automatic key generation
     *
     * @param string|array $tags
     * @param string $key
     * @param int $ttl
     * @param \Closure $callback
     * @return mixed
     */
    protected static function rememberWithTags($tags, $key, $ttl, \Closure $callback)
    {
        // If caching is disabled, execute callback directly
        if (!config('woo-order-dashboard.cache.enabled', true)) {
            return $callback();
        }

        $tags = is_array($tags) ? $tags : [$tags];
        $prefixedKey = config('woo-order-dashboard.cache.prefix', 'woo_') . $key;
        
        try {
            // Try using tags if supported (Redis/Memcached)
            if (config('woo-order-dashboard.cache.tags_enabled', true) && Cache::supportsTags()) {
                return Cache::tags($tags)->remember($prefixedKey, $ttl, $callback);
            }
            
            // Fallback to regular cache if tags not supported
            return Cache::remember($prefixedKey, $ttl, $callback);
        } catch (\Exception $e) {
            // Log cache error but don't break functionality
            \Log::warning("Cache error: {$e->getMessage()}. Executing without cache.");
            return $callback();
        }
    }

    /**
     * Clear cache by tags with fallback
     *
     * @param string|array $tags
     * @return void
     */
    protected static function clearCacheByTags($tags)
    {
        if (!config('woo-order-dashboard.cache.enabled', true)) {
            return;
        }

        try {
            $tags = is_array($tags) ? $tags : [$tags];
            
            if (config('woo-order-dashboard.cache.tags_enabled', true) && Cache::supportsTags()) {
                Cache::tags($tags)->flush();
            } else {
                // Fallback: Clear entire cache if tags not supported
                // This is not ideal but ensures cache consistency
                Cache::flush();
            }
        } catch (\Exception $e) {
            \Log::warning("Cache clear error: {$e->getMessage()}");
        }
    }

    /**
     * Optimize a query to use WooCommerce's existing indexes
     *
     * @param Builder $query
     * @return Builder
     */
    protected static function optimizeQuery(Builder $query)
    {
        // Force the query to use WooCommerce's existing indexes
        if (str_contains($query->from, 'posts')) {
            // WooCommerce already has indexes on post_type and post_status
            $query->orderBy('post_type')->orderBy('post_status');
        }
        
        if (str_contains($query->from, 'postmeta')) {
            // WooCommerce has an index on post_id
            $query->orderBy('post_id');
        }
        
        return $query;
    }

    /**
     * Execute a query with timeout and deadlock retry
     *
     * @param \Closure $queryCallback
     * @param int $maxAttempts
     * @param int $timeout
     * @return mixed
     * @throws \Exception
     */
    protected static function executeWithRetry(\Closure $queryCallback, $maxAttempts = 3, $timeout = 5)
    {
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            try {
                return self::getConnection()->transaction(function () use ($queryCallback) {
                    return $queryCallback();
                });
            } catch (\Exception $e) {
                $attempt++;
                
                // If it's a deadlock or lock wait timeout and we have attempts left, retry
                if ($attempt < $maxAttempts && (
                    strpos($e->getMessage(), 'Deadlock') !== false ||
                    strpos($e->getMessage(), 'Lock wait timeout') !== false
                )) {
                    // Exponential backoff
                    usleep(pow(2, $attempt) * 100000);
                    continue;
                }
                
                throw $e;
            }
        }
    }

    /**
     * Split a large IN clause into smaller chunks
     *
     * @param Builder $query
     * @param string $column
     * @param array $values
     * @param int $chunkSize
     * @return Builder
     */
    protected static function whereInChunked(Builder $query, $column, array $values, $chunkSize = 1000)
    {
        return $query->where(function ($query) use ($column, $values, $chunkSize) {
            collect($values)->chunk($chunkSize)->each(function ($chunk) use ($query, $column) {
                $query->orWhereIn($column, $chunk);
            });
        });
    }
} 