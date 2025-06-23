<?php

namespace Makiomar\WooOrderDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PostMeta extends Model
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
    protected $table = 'postmeta';

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
        'post_id',
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
     * Get the post that owns the meta.
     */
    public function post()
    {
        return $this->belongsTo(Product::class, 'post_id', 'ID');
    }
} 