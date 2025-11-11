<?php

namespace App\Models\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use App\Models\Tax;
use App\Models\User;
use App\Models\Unit;
use App\Models\Items\ItemCategory;
use App\Models\Items\ItemGeneralQuantity;
use App\Models\Items\ItemOfferComponent;


class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'is_service',

        'count_id',
        'item_code',
        'name',
        'description',
        'hsn',
        'sku',
        'item_category_id',
        'base_unit_id',
        'secondary_unit_id',
        'conversion_rate',

        'sale_price',
        'is_sale_price_with_tax',
        'sale_price_discount',
        'sale_price_discount_type',
        'purchase_price',
        'is_purchase_price_with_tax',
        'tax_id',
        'wholesale_price',
        'is_wholesale_price_with_tax',

        'profit_margin',

        'mrp',
        'msp',

        'tracking_type',
        'min_stock',
        'item_location',
        'image_path',
        'status',

        'brand_id',
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
     * Get the tax associated with the service.
     *
     * @return BelongsTo
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
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
     * Define the relationship between Item and Unit.
     *
     * @return BelongsTo
     * */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * Define the relationship between Item and Unit.
     *
     * @return BelongsTo
     * */
    public function secondaryUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'secondary_unit_id');
    }

    public function getFormattedQuantityAttribute()
    {
        // Retrieve the base and secondary units
        $baseUnit = $this->baseUnit;
        $secondaryUnit = $this->secondaryUnit;

        // Get the conversion factor from base to secondary unit
        $conversionRate = $baseUnit->conversion_rate;

        // Calculate quantities
        $totalQuantityInBaseUnit = $this->quantity;
        $baseUnitQuantity = floor($totalQuantityInBaseUnit);
        $secondaryUnitQuantity = ($totalQuantityInBaseUnit - $baseUnitQuantity) * $conversionRate;

        // Return formatted string
        return "{$baseUnitQuantity} {$baseUnit->name} " . round($secondaryUnitQuantity) . " {$secondaryUnit->name}";
    }

    /**
     * Define the relationship between Item and Unit.
     *
     * @return BelongsTo
     * */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    /**
     * Define the relationship between Item and Brand.
     *
     * @return BelongsTo
     * */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Define the relationship between Item Transaction & Items table.
     *
     * @return MorphMany
     */
    public function itemTransaction(): MorphMany
    {
        return $this->morphMany(ItemTransaction::class, 'transaction');
    }

    public function getTableCode()
    {
        return null;
    }

    public function itemGeneralQuantities()
    {
        return $this->hasMany(ItemGeneralQuantity::class);
    }

    public function offerComponents()
    {
        return $this->hasMany(ItemOfferComponent::class, 'offer_item_id');
    }

    public function componentItems()
    {
        return $this->belongsToMany(
            Item::class,
            'item_offer_components',
            'offer_item_id',
            'component_item_id'
        )->withPivot('quantity');
    }

    public function getIsComboAttribute(): bool
    {
        return $this->offerComponents()->exists();
    }
}
