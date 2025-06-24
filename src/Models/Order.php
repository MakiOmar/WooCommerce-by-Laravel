<?php

namespace Makiomar\WooOrderDashboard\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
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
    public $timestamps = false; // WooCommerce handles its own date columns

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_type',
        'post_status',
        'ping_status',
        'post_author',
        'post_title',
        'post_content',
        'post_excerpt',
        'post_date',
        'post_date_gmt',
        'post_modified',
        'post_modified_gmt',
        'to_ping',
        'pinged',
        'post_content_filtered',
        'post_parent',
        'menu_order',
        'comment_status',
        'guid'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('shop_order', function (Builder $builder) {
            $builder->where('post_type', 'shop_order');
        });
    }

    /**
     * Get the meta for the order.
     */
    public function meta()
    {
        return $this->hasMany(PostMeta::class, 'post_id', 'ID');
    }

    /**
     * Get the order items for the order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'ID');
    }

    /**
     * Get the customer that placed the order.
     */
    public function customer()
    {
        // This requires getting customer_id from postmeta
        return $this->belongsTo(Customer::class, 'customer_id'); // This will need adjustment
    }

    /**
     * Get the comments (order notes) for the order.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'comment_post_ID', 'ID')->where('comment_type', 'order_note');
    }
} 