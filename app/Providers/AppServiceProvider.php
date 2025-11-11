<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Relations\Relation;

use App\Models\Items\Item;
use App\Models\Party\Party;
use App\Models\Party\PartyTransaction;
use App\Models\Twilio;
use App\Models\AppSettings;
use App\Models\SmtpSettings;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Sale\SaleOrder;
use App\Models\Sale\Sale;
use App\Models\Sale\SaleReturn;
use App\Models\PaymentTransaction;
use App\Models\Items\ItemTransaction;
use App\Models\Expenses\Expense;
use App\Models\CashAdjustment;
use App\Models\StockTransfer;
use App\Observers\TwilioObserver;
use App\Services\GeneralDataService;
use App\Services\SmsService;
use Illuminate\Support\Facades\Route;
use App\Services\CacheService;
use App\Enums\App;
use App\Models\Party\PartyPayment;
use App\Models\Sale\Quotation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (env('INSTALLATION_STATUS')) {
            $this->app->singleton(GeneralDataService::class, function ($app) {
                return new GeneralDataService();
            });

            $this->app->singleton(SmsService::class, function ($app) {
                return new SmsService();
            });

            $this->app->singleton('site', function () {
                $appSettings = CacheService::get('appSetting');
                return [
                    'name'              => $appSettings?->application_name,
                    'colored_logo'      => $appSettings?->colored_logo,
                ];
            });

            if (Schema::hasTable('smtp_settings')) {
                $this->app->singleton('smtp_settings', function () {
                    $smtpSettings = CacheService::get('smtpSettings');
                    return [
                        'host'       => $smtpSettings?->host,
                        'port'       => $smtpSettings?->port,
                        'username'   => $smtpSettings?->username,
                        'password'   => $smtpSettings?->password,
                        'encryption' => $smtpSettings?->encryption,
                    ];
                });
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('INSTALLATION_STATUS')) {
            if (Schema::hasTable('smtp_settings') && $this->app->bound('smtp_settings')) {
                $smtpSettings = $this->app->make('smtp_settings');

                // Extract SMTP settings from the model
                $driver = "smtp";

                // Update mail configuration with retrieved settings
                config([
                    'mail.driver'     => $driver,
                    'mail.host'       => $smtpSettings['host'],
                    'mail.port'       => $smtpSettings['port'],
                    'mail.username'   => $smtpSettings['username'],
                    'mail.password'   => $smtpSettings['password'],
                    'mail.encryption' => $smtpSettings['encryption'],
                ]);
            }
        }

        if (Schema::hasTable('customizations')) {
            View::composer('*', function ($view) {
                $custom = DB::table('customizations')
                    ->whereIn('key', [
                        'card_header_color',
                        'card_border_color',
                        'heading_color',
                    ])
                    ->pluck('value', 'key')
                    ->all();

                $view->with('custom', $custom);
            });
        }

        Relation::morphMap([
            'Item Opening'              =>  Item::class,
            'Purchase Order'            =>  PurchaseOrder::class,
            'Purchase'                  =>  Purchase::class,
            'Purchase Return'           =>  PurchaseReturn::class,
            'Sale Order'                =>  SaleOrder::class,
            'Sale'                      =>  Sale::class,
            'Sale Return'               =>  SaleReturn::class,
            'Party Opening'             =>  Party::class,
            'Payment Transaction'       =>  PaymentTransaction::class,
            'Expense'                   =>  Expense::class,
            'Party Transaction'         =>  PartyTransaction::class,
            'Item Transaction'          =>  ItemTransaction::class,
            'Cash Adjustment'           =>  CashAdjustment::class,
            'Stock Transfer'            =>  StockTransfer::class,
            'Party Payment'             =>  PartyPayment::class,
            'Quotation'                 =>  Quotation::class,
        ]);

        /**
         * Ensures that public/storage is always correctly linked.
         */
        try {
            $publicStorage = public_path('storage');
            $target = storage_path('app/public');

            if (!File::exists($target)) {
                File::makeDirectory($target, 0755, true);
            }

            if (!File::exists($publicStorage) || !is_link($publicStorage)) {
                if (File::exists($publicStorage)) {
                    File::deleteDirectory($publicStorage);
                }
                app('files')->link($target, $publicStorage);
            }
        } catch (\Exception $e) {
            // Ignore any linking errors silently
        }
    }
}
