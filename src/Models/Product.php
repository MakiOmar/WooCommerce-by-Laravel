<?php

namespace Makiomar\WooOrderDashboard\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'woocommerce';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('product', function (Builder $builder) {
            $builder->whereIn('post_type', ['product', 'product_variation']);
        });
    }

    /**
     * Scope a query to only include simple products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSimple(Builder $query)
    {
        return $query->where('post_type', 'product');
    }

    /**
     * Scope a query to only include variation products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVariable(Builder $query)
    {
        return $query->where('post_type', 'product_variation');
    }

    /**
     * Get the meta for the product.
     */
    public function meta()
    {
        return $this->hasMany(PostMeta::class, 'post_id', 'ID');
    }

    /**
     * Get the SKU for the product.
     */
    public function getSku()
    {
        return $this->meta()->where('meta_key', '_sku')->value('meta_value');
    }

    /**
     * Get the price for the product.
     */
    public function getPrice()
    {
        return $this->meta()->where('meta_key', '_price')->value('meta_value');
    }
} 