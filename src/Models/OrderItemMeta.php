<?php

namespace Makiomar\WooOrderDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderItemMeta extends Model
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
    protected $table = 'woocommerce_order_itemmeta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'meta_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_item_id',
        'meta_key',
        'meta_value'
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Get the order item that this meta belongs to.
     */
    public function order_item()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
} 