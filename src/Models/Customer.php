<?php

namespace Makiomar\WooOrderDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
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
    protected $primaryKey = 'ID';

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
        $this->table = DB::getDatabaseName() . '.wp_users';
    }

    /**
     * Get the meta for the user.
     */
    public function meta()
    {
        return $this->hasMany(UserMeta::class, 'user_id', 'ID');
    }

    /**
     * Get the first name for the user.
     */
    public function getFirstName()
    {
        return $this->meta()->where('meta_key', 'first_name')->value('meta_value');
    }

    /**
     * Get the last name for the user.
     */
    public function getLastName()
    {
        return $this->meta()->where('meta_key', 'last_name')->value('meta_value');
    }
} 