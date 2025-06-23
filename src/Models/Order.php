<?php

namespace Makiomar\WooOrderDashboard\Models;

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
    protected $table = 'wc_orders';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // We will handle date columns manually

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_created_gmt' => 'datetime',
        'date_updated_gmt' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'currency',
        'discount_total',
        'discount_tax',
        'shipping_total',
        'shipping_tax',
        'cart_tax',
        'total',
        'total_tax',
        'customer_id',
        'payment_method',
        'payment_method_title',
        'transaction_id',
        'customer_ip_address',
        'customer_user_agent',
        'created_via',
        'customer_note',
        'date_completed',
        'date_paid',
        'cart_hash',
        'date_created_gmt',
        'date_updated_gmt',
    ];

    /**
     * Get the order items for the order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Get the customer that placed the order.
     */
    public function customer()
    {
        // This assumes a wp_users table and a Customer model exist
        return $this->belongsTo(Customer::class, 'customer_id');
    }
} 