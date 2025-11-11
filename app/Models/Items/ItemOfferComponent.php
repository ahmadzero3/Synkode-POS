<?php

namespace App\Models\Items;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemOfferComponent extends Model
{
    protected $table = 'item_offer_components';

    protected $fillable = [
        'offer_item_id',
        'component_item_id',
        'quantity',
    ];

    public function offerItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'offer_item_id');
    }

    public function componentItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'component_item_id');
    }
}
