<?php

namespace Makiomar\WooOrderDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserMeta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'umeta_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = DB::getDatabaseName() . '.wp_usermeta';
    }

    /**
     * Get the user that owns the meta.
     */
    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id', 'ID');
    }
} 