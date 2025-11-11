<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_at',
        'end_at',
        'duration_minutes',
        'created_by',
        'updated_by',
        'is_lifetime',
        'is_yearly',
        'is_monthly',
        'is_weekly',
        'is_daily',
        'start_year',
        'end_year',
        'start_month',
        'end_month',
        'start_day',
        'end_day',
        'start_hour',
        'end_hour',
        'monthly_start_time',
        'monthly_end_time',
        'weekly_start_time',
        'weekly_end_time',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'is_lifetime' => 'boolean',
        'is_yearly' => 'boolean',
        'is_monthly' => 'boolean',
        'is_weekly' => 'boolean',
        'is_daily' => 'boolean',
        'start_year' => 'integer',
        'end_year' => 'integer',
        'start_month' => 'integer',
        'end_month' => 'integer',
        'start_day' => 'integer',
        'end_day' => 'integer',
        'start_hour' => 'integer',
        'end_hour' => 'integer',
        'monthly_start_time' => 'string',
        'monthly_end_time' => 'string',
        'weekly_start_time' => 'string',
        'weekly_end_time' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}