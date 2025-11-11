<?php

namespace App\Models\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormatsDateInputs;
use App\Models\Items\ItemBatchTransaction;
use App\Models\Items\ItemSerialTransaction;
use App\Models\Accounts\AccountTransaction;
use App\Models\Items\Item;
use App\Models\Items\ItemStockTransfer;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\Purchase\Purchase;

class ItemTransaction extends Model
{
    use HasFactory;

    use FormatsDateInputs;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_date',
        'unique_code',
        'warehouse_id',
        'unit_id',
        'item_id',
        'description',
        'tracking_type',
        'item_location',

        'unit_price',

        'mrp',

        'quantity',

        'discount',
        'discount_type',
        'discount_amount',

        'tax_id',
        'tax_type',
        'tax_amount',

        'charge_type',
        'charge_amount',

        'total',
    ];

    /**
     * Insert & update User Id's
     * */
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
     * This method calling the Trait FormatsDateInputs
     * @return null or string
     * Use it as formatted_transaction_date
     * */
    public function getFormattedTransactionDateAttribute()
    {
        return $this->toUserDateFormat($this->transaction_date); // Call the trait method
    }

    /**
     * Batech Records
     *
     * @return HasMany
     */
    public function batch(): HasOne
    {
        return $this->hasOne(ItemBatchTransaction::class);
    }

    public function itemBatchTransactions(): HasMany
    {
        return $this->hasMany(ItemBatchTransaction::class);
    }

    /**
     * Batech Records
     *
     * @return HasMany
     */
    public function itemSerialTransaction(): HasMany
    {
        return $this->hasMany(ItemSerialTransaction::class);
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
     * Get the tax associated with the service.
     *
     * @return BelongsTo
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    /**
     * ItemTransaction item has unit id
     *
     * @return BelongsTo
     * */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * ItemTransaction item has item id
     *
     * @return BelongsTo
     * */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * ItemTransaction item has item id
     *
     * @return BelongsTo
     * */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function itemStockTransfer(): HasOne
    {
        return $this->hasOne(ItemStockTransfer::class, 'from_item_transaction_id');
    }
    /**
     * ItemTransaction item has item id
     *
     * @return BelongsTo
     * */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
