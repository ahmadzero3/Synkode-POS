<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Company extends Model
{
    use HasFactory;

    protected $table = 'company';

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'address',
        'colored_logo',
        'light_logo',
        'signature',
        'active_sms_api',
        'state_id',
        'bank_details',
        'tax_number',
        'show_discount',
        'allow_negative_stock_billing',
        'is_enable_secondary_currency',
        'is_enable_carrier_charge',
        'restrict_to_sell_above_mrp',
        'restrict_to_sell_below_msp',
        'auto_update_sale_price',
        'auto_update_purchase_price',
        'auto_update_average_purchase_price',
        'is_item_name_unique',
        'enable_serial_tracking',
        'enable_batch_tracking',
        'is_batch_compulsory',
        'enable_mfg_date',
        'enable_exp_date',
        'enable_color',
        'enable_size',
        'enable_model',
        'tax_type',
        'enable_minimum_stock_qty',
        'minimum_stock_qty',
        'show_tax_summary',
        'enable_print_tax',
        'enable_print_discount',
        'show_signature_on_invoice',
        'show_party_due_payment',
        'show_terms_and_conditions_on_invoice',
        'terms_and_conditions',

        'number_precision',
        'quantity_precision',
        'is_enable_carrier',
        'is_enable_crm',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($company) {
            Cache::forget('company');
        });

        static::updated(function ($company) {
            Cache::forget('company');
        });

        static::deleted(function ($company) {
            Cache::forget('company');
        });
    }
}
