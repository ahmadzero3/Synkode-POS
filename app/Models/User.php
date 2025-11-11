<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use App\Models\UserWarehouse;
use App\Models\OrderedProduct;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'username',
        'role_id',
        'status',
        'avatar',
        'mobile',
        'is_allowed_all_warehouses',
        'pos_sorting_preference',
        'pos_manual_order',
    ];

    /**
     * Insert & update User Id's
     * */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Remove manual ID assignment to let database handle auto-increment
            if (isset($model->id)) {
                unset($model->id);
            }
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Join with roles table
     * */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Join with roles table
     * */
    public function orderedProducts(): BelongsTo
    {
        return $this->belongsTo(OrderedProduct::class, 'assigned_user_id');
    }

    /**
     * Join with user_warehouses table
     * */
    public function userWarehouses(): HasMany
    {
        return $this->hasMany(UserWarehouse::class, 'user_id');
    }

    /**
     * Get the accessible warehouses for the user
     * */
    public function getAccessibleWarehouses(bool $viewAllWarehouse = false)
    {
        if ($this->is_allowed_all_warehouses || $viewAllWarehouse) {
            return Warehouse::all();
        }

        $warehouseIds = UserWarehouse::where('user_id', $this->id)->pluck('warehouse_id');

        // Retrieve warehouse details for the assigned IDs
        return Warehouse::whereIn('id', $warehouseIds)->get();
    }

}