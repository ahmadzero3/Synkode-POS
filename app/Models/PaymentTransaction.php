<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormatsDateInputs;
use App\Traits\FormatTime;
use App\Models\Expenses\Expense;
use App\Models\PaymentTypes;
use App\Models\Accounts\AccountTransaction;
use App\Models\User;
use App\Models\ChequeTransaction;

class PaymentTransaction extends Model
{
    use HasFactory;
    use FormatsDateInputs;
    use FormatTime;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_date',
        'transaction_id',
        'transaction_type',
        'payment_type_id',
        'amount',
        'reference_no',
        'note',
        'payment_from_unique_code',
    ];

    /**
     * ✅ Automatically cast these attributes to Carbon instances
     * so you can safely use ->format(), ->diffForHumans(), etc.
     */
    protected $casts = [
        'transaction_date' => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Insert & update User Id's
     */
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

    /**
     * Get the parent transactions model (user or post).
     */
    public function transaction(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Define the relationship between Order and User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * This method calling the Trait FormatsDateInputs
     * @return null|string
     * Use it as formatted_transaction_date
     */
    public function getFormattedTransactionDateAttribute()
    {
        return $this->toUserDateFormat($this->transaction_date); // Call the trait method
    }

    /**
     * This method calling the Trait FormatTime
     * @return null|string
     * Use it as format_created_time
     */
    public function getFormatCreatedTimeAttribute()
    {
        return $this->toUserTimeFormat($this->created_at); // Call the trait method
    }

    /**
     * Get Payment Type
     * @return HasOne
     */
    public function paymentType(): HasOne
    {
        return $this->hasOne(PaymentTypes::class, 'id', 'payment_type_id');
    }

    /**
     * Define the relationship between Item Transaction & Items table.
     *
     * @return MorphMany
     */
    public function accountTransaction(): MorphMany
    {
        return $this->morphMany(AccountTransaction::class, 'transaction');
    }

    /**
     * Define the relationship with cheque transactions.
     *
     * @return HasOne
     */
    public function chequeTransaction(): HasOne
    {
        return $this->hasOne(ChequeTransaction::class, 'payment_transaction_id');
    }
}
