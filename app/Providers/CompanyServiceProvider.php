<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Company;
use App\Enums\App;
use App\Enums\Date;
use App\Enums\Timezone;
use App\Services\CacheService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class CompanyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if (env('INSTALLATION_STATUS')) {
            // Bind the timezone to a service
            $this->app->singleton('company', function () {
                $company = null;

                // Only try to fetch company if table exists
                if (Schema::hasTable('company')) {
                    $company = CacheService::get('company');
                }

                $timezone = $company ? $company->timezone : Timezone::APP_DEFAULT_TIME_ZONE->value;
                $dateFormat = $company ? $company->date_format : Date::APP_DEFAULT_DATE_FORMAT->value;
                $timeFormat = $company ? $company->time_format : App::APP_DEFAULT_TIME_FORMAT->value;
                $active_sms_api = $company ? $company->active_sms_api : null;
                $isEnableCrm = $company ? $company->is_enable_crm : null;

                return [
                    'name' => $company->name ?? '',
                    'email' => $company->email ?? '',
                    'mobile' => $company->mobile ?? '',
                    'address' => $company->address ?? '',
                    'tax_number' => $company->tax_number ?? '',
                    'timezone' => $timezone,
                    'date_format' => $dateFormat,
                    'time_format' => $timeFormat,
                    'active_sms_api' => $active_sms_api,
                    'number_precision' => $company->number_precision ?? 2,
                    'quantity_precision' => $company->quantity_precision ?? 2,

                    'show_sku' => $company->show_sku ?? false,
                    'show_mrp' => $company->show_mrp ?? false,
                    'restrict_to_sell_above_mrp' => $company->restrict_to_sell_above_mrp ?? false,
                    'restrict_to_sell_below_msp' => $company->restrict_to_sell_below_msp ?? false,
                    'auto_update_sale_price' => $company->auto_update_sale_price ?? false,
                    'auto_update_purchase_price' => $company->auto_update_purchase_price ?? false,
                    'auto_update_average_purchase_price' => $company->auto_update_average_purchase_price ?? false,

                    'is_item_name_unique' => $company->is_item_name_unique ?? false,
                    'tax_type' => $company->tax_type ?? null,

                    'enable_serial_tracking' => $company->enable_serial_tracking ?? false,
                    'enable_batch_tracking' => $company->enable_batch_tracking ?? false,
                    'is_batch_compulsory' => $company->is_batch_compulsory ?? false,
                    'enable_mfg_date' => $company->enable_mfg_date ?? false,
                    'enable_exp_date' => $company->enable_exp_date ?? false,
                    'enable_color' => $company->enable_color ?? false,
                    'enable_size' => $company->enable_size ?? false,
                    'enable_model' => $company->enable_model ?? false,

                    'show_tax_summary' => $company->show_tax_summary ?? false,
                    'state_id' => $company->state_id ?? null,
                    'terms_and_conditions' => $company->terms_and_conditions ?? '',
                    'show_terms_and_conditions_on_invoice' => $company->show_terms_and_conditions_on_invoice ?? false,
                    'show_party_due_payment' => $company->show_party_due_payment ?? false,
                    'bank_details' => $company->bank_details ?? '',
                    'signature' => $company->signature ?? '',
                    'show_signature_on_invoice' => $company->show_signature_on_invoice ?? false,
                    'show_brand_on_invoice' => $company->show_brand_on_invoice ?? false,
                    'show_tax_number_on_invoice' => $company->show_tax_number_on_invoice ?? false,
                    'colored_logo' => $company->colored_logo ?? '',

                    'is_enable_crm' => $isEnableCrm,
                    'is_enable_carrier' => $company->is_enable_carrier ?? false,
                    'is_enable_carrier_charge' => $company->is_enable_carrier_charge ?? false,
                    'show_discount' => $company->show_discount ?? false,
                    'allow_negative_stock_billing' => $company->allow_negative_stock_billing ?? false,
                    'show_hsn' => $company->show_hsn ?? false,
                    'is_enable_secondary_currency' => $company->is_enable_secondary_currency ?? false,

                    // ADD THESE TWO LINES - This is the fix!
                    'enable_minimum_stock_qty' => $company->enable_minimum_stock_qty ?? false,
                    'minimum_stock_qty' => $company->minimum_stock_qty ?? 0,
                ];
            });
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (env('INSTALLATION_STATUS') && Schema::hasTable('company')) {
            // Set the default timezone
            date_default_timezone_set(app('company')['timezone']);

            // Use the timezone and date format in Carbon
            Carbon::setLocale(app('company')['timezone']);

            $carbon = new Carbon();
            $carbon->settings(['strictMode' => true]);

            // Email setup
            Config::set('mail.from.address', app('company')['email']);
            Config::set('mail.from.name', app('company')['name']);
        }
    }
}