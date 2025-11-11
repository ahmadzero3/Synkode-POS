<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppSettingsController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SmtpSettingsController;
use App\Http\Controllers\UserPermissionsController;
use App\Http\Controllers\UserRolesController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PrefixController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AssignedJobController;
use App\Http\Controllers\Party\PartyController;
use App\Http\Controllers\Party\PartyTransactionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UserPermissionsGroupController;
use App\Http\Controllers\PaymentTypesController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\StockTransferController;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\POSController;

use App\Http\Controllers\CustomizationController;

use App\Http\Controllers\Accounts\AccountController;
use App\Http\Controllers\Accounts\AccountGroupController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\Expenses\ExpenseCategoryController;
use App\Http\Controllers\Expenses\ExpenseSubcategoryController;
use App\Http\Controllers\Expenses\ExpenseController;

use App\Http\Controllers\Items\ItemController;
use App\Http\Controllers\Items\ItemTransactionController;
use App\Http\Controllers\Items\ItemCategoryController;

use App\Http\Controllers\UnitController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\Items\BrandController;
use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\Purchase\PurchaseController;

use App\Http\Controllers\Purchase\PurchaseReturnController;

use App\Http\Controllers\Payment\PurchaseOrderPaymentController;
use App\Http\Controllers\Payment\PurchasePaymentController;
use App\Http\Controllers\Payment\PurchaseReturnPaymentController;
use App\Http\Controllers\Payment\QuotationPaymentController;
use App\Http\Controllers\Reports\ItemTransactionReportController;
use App\Http\Controllers\Reports\PurchaseTransactionReportController;
use App\Http\Controllers\Reports\SaleTransactionReportController;
use App\Http\Controllers\Reports\ExpenseTransactionReportController;
use App\Http\Controllers\Reports\GstReportController;
use App\Http\Controllers\Reports\StockTransferReportController;
use App\Http\Controllers\Reports\ProfitReportController;
use App\Http\Controllers\Reports\CustomerReportController;
use App\Http\Controllers\Reports\SupplierReportController;
use App\Http\Controllers\Reports\StockReportController;
use App\Http\Controllers\Reports\StockAdjustmentReportController;

use App\Http\Controllers\Sale\SaleOrderController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\Sale\PosPendingController;

use App\Http\Controllers\Sale\SaleReturnController;

use App\Http\Controllers\Payment\SaleOrderPaymentController;
use App\Http\Controllers\Payment\SalePaymentController;
use App\Http\Controllers\Payment\SaleReturnPaymentController;
use App\Http\Controllers\Sale\QuotationController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\Transaction\CashController;
use App\Http\Controllers\Transaction\ChequeController;
use App\Http\Controllers\Transaction\BankController;
use App\Http\Controllers\Transaction\CloseCashController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AppLogController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\LicenseController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/license', [LicenseController::class, 'show'])->name('license.show');
Route::post('/license/verify', [LicenseController::class, 'verify'])->name('license.verify');

Route::get('/', function () {
    // STRICT license check - redirect if any license component is missing
    $envHasLicense = env('LICENSE_KEY_ACTIVATION') === 'true' &&
        env('LICENSE_KEY') === 'true' &&
        env('LICENSE_STATUS') === 'active' &&
        !empty(env('LICENSE_CODE'));

    $projectFile = storage_path('app/license_status.txt');
    $publicFile = 'C:\\Users\\Public\\Documents\\license_status.txt';

    $projectOk = file_exists($projectFile) && strpos(file_get_contents($projectFile), 'status=active') !== false;
    $publicOk = file_exists($publicFile) && @strpos(@file_get_contents($publicFile), 'status=active') !== false;

    // Only proceed if license is properly activated in env AND at least one file is active
    if ($envHasLicense && ($projectOk || $publicOk)) {
        // Check if user is authenticated to redirect appropriately
        if (auth()->check()) {
            return redirect('/dashboard');
        } else {
            return redirect('/login');
        }
    }

    // If license is not properly set up, redirect to activation
    return redirect()->route('license.show');
});

if (config('demo.enabled')) {
    Route::get('/app/clear_cache', [AppSettingsController::class, 'clearCache']);
}


Route::get('/migrate/db', [AppSettingsController::class, 'migrate'])->name('migrate');

Route::get('/noimage', function () {
    //If image doesn't exist, show empty image
    $imagePath = Storage::path('public/images/noimages/no-image-found.jpg');
    return response()->file($imagePath);
});

Route::get('/fevicon/{image_name?}', function ($image_name = null) {
    $imagePath = 'public/images/fevicon/' . $image_name;
    if ($image_name == null || !Storage::exists($imagePath)) {
        $imagePath = 'public/images/fevicon/default/favicon-32x32.png';
    }
    return response()->file(Storage::path($imagePath));
});

Route::get('/app/getimage/{image_name?}', function ($image_name = null) {
    $imagePath = 'public/images/app-logo/' . $image_name;

    if ($image_name == null || !Storage::exists($imagePath)) {
        $imagePath = 'public/images/app-logo/default-colored-logo.png';
    }

    return response()->file(Storage::path($imagePath));
});

Route::get('/language/switch/{id}', [LanguageController::class, 'switchLanguage'])
    ->name('language.switch'); //View

Route::get('/theme/switch/{theme_name}', [LanguageController::class, 'switchTheme'])
    ->name('theme.switch');

Route::middleware(['web', 'auth', 'timed.session'])->group(function () {
    Route::post('/sale/invoice/update-status', [App\Http\Controllers\Sale\SaleController::class, 'updateInvoiceStatus'])->name('sale.invoice.updateStatus');
    Route::post('/sale/invoice/delete', [App\Http\Controllers\Sale\SaleController::class, 'delete'])->name('sale.invoice.delete');
    Route::get('/sale/invoice/customer-display', function () {
        return view('sale.invoice.pos.customer-display');
    })->name('sale.invoice.customer-display');;
    /**
     * Dashboard
     * */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshData'])->name('dashboard.refresh');
    /**
     * Settings
     */
    Route::post('/settings/app/clear_cache', [AppSettingsController::class, 'clearCache'])->name('clear.cache');

    Route::group(['prefix' => 'settings', 'middleware' => ['can:app.settings.edit']], function () {
        Route::get('/app-settings', [AppSettingsController::class, 'index'])->name('settings.app');
        Route::post('/app/store', [AppSettingsController::class, 'store'])->name('general.store');
        Route::post('/app/store_logo', [AppSettingsController::class, 'storeLogo'])->name('logo.store');
        Route::post('/smtp/store', [SmtpSettingsController::class, 'store'])->name('smtp.store');
        Route::post('/sms/twilio/store', [AppSettingsController::class, 'storeTwilio'])->name('twilio.store');
        Route::post('/sms/vonage/store', [AppSettingsController::class, 'storeVonage'])->name('vonage.store');
        Route::post('/database/backup', [AppSettingsController::class, 'databaseBackup'])->name('database.backup');
        Route::post('/app/clear-log', [AppSettingsController::class, 'clearAppLog'])->name('clear.app.log');

        /*
         * SMS API Settings
         */
        /*Route::get('/sms/send', [SmsController::class, 'send'])->name('sms.send');
        Route::get('/sms/twilio', [SmsController::class, 'twilio'])->name('sms.twilio');
        Route::get('/sms/vonage', [SmsController::class, 'vonage'])->name('sms.vonage');
        Route::get('/sms/telesign', [SmsController::class, 'telesign']);*/


        //Notifications Testing
        Route::get('/email/notification', [EmailController::class, 'notificationTest']);


        /*Route::get('/app/getimage/{image_name?}', function($image_name=null){
            $imagePath = 'public/images/app-logo/' . $image_name;
            if ($image_name==null || !Storage::exists($imagePath)) {
                return redirect('noimage');
            }
            return response()->file(Storage::path($imagePath));
        });*/
    });

    /**
     * Company Settings
     */
    Route::group(['prefix' => 'company', 'middleware' => ['can:company.edit']], function () {

        Route::get('/', [CompanyController::class, 'index'])->name('company');
        Route::post('/update', [CompanyController::class, 'update'])->name('company.update');
        Route::post('/general/update', [CompanyController::class, 'generalUpdate'])->name('company.general.update');
        Route::post('/item/update', [CompanyController::class, 'itemUpdate'])->name('company.item.update');
        Route::post('/prefix/update', [PrefixController::class, 'update'])->name('prefix.update');
        Route::post('/print/update', [CompanyController::class, 'printUpdate'])->name('company.print.update');
        Route::post('/module/update', [CompanyController::class, 'moduleUpdate'])->name('company.module.update');

        Route::get('/getimage/{image_name?}', function ($image_name = null) {
            $imagePath = 'public/images/company/' . $image_name;
            if ($image_name == null || !Storage::exists($imagePath)) {
                return redirect('/noimage');
            }
            return response()->file(Storage::path($imagePath));
        });

        Route::get('/signature/getimage/{image_name?}', function ($image_name = null) {
            $imagePath = 'public/images/signature/' . $image_name;
            if ($image_name == null || !Storage::exists($imagePath)) {
                return redirect('/noimage');
            }
            return response()->file(Storage::path($imagePath));
        });

        Route::get('/sponsers/getimage/{image_name?}', function ($image_name = 'sponsers.png') {
            $imagePath = 'public/images/sponsers/' . $image_name;
            if ($image_name == null || !Storage::exists($imagePath)) {
                return redirect('/noimage');
            }
            return response()->file(Storage::path($imagePath));
        });
    });

    /**
     * SMS and SMS Templates
     * */
    Route::group(['prefix' => 'sms'], function () {
        Route::get('/create', [SmsController::class, 'create'])
            ->middleware('can:sms.create')
            ->name('sms.create'); //View
        Route::post('/send', [SmsController::class, 'send'])->name('sms.send');

        Route::get('/template/create', [SmsTemplateController::class, 'create'])
            ->middleware('can:sms.template.create')
            ->name('sms.template.create'); //View
        Route::post('/template/store', [SmsTemplateController::class, 'store'])->name('sms.template.store');

        Route::get('/template/edit/{id}', [SmsTemplateController::class, 'edit'])
            ->middleware('can:sms.template.edit')
            ->name('sms.template.edit'); //Edit
        Route::put('/template/update', [SmsTemplateController::class, 'update'])->name('sms.template.update'); //Update
        Route::get('/template/list', [SmsTemplateController::class, 'list'])
            ->middleware('can:sms.template.view')
            ->name('sms.template.list'); //List
        Route::get('/template/datatable-list', [SmsTemplateController::class, 'datatableList'])->name('sms.template.datatable.list'); //Datatable List
        Route::post('/delete/', [SmsTemplateController::class, 'delete'])
            ->middleware('can:sms.template.delete')
            ->name('sms.template.delete'); //delete operation
    });

    /**
     * Email and Email Templates
     * */
    Route::group(['prefix' => 'email'], function () {
        Route::get('/create', [EmailController::class, 'create'])
            ->middleware('can:email.create')
            ->name('email.create'); //View
        Route::post('/send', [EmailController::class, 'send'])->name('email.send');

        Route::get('/template/create', [EmailTemplateController::class, 'create'])
            ->middleware('can:email.template.create')
            ->name('email.template.create'); //View
        Route::post('/template/store', [EmailTemplateController::class, 'store'])->name('email.template.store');

        Route::get('/template/edit/{id}', [EmailTemplateController::class, 'edit'])
            ->middleware('can:email.template.edit')
            ->name('email.template.edit'); //Edit
        Route::put('/template/update', [EmailTemplateController::class, 'update'])->name('email.template.update'); //Update
        Route::get('/template/list', [EmailTemplateController::class, 'list'])
            ->middleware('can:email.template.view')
            ->name('email.template.list'); //List
        Route::get('/template/datatable-list', [EmailTemplateController::class, 'datatableList'])->name('email.template.datatable.list'); //Datatable List
        Route::post('/delete/', [EmailTemplateController::class, 'delete'])
            ->middleware('can:email.template.delete')
            ->name('email.template.delete'); //delete operation
    });

    /**
     * Party
     * */
    Route::group(['prefix' => 'party'], function () {
        Route::get('/{partyType}/create', [PartyController::class, 'create'])
            ->middleware('check.party.permission')
            ->name('party.create'); //View
        Route::get('/{partyType}/edit/{id}', [PartyController::class, 'edit'])
            ->middleware('check.party.permission')
            ->name('party.edit'); //Edit
        Route::put('/update', [PartyController::class, 'store'])->name('party.update'); //Update
        Route::get('/{partyType}/list', [PartyController::class, 'list'])
            ->middleware('check.party.permission')
            ->name('party.list'); //List
        Route::get('/{partyType}/datatable-list', [PartyController::class, 'datatableList'])->name('party.datatable.list'); //Datatable List
        Route::post('/store', [PartyController::class, 'store'])->name('party.store'); //Save operation
        Route::post('/delete/', [PartyController::class, 'delete'])
            ->middleware('check.party.permission')
            ->name('party.delete'); //delete operation
        Route::get('/{partyType}/transaction/{id}', [PartyTransactionController::class, 'list'])
            ->middleware('check.party.permission')
            ->name('party.transaction.list');
        Route::get('/transaction-list', [PartyTransactionController::class, 'datatableList']); //Datatable List

        Route::get('/payment/{partyType}/{id}', [PartyTransactionController::class, 'partyPayment'])
            ->middleware('check.party.permission')
            ->name('party.payment.create');
        Route::get('/due-payment/get-records/{id}', [PartyTransactionController::class, 'getDueRecords']);
        Route::post('/payment/store', [PartyTransactionController::class, 'storePartyPayment'])->name('store.party.payment'); //Save operation
        Route::get('/payment-receipt/print/{id}', [PartyTransactionController::class, 'printPartyPayment'])
            ->middleware('check.party.permission');
        Route::get('/payment-receipt/pdf/{id}', [PartyTransactionController::class, 'pdfPartyPayment'])
            ->middleware('check.party.permission');
        //get payment history
        Route::get('/payment-history/{id}', [PartyTransactionController::class, 'getPartyPaymentHistoryData'])
            ->middleware('check.party.permission');
        Route::get('/payment-delete/{id}', [PartyTransactionController::class, 'deletePartyPayment'])
            ->middleware('check.party.permission');
        /**
         * Ajax selection box search
         * */
        Route::get('/ajax/get-list', [PartyController::class, 'getAjaxSearchBarList']);
    });

    /**
     * Customer
     * */
    Route::group(['prefix' => 'customer'], function () {
        Route::get('/create', [CustomerController::class, 'create'])
            ->middleware('can:customer.create')
            ->name('customer.create'); //View
        Route::get('/edit/{id}', [CustomerController::class, 'edit'])
            ->middleware('can:customer.edit')
            ->name('customer.edit'); //Edit
        Route::put('/update', [CustomerController::class, 'update'])->name('customer.update'); //Update
        Route::get('/list', [CustomerController::class, 'list'])
            ->middleware('can:customer.view')
            ->name('customer.list'); //List
        Route::get('/datatable-list', [CustomerController::class, 'datatableList'])->name('customer.datatable.list'); //Datatable List
        Route::post('/store', [CustomerController::class, 'store'])->name('customer.store'); //Save operation
        Route::post('/delete/', [CustomerController::class, 'delete'])
            ->middleware('can:customer.delete')
            ->name('customer.delete'); //delete operation
    });

    // Register Routes
    Route::group(['prefix' => 'register'], function () {
        Route::get('/create', [RegisterController::class, 'create'])
            ->middleware('can:register.create')
            ->name('register.create');
        Route::get('/edit/{id}', [RegisterController::class, 'edit'])
            ->middleware('can:register.edit')
            ->name('register.edit');
        Route::put('/update', [RegisterController::class, 'update'])->name('register.update');
        Route::get('/list', [RegisterController::class, 'list'])
            ->middleware('can:register.view')
            ->name('register.list');
        Route::get('/datatable-list', [RegisterController::class, 'datatableList'])->name('register.datatable.list');
        Route::post('/store', [RegisterController::class, 'store'])->name('register.store');
        Route::post('/delete/', [RegisterController::class, 'delete'])
            ->middleware('can:register.delete')
            ->name('register.delete');

        Route::get('/ajax/get-list', [RegisterController::class, 'ajaxList'])->name('register.ajax.index');

        Route::get('/user/ajax/cashiers', [UserController::class, 'ajaxCashiers']);
    });

    /**
     * Order
     * */
    Route::group(['prefix' => 'order'], function () {
        Route::get('/create', [OrderController::class, 'create'])
            ->middleware('can:order.create')
            ->name('order.create'); //View
        Route::post('/store', [OrderController::class, 'store'])->name('order.store'); //Save operation

        Route::get('/edit/{id}', [OrderController::class, 'edit'])
            ->middleware('can:order.edit')
            ->name('order.edit'); //Edit
        Route::put('/update', [OrderController::class, 'update'])->name('order.update'); //Update

        Route::get('/list', [OrderController::class, 'list'])
            ->middleware('can:order.view')
            ->name('order.list'); //List
        Route::get('/datatable-list', [OrderController::class, 'datatableList'])->name('order.datatable.list'); //Datatable List

        Route::post('/delete/', [OrderController::class, 'delete'])
            ->middleware('can:order.delete')
            ->name('order.delete'); //delete operation

        Route::get('/payment/delete', [OrderController::class, 'deletePayment'])
            ->middleware('can:order.delete')
            ->name('order.payment.delete'); //delete operation

        Route::get('/receipt/{id}', [OrderController::class, 'receipt'])
            ->middleware('can:order.create, order.edit, order.view')
            ->name('order.receipt');

        Route::get('/timeline/{id}', [OrderController::class, 'timeline'])
            ->middleware('can:order.create, order.edit, order.view')
            ->name('order.timeline');

        Route::put('/update', [OrderController::class, 'update'])->name('order.update'); //Update

        /*Ajax Returns*/
        Route::get('/get_service_order_records', [OrderController::class, 'getOrderRecords']);
    });

    /**
     * Order Scheduler
     * */
    Route::group(['prefix' => 'schedule'], function () {

        Route::get('/edit/{id}', [ScheduleController::class, 'edit'])
            ->middleware('can:schedule.edit')
            ->name('schedule.edit'); //Edit
        Route::put('/update', [ScheduleController::class, 'update'])->name('schedule.update'); //Update

        Route::get('/list', [ScheduleController::class, 'list'])
            ->middleware('can:schedule.view')
            ->name('schedule.list'); //List
        Route::get('/datatable-list', [ScheduleController::class, 'datatableList'])->name('schedule.datatable.list'); //Datatable List
        /*Ajax Returns*/
        Route::get('/get_service_order_records', [ScheduleController::class, 'getOrderRecords']);
    });

    /**
     * Assigned Jobs
     * */
    Route::group(['prefix' => 'assigned-jobs'], function () {

        Route::get('/update/{id}', [AssignedJobController::class, 'edit'])
            ->middleware('can:assigned_jobs.edit')
            ->name('assigned_jobs.edit'); //Edit
        Route::put('/update', [AssignedJobController::class, 'update'])->name('assigned_jobs.update'); //Update

        Route::get('/list', [AssignedJobController::class, 'list'])
            ->middleware('can:assigned_jobs.view')
            ->name('assigned_jobs.list'); //List
        Route::get('/datatable-list', [AssignedJobController::class, 'datatableList'])->name('assigned_jobs.datatable.list'); //Datatable List
    });

    /**
     * Tax
     * */
    Route::group(['prefix' => 'tax'], function () {
        Route::get('/create', [TaxController::class, 'create'])
            ->middleware('can:tax.create')
            ->name('tax.create'); //View
        Route::get('/edit/{id}', [TaxController::class, 'edit'])
            ->middleware('can:tax.edit')
            ->name('tax.edit'); //Edit
        Route::put('/update', [TaxController::class, 'update'])->name('tax.update'); //Update
        Route::get('/list', [TaxController::class, 'list'])
            ->middleware('can:tax.view')
            ->name('tax.list'); //List
        Route::get('/datatable-list', [TaxController::class, 'datatableList'])->name('tax.datatable.list'); //Datatable List
        Route::post('/store', [TaxController::class, 'store'])->name('tax.store'); //Save operation
        Route::post('/delete/', [TaxController::class, 'delete'])
            ->middleware('can:tax.delete')
            ->name('tax.delete'); //delete operation
    });

    /**
     * Warehouse
     * */
    Route::group(['prefix' => 'warehouse'], function () {
        Route::get('/create', [WarehouseController::class, 'create'])
            ->middleware('can:warehouse.create')
            ->name('warehouse.create'); //View
        Route::get('/edit/{id}', [WarehouseController::class, 'edit'])
            ->middleware('can:warehouse.edit')
            ->name('warehouse.edit'); //Edit
        Route::put('/update', [WarehouseController::class, 'update'])->name('warehouse.update'); //Update
        Route::get('/list', [WarehouseController::class, 'list'])
            ->middleware('can:warehouse.view')
            ->name('warehouse.list'); //List
        Route::get('/datatable-list', [WarehouseController::class, 'datatableList'])->name('warehouse.datatable.list'); //Datatable List
        Route::post('/store', [WarehouseController::class, 'store'])->name('warehouse.store'); //Save operation
        Route::post('/delete/', [WarehouseController::class, 'delete'])
            ->middleware('can:warehouse.delete')
            ->name('warehouse.delete'); //delete operation
        /**
         * Load Items for search box for Select2
         * */
        Route::get('/select2/ajax/get-list', [WarehouseController::class, 'getAjaxWarehouseSearchBarList']);
    });

    /**
     * Stock Transfer
     * */
    Route::group(['prefix' => 'stock-transfer'], function () {
        Route::get('/create', [StockTransferController::class, 'create'])
            ->middleware('can:stock_transfer.create')
            ->name('stock_transfer.create'); //View
        Route::get('/details/{id}', [StockTransferController::class, 'details'])
            ->middleware('can:stock_transfer.view')
            ->name('stock_transfer.details');
        Route::get('/edit/{id}', [StockTransferController::class, 'edit'])
            ->middleware('can:stock_transfer.edit')
            ->name('stock_transfer.edit'); //Edit
        Route::put('/update', [StockTransferController::class, 'store'])->name('stock_transfer.update'); //Update
        Route::get('/list', [StockTransferController::class, 'list'])
            ->middleware('can:stock_transfer.view')
            ->name('stock_transfer.list'); //List
        Route::get('/datatable-list', [StockTransferController::class, 'datatableList'])->name('stock_transfer.datatable.list'); //Datatable List
        Route::post('/store', [StockTransferController::class, 'store'])->name('stock_transfer.store'); //Save operation
        Route::post('/delete/', [StockTransferController::class, 'delete'])
            ->middleware('can:stock_transfer.delete')
            ->name('stock_transfer.delete'); //delete operation

        Route::get('/print/{id}', [StockTransferController::class, 'print'])
            ->middleware('can:stock_transfer.view')
            ->name('stock_transfer.print');

        Route::get('/pdf/{id}', [StockTransferController::class, 'generatePdf'])
            ->middleware('can:stock_transfer.view')
            ->name('stock_transfer.pdf');
    });

    /**
     * Stock Adjustment
     * */
    Route::group(['prefix' => 'stock-adjustment'], function () {
        Route::get('/create', [StockAdjustmentController::class, 'create'])
            ->middleware('can:stock_adjustment.create')
            ->name('stock_adjustment.create'); //View
        Route::get('/details/{id}', [StockAdjustmentController::class, 'details'])
            ->middleware('can:stock_adjustment.view')
            ->name('stock_adjustment.details');
        Route::get('/edit/{id}', [StockAdjustmentController::class, 'edit'])
            ->middleware('can:stock_adjustment.edit')
            ->name('stock_adjustment.edit'); //Edit
        Route::put('/update', [StockAdjustmentController::class, 'store'])->name('stock_adjustment.update'); //Update
        Route::get('/list', [StockAdjustmentController::class, 'list'])
            ->middleware('can:stock_adjustment.view')
            ->name('stock_adjustment.list'); //List
        Route::get('/datatable-list', [StockAdjustmentController::class, 'datatableList'])->name('stock_adjustment.datatable.list'); //Datatable List
        Route::post('/store', [StockAdjustmentController::class, 'store'])->name('stock_adjustment.store'); //Save operation
        Route::post('/delete/', [StockAdjustmentController::class, 'delete'])
            ->middleware('can:stock_adjustment.delete')
            ->name('stock_adjustment.delete'); //delete operation

        Route::get('/print/{id}', [StockAdjustmentController::class, 'print'])
            ->middleware('can:stock_adjustment.view')
            ->name('stock_adjustment.print');

        Route::get('/pdf/{id}', [StockAdjustmentController::class, 'generatePdf'])
            ->middleware('can:stock_adjustment.view')
            ->name('stock_adjustment.pdf');
    });

    /**
     * Customization
     */
    Route::group(['prefix' => 'customize'], function () {
        Route::get('/edit', [CustomizationController::class, 'edit'])
            ->middleware('can:customize.edit')
            ->name('customize.edit');

        Route::put('/update', [CustomizationController::class, 'update'])
            ->middleware('can:customize.edit')
            ->name('customize.update');

        Route::delete('/image/delete', [CustomizationController::class, 'deleteImage'])
            ->middleware('can:customize.edit')
            ->name('customize.image.delete');
    });

    Route::get('/api/customization/images', [CustomizationController::class, 'getCustomerDisplayImages'])
        ->name('api.customization.images');

    /**
     * Application Logs
     * */
    Route::group(['prefix' => 'app-log'], function () {
        Route::get('/list', [AppLogController::class, 'list'])
            ->middleware('can:app.log.view')
            ->name('app.log.list');

        Route::get('/datatable-list', [AppLogController::class, 'datatableList'])
            ->middleware('can:app.log.view')
            ->name('app.log.datatable.list');

        Route::get('/{id}', [AppLogController::class, 'show'])
            ->middleware('can:app.log.view')
            ->name('app.log.show');

        Route::post('/delete', [AppLogController::class, 'delete'])
            ->middleware('can:app.log.view')
            ->name('app.log.delete');

        Route::post('/clear-all', [AppLogController::class, 'clearAll'])
            ->middleware('can:app.log.clear_all')
            ->name('app.log.clear.all');
    });

    Route::middleware(['auth', 'permission:database.backup.view'])->group(function () {
        Route::get('/database-backup/list', [DatabaseBackupController::class, 'list'])->name('database.backup.list');
        Route::post('/database-backup/create', [DatabaseBackupController::class, 'createBackup'])->name('database.backup.create');
        Route::get('/database-backup/download/{filename}', [DatabaseBackupController::class, 'downloadBackup'])->name('database.backup.download');
        Route::post('/database-backup/delete', [DatabaseBackupController::class, 'deleteBackup'])->name('database.backup.delete');
    });

    Route::get('/database-backup/datatable-list', [DatabaseBackupController::class, 'datatableList'])->name('database.backup.datatable.list');


    /**
     * Languages
     * */
    Route::group(['prefix' => 'language'], function () {
        Route::get('/create', [LanguageController::class, 'create'])
            ->middleware('can:language.create')
            ->name('language.create'); //View
        Route::get('/edit/{id}', [LanguageController::class, 'edit'])
            ->middleware('can:language.edit')
            ->name('language.edit'); //Edit
        Route::put('/update', [LanguageController::class, 'update'])->name('language.update'); //Update
        Route::get('/list', [LanguageController::class, 'list'])
            ->middleware('can:language.view')
            ->name('language.list'); //List
        Route::get('/datatable-list', [LanguageController::class, 'datatableList'])->name('language.datatable.list'); //Datatable List
        Route::post('/store', [LanguageController::class, 'store'])->name('language.store'); //Save operation
        Route::post('/delete/', [LanguageController::class, 'delete'])
            ->middleware('can:language.delete')
            ->name('language.delete'); //delete operation
    });

    /**
     * PaymentTypes
     * */
    Route::group(['prefix' => 'payment-type'], function () {
        Route::get('/create', [PaymentTypesController::class, 'create'])
            ->middleware('can:payment.type.create')
            ->name('payment.type.create'); //View
        Route::get('/edit/{id}', [PaymentTypesController::class, 'edit'])
            ->middleware('can:payment.type.edit')
            ->name('payment.type.edit'); //Edit
        Route::put('/update', [PaymentTypesController::class, 'update'])->name('payment.type.update'); //Update
        Route::get('/list', [PaymentTypesController::class, 'list'])
            ->middleware('can:payment.type.view')
            ->name('payment.types.list'); //List
        Route::get('/datatable-list', [PaymentTypesController::class, 'datatableList'])->name('payment.type.datatable.list'); //Datatable List
        Route::post('/store', [PaymentTypesController::class, 'store'])->name('payment.type.store'); //Save operation
        Route::post('/delete/', [PaymentTypesController::class, 'delete'])
            ->middleware('can:payment.type.delete')
            ->name('payment.type.delete'); //delete operation
        /**
         * Ajax selection box search
         * */
        Route::get('/ajax/get-list', [PaymentTypesController::class, 'getAjaxSearchBarList']);
    });

    /**
     * Units
     * */
    Route::group(['prefix' => 'unit'], function () {
        Route::get('/create', [UnitController::class, 'create'])
            ->middleware('can:unit.create')
            ->name('unit.create'); //View
        Route::get('/edit/{id}', [UnitController::class, 'edit'])
            ->middleware('can:unit.edit')
            ->name('unit.edit'); //Edit
        Route::put('/update', [UnitController::class, 'update'])->name('unit.update'); //Update
        Route::get('/list', [UnitController::class, 'list'])
            ->middleware('can:unit.view')
            ->name('unit.list'); //List
        Route::get('/datatable-list', [UnitController::class, 'datatableList'])->name('unit.datatable.list'); //Datatable List
        Route::post('/store', [UnitController::class, 'store'])->name('unit.store'); //Save operation
        Route::post('/delete/', [UnitController::class, 'delete'])
            ->middleware('can:unit.delete')
            ->name('unit.delete'); //delete operation

    });

    /**
     * services
     * */
    Route::group(['prefix' => 'service'], function () {
        Route::get('/create', [ServiceController::class, 'create'])
            ->middleware('can:service.create')
            ->name('service.create'); //View
        Route::get('/edit/{id}', [ServiceController::class, 'edit'])
            ->middleware('can:service.edit')
            ->name('service.edit'); //Edit
        Route::put('/update', [ServiceController::class, 'update'])->name('service.update'); //Update
        Route::get('/list', [ServiceController::class, 'list'])
            ->middleware('can:service.view')
            ->name('service.list'); //List
        Route::get('/datatable-list', [ServiceController::class, 'datatableList'])->name('service.datatable.list'); //Datatable List
        Route::post('/store', [ServiceController::class, 'store'])->name('service.store'); //Save operation
        Route::post('/delete/', [ServiceController::class, 'delete'])->middleware('can:service.delete')->name('service.delete'); //delete operation
        Route::get('/getimage/{image_name?}', function ($image_name = null) {
            $imagePath = 'public/images/services/' . $image_name;
            if ($image_name == null || !Storage::exists($imagePath)) {
                return redirect('/users/noimage');
            }
            return response()->file(Storage::path($imagePath));
        });
        Route::get('/noimage', function () {
            //If image doesn't exist, show empty image
            $imagePath = base_path('storage/app/public/images/noimages/no-image-found.jpg');
            return response()->file($imagePath);
        });

        /*Ajax Returns*/
        Route::get('/get_service_records', [ServiceController::class, 'getRecords']);
    });

    Route::group(['prefix' => 'users'], function () {

        /**
         * Users Master
         */
        Route::get('/create', [UserController::class, 'create'])
            ->middleware('can:user.create')
            ->name('user.create'); //View
        Route::get('/edit/{id}', [UserController::class, 'edit'])
            ->middleware('can:user.edit')
            ->name('user.edit'); //Edit
        Route::put('/update', [UserController::class, 'update'])->name('user.update'); //Update
        Route::get('/list', [UserController::class, 'list'])
            ->middleware('can:user.view')
            ->name('users.list'); //List
        Route::get('/datatable-list', [UserController::class, 'datatableList'])->name('user.datatable.list'); //Datatable List
        Route::post('/store', [UserController::class, 'store'])->name('user.store'); //Save operation
        Route::post('/delete/', [UserController::class, 'delete'])->middleware('can:user.delete')->name('user.delete'); //delete operation
        Route::get('/getimage/{image_name?}', function ($image_name = null) {
            $imagePath = 'public/images/avatar/' . $image_name;
            if ($image_name == null || !Storage::exists($imagePath)) {
                return redirect('/users/noimage');
            }
            return response()->file(Storage::path($imagePath));
        });
        Route::get('/noimage', function () {
            //If image doesn't exist, show empty image
            $imagePath = base_path('storage/app/public/images/avatar/noimage/default.png');
            return response()->file($imagePath);
        });
    });

    // Session routes
    Route::group(['prefix' => 'session', 'middleware' => ['auth', 'can:session.view']], function () {
        Route::get('/list', [SessionController::class, 'list'])->name('session.list');
        Route::get('/create', [SessionController::class, 'create'])->name('session.create');
        Route::post('/store', [SessionController::class, 'store'])->name('session.store');
        Route::get('/edit/{id}', [SessionController::class, 'edit'])->name('session.edit');
        Route::put('/update', [SessionController::class, 'update'])->name('session.update');
        Route::get('/datatable-list', [SessionController::class, 'datatableList'])->name('session.datatable.list');
        Route::post('/delete/', [SessionController::class, 'delete'])->name('session.delete');
    });

    Route::get('/session/user/ajax/all-users', [UserController::class, 'ajaxAllUsers']);

    /**
     * User Profile
     * */
    Route::group(['prefix' => 'profile', 'middleware' => ['can:profile.edit']], function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('user.profile'); //View
        Route::put('/update', [ProfileController::class, 'updateProfile'])->name('user.profile.update'); //Update
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('user.profile.password'); //Update
    });
    /**
     * Roles and Permissions
     */
    Route::group(['prefix' => 'role-and-permission'], function () {

        /**
         * Roles Master
         */
        Route::get('/role/create', [UserRolesController::class, 'createRole'])
            ->middleware('can:role.create')
            ->name('role.create'); //View
        Route::get('/role/edit/{id}', [UserRolesController::class, 'editRole'])
            ->middleware('can:role.edit')
            ->name('role.edit'); //Edit
        Route::put('/role/update', [UserRolesController::class, 'updateRole'])->name('role.update'); //Update
        Route::get('/role/list', [UserRolesController::class, 'listRoles'])
            ->middleware('can:role.view')
            ->name('roles.list'); //List
        Route::get('/role/datatable-list', [UserRolesController::class, 'datatableList'])->name('role.datatable.list'); //Datatable List
        Route::post('/role/store', [UserRolesController::class, 'storeRole'])->name('role.store'); //Save operation
        Route::post('/role/delete/', [UserRolesController::class, 'deleteRole'])
            ->middleware('can:role.delete')
            ->name('role.delete'); //delete operation

        /**
         * Permissions Master
         */
        Route::get('/permission/create', [UserPermissionsController::class, 'createPermission'])
            ->middleware('can:permission.create')
            ->name('permission.create'); //View
        Route::get('/permission/edit/{id}', [UserPermissionsController::class, 'editPermission'])
            ->middleware('can:permission.edit')
            ->name('permission.edit'); //Edit
        Route::put('/permission/update', [UserPermissionsController::class, 'updatePermission'])->name('permission.update'); //Update
        Route::get('/permission/list', [UserPermissionsController::class, 'listPermissions'])
            ->middleware('can:permission.view')
            ->name('permission.list'); //List
        Route::get('/permission/datatable-list', [UserPermissionsController::class, 'datatableList'])->name('permission.datatable.list'); //Datatable List
        Route::post('/permission/store', [UserPermissionsController::class, 'storePermission'])->name('permission.store'); //Save operation
        Route::post('/permission/delete/', [UserPermissionsController::class, 'deletePermission'])
            ->middleware('can:permission.delete')
            ->name('permission.delete'); //delete operation

        /**
         * Permission Group Master
         */
        Route::get('/group/create', [UserPermissionsGroupController::class, 'createGroup'])
            ->middleware('can:permission.group.create')
            ->name('permission.group.create'); //View
        Route::get('/group/edit/{id}', [UserPermissionsGroupController::class, 'editGroup'])
            ->middleware('can:permission.group.edit')
            ->name('permission.group.edit'); //Edit
        Route::put('/group/update', [UserPermissionsGroupController::class, 'updateGroup'])->name('permission.group.update'); //Update
        Route::get('/group/list', [UserPermissionsGroupController::class, 'listGroups'])
            ->middleware('can:permission.group.view')
            ->name('permission.group.list'); //List
        Route::get('/group/datatable-list', [UserPermissionsGroupController::class, 'datatableList'])->name('permission.group.datatable.list'); //Datatable List
        Route::post('/group/store', [UserPermissionsGroupController::class, 'storeGroup'])->name('permission.store.group'); //Store
        Route::post('/group/delete', [UserPermissionsGroupController::class, 'deleteGroup'])
            ->middleware('can:permission.group.delete')
            ->name('permission.group.delete'); //Delete
    });

    /**
     * Reports
     * */
    Route::group(['prefix' => 'report'], function () {

        Route::get('/order', function () {
            return view('report.order');
        })->middleware('can:report.order')
            ->name('report.order'); //View

        Route::post('/order/get-orders', [OrderController::class, 'getOrderRecordsForReport'])->name('report.order.ajax');

        Route::get('/job-status', function () {
            return view('report.job-status');
        })->middleware('can:report.job.status')
            ->name('report.job.status'); //View

        Route::post('/job-status/get-records', [ScheduleController::class, 'getJobStatusRecords'])->name('report.job.status.ajax');

        Route::get('/order/payment', function () {
            return view('report.order-payment');
        })->middleware('can:report.order.payment')
            ->name('report.order.payment'); //View

        Route::post('/order/payment/get-records', [OrderController::class, 'getOrderPaymentRecordsForReport'])->name('report.order.payment.ajax');

        Route::get('/account/balance-sheet', [AccountController::class, 'viewBalanceSheetReport'])
            ->middleware('can:report.balance_sheet')
            ->name('report.balance_sheet'); //View

        Route::post('/account/balance-sheet/get-records', [AccountController::class, 'getAccountBalanceSheetRecordsForReport'])->name('report.account.balance-sheet.ajax');


        Route::get('/account/trial-balance', [AccountController::class, 'viewTrialSheetReport'])
            ->middleware('can:report.trial_balance')
            ->name('report.trial_balance'); //View

        Route::post('/account/trial-balance/get-records', [AccountController::class, 'getAccountTrialBalanceRecordsForReport'])->name('report.account.trial-balance.ajax');

        Route::get('/api/account-tree', [AccountController::class, 'getTreeData']);

        /*Report -> Item Transaction -> Batch  */
        Route::get('/item-transaction/batch', function () {
            return view('report.item-transaction.batch');
        })->middleware('can:report.item.transaction.batch')
            ->name('report.item.transaction.batch'); //View

        Route::post('/item-transaction/batch/get-records', [ItemTransactionReportController::class, 'getBatchWiseTransactionRecords'])->name('report.item.transaction.batch.ajax');

        /*Report -> Item Transaction -> Serial  */
        Route::get('/item-transaction/serial', function () {
            return view('report.item-transaction.serial');
        })->middleware('can:report.item.transaction.serial')
            ->name('report.item.transaction.serial'); //View

        Route::post('/item-transaction/serial/get-records', [ItemTransactionReportController::class, 'getSerialWiseTransactionRecords'])->name('report.item.transaction.serial.ajax');

        /*Report -> Item Transaction -> General  */
        Route::get('/item-transaction/general', function () {
            return view('report.item-transaction.general');
        })->middleware('can:report.item.transaction.general')
            ->name('report.item.transaction.general'); //View

        Route::post('/item-transaction/general/get-records', [ItemTransactionReportController::class, 'getGeneralTransactionRecords'])->name('report.item.transaction.general.ajax');

        /*Report -> Purchase -> Purchase  */
        Route::get('/purchase', function () {
            return view('report.purchase.purchase');
        })->middleware('can:report.purchase')
            ->name('report.purchase'); //View

        Route::post('/purchase/get-records', [PurchaseTransactionReportController::class, 'getPurchaseRecords'])->name('report.purchase.ajax');

        /*Report -> Purchase -> Item Purchase  */
        Route::get('/purchase/item', function () {
            return view('report.purchase.item-purchase');
        })->middleware('can:report.purchase.item')
            ->name('report.purchase.item'); //View

        Route::post('/purchase/item/get-records', [PurchaseTransactionReportController::class, 'getPurchaseItemRecords'])->name('report.purchase.item.ajax');

        /*Report -> Purchase -> Payment  */
        Route::get('/purchase/payment', function () {
            return view('report.purchase.payment');
        })->middleware('can:report.purchase.payment')
            ->name('report.purchase.payment'); //View

        Route::post('/purchase/payment/get-records', [PurchaseTransactionReportController::class, 'getPurchasePaymentRecords'])->name('report.purchase.payment.ajax');

        /*Report -> Sale -> Sale  */
        Route::get('/sale', function () {
            return view('report.sale.sale');
        })->middleware('can:report.sale')
            ->name('report.sale'); //View

        Route::post('/sale/get-records', [SaleTransactionReportController::class, 'getSaleRecords'])->name('report.sale.ajax');

        /*Report -> Sale -> Item Sale  */
        Route::get('/sale/item', function () {
            return view('report.sale.item-sale');
        })->middleware('can:report.sale.item')
            ->name('report.sale.item'); //View

        Route::post('/sale/item/get-records', [SaleTransactionReportController::class, 'getSaleItemRecords'])->name('report.sale.item.ajax');

        /*Report -> Sale -> Payment  */
        Route::get('/sale/payment', function () {
            return view('report.sale.payment');
        })->middleware('can:report.sale.payment')
            ->name('report.sale.payment'); //View

        Route::post('/sale/payment/get-records', [SaleTransactionReportController::class, 'getSalePaymentRecords'])->name('report.sale.payment.ajax');

        /*Report -> Expired Items  */
        Route::get('/expired/item', function () {
            return view('report.expired-item');
        })->middleware('can:report.expired.item')
            ->name('report.expired.item'); //View

        Route::post('/expired/item/get-records', [ItemTransactionReportController::class, 'getExpiredItemRecords'])->name('report.expired.item.ajax');

        /*Report -> Reorder Items  */
        Route::get('/reorder/item', function () {
            return view('report.reorder-item');
        })->middleware('can:report.reorder.item')
            ->name('report.reorder.item'); //View

        Route::post('/reorder/item/get-records', [ItemTransactionReportController::class, 'getReorderItemRecords'])->name('report.reorder.item.ajax');

        /*Report -> Profit and Loss  */
        Route::get('/profit-and-loss', function () {
            return view('report.profit-and-loss.profit');
        })->middleware('can:report.profit_and_loss')
            ->name('report.profit_and_loss'); //View

        Route::post('/profit-and-loss/get-records', [ProfitReportController::class, 'getProfitRecords'])->name('report.profit_and_loss.ajax');

        Route::post('/invoice-wise-profit-and-loss/get-records', [ProfitReportController::class, 'getInvoiceWiseProfitRecords'])->name('report.invoice_wise_profit_and_loss.ajax');

        Route::post('/item-wise-profit-and-loss/get-records', [ProfitReportController::class, 'getItemWiseProfitRecords'])->name('report.item_wise_profit_and_loss.ajax');

        Route::post('/brand-wise-profit-and-loss/get-records', [ProfitReportController::class, 'getBrandWiseProfitRecords'])->name('report.brand_wise_profit_and_loss.ajax');

        Route::post('/category-wise-profit-and-loss/get-records', [ProfitReportController::class, 'getCategoryWiseProfitRecords'])->name('report.category_wise_profit_and_loss.ajax');

        Route::post('/customer-wise-profit-and-loss/get-records', [ProfitReportController::class, 'getCustomerWiseProfitRecords'])->name('report.customer_wise_profit_and_loss.ajax');

        /*Report -> Expense -> Expense  */
        Route::get('/expense', function () {
            return view('report.expense.expense');
        })->middleware('can:report.expense')
            ->name('report.expense'); //View

        Route::post('/expense/get-records', [ExpenseTransactionReportController::class, 'getExpenseRecords'])->name('report.expense.ajax');

        /*Report -> Expense -> Item Expense  */
        Route::get('/expense/item', function () {
            return view('report.expense.item-expense');
        })->middleware('can:report.expense.item')
            ->name('report.expense.item'); //View

        Route::post('/expense/item/get-records', [ExpenseTransactionReportController::class, 'getExpenseItemRecords'])->name('report.expense.item.ajax');

        /*Report -> Expense -> Payment  */
        Route::get('/expense/payment', function () {
            return view('report.expense.payment');
        })->middleware('can:report.expense.payment')
            ->name('report.expense.payment'); //View

        Route::post('/expense/payment/get-records', [ExpenseTransactionReportController::class, 'getExpensePaymentRecords'])->name('report.expense.payment.ajax');

        /*Report  -> Transaction -> Cash flow  */
        Route::get('/transaction/cashflow', function () {
            return view('report.transaction.cashflow');
        })->middleware('can:report.transaction.cashflow')
            ->name('report.transaction.cashflow'); //View

        Route::post('/transaction/cashflow/get-records', [CashController::class, 'getCashflowRecords'])->name('report.transaction.cashflow.ajax');

        /*Report -> Transaction -> Bank Statement  */
        Route::get('/transaction/bank-statement', function () {
            return view('report.transaction.bank-statement');
        })->middleware('can:report.transaction.bank-statement')
            ->name('report.transaction.bank-statement'); //View

        Route::post('/transaction/bank-statement/get-records', [BankController::class, 'getBankStatementRecords'])->name('report.transaction.bank-statement.ajax');

        /*Report -> GST Reports -> GSTR-1  */
        Route::get('/gstr-1', function () {
            return view('report.gst.gstr-1');
        })->middleware('can:report.gstr-1')
            ->name('report.gstr-1'); //View

        Route::post('/gstr-1/get-records', [GstReportController::class, 'getGstr1Records'])->name('report.gstr-1.ajax');

        /*Report -> GST Reports -> GSTR-2  */
        Route::get('/gstr-2', function () {
            return view('report.gst.gstr-2');
        })->middleware('can:report.gstr-2')
            ->name('report.gstr-2'); //View

        Route::post('/gstr-2/get-records', [GstReportController::class, 'getGstr2Records'])->name('report.gstr-2.ajax');

        /*Report -> Stock Transfer -> Stock Transfer  */
        Route::get('/stock-transfer', function () {
            return view('report.stock-transfer.stock-transfer');
        })->middleware('can:report.stock_transfer')
            ->name('report.stock_transfer'); //View

        Route::post('/stock-transfer/get-records', [StockTransferReportController::class, 'getStockTransferRecords'])->name('report.stock_transfer.ajax');

        /*Report -> Stock Transfer -> Items*/
        Route::get('/stock-transfer/item', function () {
            return view('report.stock-transfer.item-stock-transfer');
        })->middleware('can:report.stock_transfer.item')
            ->name('report.stock_transfer.item'); //View

        Route::post('/stock-transfer/item/get-records', [StockTransferReportController::class, 'getStockTransferItemRecords'])->name('report.stock_transfer.item.ajax');

        /*Report -> Stock Adjustment -> Stock Adjustment  */
        Route::get('/stock-adjustment', function () {
            return view('report.stock-adjustment.stock-adjustment');
        })->middleware('can:report.stock_adjustment')
            ->name('report.stock_adjustment'); //View

        Route::post('/stock-adjustment/get-records', [StockAdjustmentReportController::class, 'getStockAdjustmentRecords'])->name('report.stock_adjustment.ajax');

        /*Report -> Stock Adjustment -> Items*/
        Route::get('/stock-adjustment/item', function () {
            return view('report.stock-adjustment.item-stock-adjustment');
        })->middleware('can:report.stock_adjustment.item')
            ->name('report.stock_adjustment.item'); //View

        Route::post('/stock-adjustment/item/get-records', [StockAdjustmentReportController::class, 'getStockAdjustmentItemRecords'])->name('report.stock_adjustment.item.ajax');

        /*Report -> Due Payments -> Customer */
        Route::get('/customer/due', function () {
            return view('report.party.due-payment.customer');
        })->middleware('can:report.customer.due.payment')
            ->name('report.customer.due.payment'); //View

        Route::post('/customer/due/get-records', [CustomerReportController::class, 'getDuePaymentsRecords'])->name('report.customer.due.payment.ajax');

        /*Report -> Due Payments -> Supplier */
        Route::get('/supplier/due', function () {
            return view('report.party.due-payment.supplier');
        })->middleware('can:report.supplier.due.payment')
            ->name('report.supplier.due.payment'); //View

        Route::post('/supplier/due/get-records', [SupplierReportController::class, 'getDuePaymentsRecords'])->name('report.supplier.due.payment.ajax');

        /*Report -> Stock Report -> Batch  */
        Route::get('/stock-report/batch', function () {
            return view('report.stock.batch');
        })->middleware('can:report.stock_report.item.batch')
            ->name('report.stock_report.item.batch'); //View

        Route::post('/stock-report/batch/get-records', [StockReportController::class, 'getBatchWiseStockRecords'])->name('report.stock_report.item.batch.ajax');

        /*Report -> Stock Report -> Serial  */
        Route::get('/stock-report/serial', function () {
            return view('report.stock.serial');
        })->middleware('can:report.stock_report.item.serial')
            ->name('report.stock_report.item.serial'); //View

        Route::post('/stock-report/serial/get-records', [StockReportController::class, 'getSerialWiseStockRecords'])->name('report.stock_report.item.serial.ajax');

        /*Report -> Stock Report -> General  */
        Route::get('/stock-report/general', function () {
            return view('report.stock.general');
        })->middleware('can:report.stock_report.item.general')
            ->name('report.stock_report.item.general'); //View

        Route::post('/stock-report/general/get-records', [StockReportController::class, 'getGeneralStockRecords'])->name('report.stock_report.item.general.ajax');
    });


    Route::group(['prefix' => 'account'], function () {

        /**
         * Account Master
         */
        Route::get('/create', [AccountController::class, 'create'])
            ->middleware('can:account.create')
            ->name('account.create'); //View
        Route::get('/edit/{id}', [AccountController::class, 'edit'])
            ->middleware('can:account.edit')
            ->name('account.edit'); //Edit
        Route::put('/update', [AccountController::class, 'update'])->name('account.update'); //Update
        Route::get('/list', [AccountController::class, 'list'])
            ->middleware('can:account.view')
            ->name('account.list'); //List
        Route::get('/datatable-list', [AccountController::class, 'datatableList'])->name('account.datatable.list'); //Datatable List
        Route::post('/store', [AccountController::class, 'store'])->name('account.store'); //Save operation
        Route::post('/delete/', [AccountController::class, 'delete'])->middleware('can:account.delete')->name('account.delete'); //delete operation

        /**
         * Account Group Master
         */
        Route::get('/group/create', [AccountGroupController::class, 'create'])
            ->middleware('can:account.group.create')
            ->name('account.group.create'); //View
        Route::get('/group/edit/{id}', [AccountGroupController::class, 'edit'])
            ->middleware('can:account.group.edit')
            ->name('account.group.edit'); //Edit
        Route::put('/group/update', [AccountGroupController::class, 'update'])->name('account.group.update'); //Update
        Route::get('/group/list', [AccountGroupController::class, 'list'])
            ->middleware('can:account.group.view')
            ->name('account.group.list'); //List
        Route::get('/group/datatable-list', [AccountGroupController::class, 'datatableList'])->name('account.group.datatable.list'); //Datatable List
        Route::post('/group/store', [AccountGroupController::class, 'store'])->name('account.group.store'); //Save operation
        Route::post('/group/delete/', [AccountGroupController::class, 'delete'])->middleware('can:account.group.delete')->name('account.group.delete'); //delete operation

    });

    Route::group(['prefix' => 'expense'], function () {

        /**
         * expense Master
         */
        Route::get('/create', [ExpenseController::class, 'create'])
            ->middleware('can:expense.create')
            ->name('expense.create'); //View
        Route::get('/edit/{id}', [ExpenseController::class, 'edit'])
            ->middleware('can:expense.edit')
            ->name('expense.edit'); //Edit
        Route::put('/update', [ExpenseController::class, 'store'])->name('expense.update'); //Update
        Route::get('/list', [ExpenseController::class, 'list'])
            ->middleware('can:expense.view')
            ->name('expense.list'); //List
        Route::get('/print/{id}', [ExpenseController::class, 'print'])
            ->middleware('can:expense.view')
            ->name('expense.print');
        Route::get('/datatable-list', [ExpenseController::class, 'datatableList'])->name('expense.datatable.list'); //Datatable List
        Route::post('/store', [ExpenseController::class, 'store'])->name('expense.store'); //Save operation
        Route::post('/delete/', [ExpenseController::class, 'delete'])->name('expense.delete')->middleware('can:expense.delete'); //delete operation

        /**
         * Expense Category Master
         */
        Route::get('/category/create', [ExpenseCategoryController::class, 'create'])
            ->middleware('can:expense.category.create')
            ->name('expense.category.create'); //View
        Route::get('/category/edit/{id}', [ExpenseCategoryController::class, 'edit'])
            ->middleware('can:expense.category.edit')
            ->name('expense.category.edit'); //Edit
        Route::put('/category/update', [ExpenseCategoryController::class, 'update'])->name('expense.category.update'); //Update
        Route::get('/category/list', [ExpenseCategoryController::class, 'list'])
            ->middleware('can:expense.category.view')
            ->name('expense.category.list'); //List
        Route::get('/category/datatable-list', [ExpenseCategoryController::class, 'datatableList'])->name('expense.category.datatable.list'); //Datatable List
        Route::post('/category/store', [ExpenseCategoryController::class, 'store'])->name('expense.category.store'); //Save operation
        Route::post('/category/delete/', [ExpenseCategoryController::class, 'delete'])->middleware('can:expense.category.delete')->name('expense.category.delete'); //delete operation

        /**
         * Expense Sub-Category Master
         */
        Route::get('/subcategory/create', [ExpenseSubcategoryController::class, 'create'])
            ->middleware('can:expense.subcategory.create')
            ->name('expense.subcategory.create'); //View
        Route::get('/subcategory/edit/{id}', [ExpenseSubcategoryController::class, 'edit'])
            ->middleware('can:expense.subcategory.edit')
            ->name('expense.subcategory.edit'); //Edit
        Route::put('/subcategory/update', [ExpenseSubcategoryController::class, 'update'])->name('expense.subcategory.update'); //Update
        Route::get('/subcategory/list', [ExpenseSubcategoryController::class, 'list'])
            ->middleware('can:expense.subcategory.view')
            ->name('expense.subcategory.list'); //List
        Route::get('/subcategory/datatable-list', [ExpenseSubcategoryController::class, 'datatableList'])->name('expense.subcategory.datatable.list'); //Datatable List
        Route::post('/subcategory/store', [ExpenseSubcategoryController::class, 'store'])->name('expense.subcategory.store'); //Save operation
        Route::post('/subcategory/delete/', [ExpenseSubcategoryController::class, 'delete'])->middleware('can:expense.subcategory.delete')->name('expense.subcategory.delete'); //delete operation

        /**
         * Load Expense Items for search box
         * */
        Route::get('/expense-items-master/ajax/get-list', [ExpenseController::class, 'getAjaxSearchBarList']);
    });

    Route::get('/item/low-stock-count', [ItemController::class, 'lowStockCount']);

    Route::group(['prefix' => 'item'], function () {

        /**
         * Item Master
         */
        Route::get('/create', [ItemController::class, 'create'])
            ->middleware('can:item.create')
            ->name('item.create'); //View
        Route::get('/edit/{id}', [ItemController::class, 'edit'])
            ->middleware('can:item.edit')
            ->name('item.edit'); //Edit
        Route::put('/update', [ItemController::class, 'store'])->name('item.update'); //Update
        Route::get('/list', [ItemController::class, 'list'])
            ->middleware('can:item.view')
            ->name('item.list'); //List
        Route::get('/low-stock-count', [ItemController::class, 'lowStockCount'])
            ->middleware('can:item.view')
            ->name('items.low-stock-count');
        Route::get('/datatable-list', [ItemController::class, 'datatableList'])->name('item.datatable.list'); //Datatable List
        Route::post('/store', [ItemController::class, 'store'])->name('item.store'); //Save operation
        Route::post('/delete/', [ItemController::class, 'delete'])->name('item.delete')->middleware('can:item.delete'); //delete operation

        Route::get('/transaction/{id}', [ItemTransactionController::class, 'list'])
            ->middleware('can:item.view')
            ->name('item.transaction.list');
        Route::get('/transaction-list', [ItemTransactionController::class, 'datatableList']); //Datatable List

        Route::get('/getimage/{image_name?}', function ($image_name = null) {
            $imagePath = 'public/images/items/' . $image_name;
            if ($image_name == null || !Storage::exists($imagePath)) {
                return redirect('/noimage');
            }
            return response()->file(Storage::path($imagePath));
        });
        Route::get('/getimage/thumbnail/{image_name?}', function ($image_name = null) {
            $imagePath = 'public/images/items/' . $image_name;
            if (empty($image_name) || $image_name == null || !Storage::exists($imagePath)) {
                $imagePath = Storage::path('public/images/noimages/camera.jpg');
                return response()->file($imagePath);
            }
            return response()->file(Storage::path($imagePath));
        });

        /**
         * Item Category Master
         */
        Route::get('/category/create', [ItemCategoryController::class, 'create'])
            ->middleware('can:item.category.create')
            ->name('item.category.create'); //View
        Route::get('/category/edit/{id}', [ItemCategoryController::class, 'edit'])
            ->middleware('can:item.category.edit')
            ->name('item.category.edit'); //Edit
        Route::put('/category/update', [ItemCategoryController::class, 'update'])->name('item.category.update'); //Update
        Route::get('/category/list', [ItemCategoryController::class, 'list'])
            ->middleware('can:item.category.view')
            ->name('item.category.list'); //List
        Route::get('/category/datatable-list', [ItemCategoryController::class, 'datatableList'])->name('item.category.datatable.list'); //Datatable List
        Route::post('/category/store', [ItemCategoryController::class, 'store'])->name('item.category.store'); //Save operation
        Route::post('/category/delete/', [ItemCategoryController::class, 'delete'])->middleware('can:item.category.delete')->name('item.category.delete'); //delete operation

        /**
         * Item Brand Master
         */
        Route::get('/brand/create', [BrandController::class, 'create'])
            ->middleware('can:item.brand.create')
            ->name('item.brand.create'); //View
        Route::get('/brand/edit/{id}', [BrandController::class, 'edit'])
            ->middleware('can:item.brand.edit')
            ->name('item.brand.edit'); //Edit
        Route::put('/brand/update', [BrandController::class, 'update'])->name('item.brand.update'); //Update
        Route::get('/brand/list', [BrandController::class, 'list'])
            ->middleware('can:item.brand.view')
            ->name('item.brand.list'); //List
        Route::get('/brand/datatable-list', [BrandController::class, 'datatableList'])->name('item.brand.datatable.list'); //Datatable List
        Route::post('/brand/store', [BrandController::class, 'store'])->name('item.brand.store'); //Save operation
        Route::post('/brand/delete/', [BrandController::class, 'delete'])->middleware('can:item.brand.delete')->name('item.brand.delete'); //delete operation

        /**
         * Load Items for search box
         * */
        Route::get('/ajax/get-list', [ItemController::class, 'getAjaxItemSearchBarList']);

        /**
         * Load Items for POS Page Items Grid Page
         * */
        Route::get('/ajax/pos/item-grid/get-list', [ItemController::class, 'getAjaxItemSearchPOSList']);

        /**
         * Load Items for search box for Select2
         * */
        Route::get('/select2/ajax/get-list', [ItemController::class, 'getAjaxSearchBarList']);

        /**
         * Load Items for search box for Select2
         * */
        Route::get('/batch/select2/ajax/get-list', [ItemController::class, 'getAjaxItemBatchSearchBarList']);

        /**
         * Load Items for search box for Select2
         * */
        Route::get('/serial/select2/ajax/get-list', [ItemController::class, 'getAjaxItemSerialSearchBarList']);

        /**
         * Load Items for search box for Select2
         * */
        Route::get('/serial/stock/ajax/get-list', [ItemController::class, 'getAjaxItemSerialIMEISearchBarList']);

        /**
         * Load Items for search box for Select2
         * */
        Route::get('/batch/stock/ajax/get-list', [ItemController::class, 'getAjaxItemBatchStockList']);

        /**
         * Load Items for search box for autocomplete
         * */
        Route::get('/batch-table-records/ajax/get-list', [ItemController::class, 'getAjaxItemBatchTableRecords']);

        /**
         * Load Brand for search box for Select2
         * */
        Route::get('/brand/select2/ajax/get-list', [BrandController::class, 'getAjaxSearchBarList']);

        /**
         * Load Category for search box for Select2
         * */
        Route::get('/category/select2/ajax/get-list', [ItemCategoryController::class, 'getAjaxSearchBarList']);

        /**
         * Generate Barcode
         * */
        Route::get('/generate/barcode', function () {
            return view('items.barcode');
        })->middleware('can:generate.barcode')
            ->name('generate.barcode'); //View
        Route::get('/generate/labels', function () {
            return view('items.labels');
        })->middleware('can:generate.barcode')
            ->name('generate.labels'); //View
        //Route::get('/generate/labels', [ItemController::class, 'datatablePurchaseBillPayment'])->name('purchase.bill.payment.datatable.list'); //Datatable List

    });

    /**
     * User Profile
     * */
    Route::group(['prefix' => 'import'], function () {
        Route::get('/items', [ImportController::class, 'items'])
            ->middleware('can:import.item')
            ->name('import.items'); //View
        Route::post('/items/upload', [ImportController::class, 'importItems'])->name('import.items.upload'); //Save operation

        Route::get('/party', [ImportController::class, 'parties'])
            ->middleware('can:import.party')
            ->name('import.party'); //View
        Route::post('/party/upload', [ImportController::class, 'importParties'])->name('import.party.upload'); //Save operation

    });

    /**
     * Purchase Order
     * */
    Route::group(['prefix' => 'purchase/order'], function () {

        Route::get('/create', [PurchaseOrderController::class, 'create'])
            ->middleware('can:purchase.order.create')
            ->name('purchase.order.create'); //View
        Route::get('/edit/{id}', [PurchaseOrderController::class, 'edit'])
            ->middleware('can:purchase.order.edit')
            ->name('purchase.order.edit'); //Edit
        Route::put('/update', [PurchaseOrderController::class, 'store'])->name('purchase.order.update'); //Update
        Route::get('/list', [PurchaseOrderController::class, 'list'])
            ->middleware('can:purchase.order.view')
            ->name('purchase.order.list'); //List
        Route::get('/details/{id}', [PurchaseOrderController::class, 'details'])
            ->middleware('can:purchase.order.view')
            ->name('purchase.order.details');
        Route::get('/print/{id}', [PurchaseOrderController::class, 'print'])
            ->middleware('can:purchase.order.view')
            ->name('purchase.order.print');
        Route::get('/pdf/{id}', [PurchaseOrderController::class, 'generatePdf'])
            ->middleware('can:purchase.order.view')
            ->name('purchase.order.pdf');
        Route::get('/datatable-list', [PurchaseOrderController::class, 'datatableList'])->name('purchase.order.datatable.list'); //Datatable List
        Route::post('/store', [PurchaseOrderController::class, 'store'])->name('purchase.order.store'); //Save operation
        Route::post('/delete/', [PurchaseOrderController::class, 'delete'])->middleware('can:purchase.order.delete')->name('purchase.order.delete'); //delete operation

        /**
         * Email
         * */
        Route::get('/email/get-content/{id}', [PurchaseOrderController::class, 'getEmailContent'])
            ->middleware('can:purchase.order.create');

        /**
         * SMS
         * */
        Route::get('/sms/get-content/{id}', [PurchaseOrderController::class, 'getSMSContent'])
            ->middleware('can:purchase.order.create');
    });

    /**
     * Purchase Bill
     * */
    Route::group(['prefix' => 'purchase/bill'], function () {

        /*Purchase Order to Purchase : Start*/
        Route::get('/convert/{id}', [PurchaseController::class, 'convertToPurchase'])
            ->middleware('can:purchase.bill.create')
            ->name('purchase.bill.convert'); //View
        Route::post('/convert-to/purchase/save', [PurchaseController::class, 'store'])->name('purchase-order.to.purchase.save'); //save
        /*Purchase Order to Purchase : End*/


        Route::get('/create', [PurchaseController::class, 'create'])
            ->middleware('can:purchase.bill.create')
            ->name('purchase.bill.create'); //View
        Route::get('/edit/{id}', [PurchaseController::class, 'edit'])
            ->middleware('can:purchase.bill.edit')
            ->name('purchase.bill.edit'); //Edit
        Route::put('/update', [PurchaseController::class, 'store'])->name('purchase.bill.update'); //Update
        Route::get('/list', [PurchaseController::class, 'list'])
            ->middleware('can:purchase.bill.view')
            ->name('purchase.bill.list'); //List
        Route::get('/details/{id}', [PurchaseController::class, 'details'])
            ->middleware('can:purchase.bill.view')
            ->name('purchase.bill.details');
        Route::get('/print/{id}', [PurchaseController::class, 'print'])
            ->middleware('can:purchase.bill.view')
            ->name('purchase.bill.print');
        Route::get('/thermal-print/{id}', [PurchaseController::class, 'thermalPrint'])
            ->middleware('can:purchase.bill.view')
            ->name('purchase.bill.thermal-print');
        Route::get('/pdf/{id}', [PurchaseController::class, 'generatePdf'])
            ->middleware('can:purchase.bill.view')
            ->name('purchase.bill.pdf');
        Route::get('/datatable-list', [PurchaseController::class, 'datatableList'])->name('purchase.bill.datatable.list'); //Datatable List
        Route::post('/store', [PurchaseController::class, 'store'])->name('purchase.bill.store'); //Save operation
        Route::post('/delete/', [PurchaseController::class, 'delete'])->middleware('can:purchase.bill.delete')->name('purchase.bill.delete'); //delete operation

        /**
         * Email
         * */
        Route::get('/email/get-content/{id}', [PurchaseController::class, 'getEmailContent'])
            ->middleware('can:purchase.bill.create');
        /**
         * SMS
         * */
        Route::get('/sms/get-content/{id}', [PurchaseController::class, 'getSMSContent'])
            ->middleware('can:purchase.bill.create');

        /**
         * Load Purchased Items
         */
        Route::get('/purchased-items/{partyId}/{itemId?}', [PurchaseController::class, 'getPurchasedItemsData']);

        /**
         * Ajax selection box search
         * Load Invoice Details for Sale
         * */
        Route::get('/ajax/get-list', [SaleController::class, 'getAjaxSearchBarList']);
    });

    /**
     * Purchase Return or Debit Note
     * */
    Route::group(['prefix' => 'purchase/return'], function () {

        /*Purchase Bill to Purchase Return : Start*/
        Route::get('/convert/{id}', [PurchaseReturnController::class, 'convertToPurchaseReturn'])
            ->middleware('can:purchase.return.create')
            ->name('purchase.return.convert'); //View
        Route::post('/convert-from/purchase-bill/save', [PurchaseReturnController::class, 'store'])->name('purchase.to.purchase.return.save'); //save
        /*Purchase Bill to Purchase Return : End*/


        Route::get('/create', [PurchaseReturnController::class, 'create'])
            ->middleware('can:purchase.return.create')
            ->name('purchase.return.create'); //View
        Route::get('/edit/{id}', [PurchaseReturnController::class, 'edit'])
            ->middleware('can:purchase.return.edit')
            ->name('purchase.return.edit'); //Edit
        Route::put('/update', [PurchaseReturnController::class, 'store'])->name('purchase.return.update'); //Update
        Route::get('/list', [PurchaseReturnController::class, 'list'])
            ->middleware('can:purchase.return.view')
            ->name('purchase.return.list'); //List
        Route::get('/details/{id}', [PurchaseReturnController::class, 'details'])
            ->middleware('can:purchase.return.view')
            ->name('purchase.return.details');
        Route::get('/print/{id}', [PurchaseReturnController::class, 'print'])
            ->middleware('can:purchase.return.view')
            ->name('purchase.return.print');
        Route::get('/pdf/{id}', [PurchaseReturnController::class, 'generatePdf'])
            ->middleware('can:purchase.return.view')
            ->name('purchase.return.pdf');
        Route::get('/datatable-list', [PurchaseReturnController::class, 'datatableList'])->name('purchase.return.datatable.list'); //Datatable List
        Route::post('/store', [PurchaseReturnController::class, 'store'])->name('purchase.return.store'); //Save operation
        Route::post('/delete/', [PurchaseReturnController::class, 'delete'])->middleware('can:purchase.return.delete')->name('purchase.return.delete'); //delete operation

        /**
         * Email
         * */
        Route::get('/email/get-content/{id}', [PurchaseReturnController::class, 'getEmailContent'])
            ->middleware('can:purchase.return.create');
        /**
         * SMS
         * */
        Route::get('/sms/get-content/{id}', [PurchaseReturnController::class, 'getSMSContent'])
            ->middleware('can:purchase.return.create');
    });



    /**
     * Make Payment
     * */
    Route::group(['prefix' => 'payment/'], function () {
        /**
         * Purchase Order
         * */
        Route::get('/purchase-order/delete/{id}', [PurchaseOrderPaymentController::class, 'deletePurchaseOrderPayment'])
            ->middleware('can:purchase.bill.delete');


        /**
         * Purchase Bill
         *
         * */
        //get Payment details
        Route::get('/purchase-bill/get/{id}', [PurchasePaymentController::class, 'getPurchaseBillPayment'])
            ->middleware('can:purchase.bill.create');
        //save payment
        Route::post('/purchase-bill/store', [PurchasePaymentController::class, 'storePurchaseBillPayment'])->name('store.purchase.bill.payment'); //Save operation
        //get payment history
        Route::get('/purchase-bill/history/{id}', [PurchasePaymentController::class, 'getPurchaseBillPaymentHistory'])
            ->middleware('can:purchase.bill.view');
        Route::get('/purchase-bill/delete/{id}', [PurchasePaymentController::class, 'deletePurchaseBillPayment'])
            ->middleware('can:purchase.bill.delete');
        Route::get('/purchase-bill/print/{id}', [PurchasePaymentController::class, 'printPurchaseBillPayment'])
            ->middleware('can:purchase.bill.view')
            ->name('purchase.bill.payment.print');
        Route::get('/purchase-bill/pdf/{id}', [PurchasePaymentController::class, 'pdfPurchaseBillPayment'])
            ->middleware('can:purchase.bill.view')
            ->name('purchase.bill.payment.pdf');

        /**
         * Purchase Return
         *
         * */
        //get Payment details
        Route::get('/purchase-return/get/{id}', [PurchaseReturnPaymentController::class, 'getPurchaseReturnPayment'])
            ->middleware('can:purchase.return.create');
        //save payment
        Route::post('/purchase-return/store', [PurchaseReturnPaymentController::class, 'storePurchaseReturnPayment'])->name('store.purchase.return.payment'); //Save operation
        //get payment history
        Route::get('/purchase-return/history/{id}', [PurchaseReturnPaymentController::class, 'getPurchaseReturnPaymentHistory'])
            ->middleware('can:purchase.return.view');
        Route::get('/purchase-return/delete/{id}', [PurchaseReturnPaymentController::class, 'deletePurchaseReturnPayment'])
            ->middleware('can:purchase.return.delete');
        Route::get('/purchase-return/print/{id}', [PurchaseReturnPaymentController::class, 'printPurchaseReturnPayment'])
            ->middleware('can:purchase.return.view')
            ->name('purchase.return.payment.print');
        Route::get('/purchase-return/pdf/{id}', [PurchaseReturnPaymentController::class, 'pdfPurchaseReturnPayment'])
            ->middleware('can:purchase.return.view')
            ->name('purchase.return.payment.pdf');

        /**
         * Payment Out
         * */
        Route::get('/out', function () {
            return view('purchase.payment-out.list');
        })->middleware('can:purchase.bill.view')
            ->name('purchase.payment.out'); //View
        Route::get('/out/datatable-list', [PurchasePaymentController::class, 'datatablePurchaseBillPayment'])->name('purchase.bill.payment.datatable.list'); //Datatable List
    });

    /**
     * Sale Order
     * */
    Route::group(['prefix' => 'sale/order'], function () {

        Route::get('/create', [SaleOrderController::class, 'create'])
            ->middleware('can:sale.order.create')
            ->name('sale.order.create'); //View
        Route::get('/edit/{id}', [SaleOrderController::class, 'edit'])
            ->middleware('can:sale.order.edit')
            ->name('sale.order.edit'); //Edit
        Route::put('/update', [SaleOrderController::class, 'store'])->name('sale.order.update'); //Update
        Route::get('/list', [SaleOrderController::class, 'list'])
            ->middleware('can:sale.order.view')
            ->name('sale.order.list'); //List
        Route::get('/details/{id}', [SaleOrderController::class, 'details'])
            ->middleware('can:sale.order.view')
            ->name('sale.order.details');
        Route::get('/print/{id}', [SaleOrderController::class, 'print'])
            ->middleware('can:sale.order.view')
            ->name('sale.order.print');
        Route::get('/pdf/{id}', [SaleOrderController::class, 'generatePdf'])
            ->middleware('can:sale.order.view')
            ->name('sale.order.pdf');
        Route::get('/datatable-list', [SaleOrderController::class, 'datatableList'])->name('sale.order.datatable.list'); //Datatable List
        Route::post('/store', [SaleOrderController::class, 'store'])->name('sale.order.store'); //Save operation
        Route::post('/delete/', [SaleOrderController::class, 'delete'])->middleware('can:sale.order.delete')->name('sale.order.delete'); //delete operation

        /**
         * Email
         * */
        Route::get('/email/get-content/{id}', [SaleOrderController::class, 'getEmailContent'])
            ->middleware('can:sale.order.create');

        /**
         * SMS
         * */
        Route::get('/sms/get-content/{id}', [SaleOrderController::class, 'getSMSContent'])
            ->middleware('can:sale.order.create');
    });

    /**
     * Sale Bill
     * */
    Route::group(['prefix' => 'pos'], function () {
        Route::get('/', [SaleController::class, 'posCreate'])
            ->middleware('can:sale.invoice.create')
            ->name('pos.create'); //View
        Route::get('/print/{id}', [SaleController::class, 'posPrint'])
            ->middleware('can:sale.invoice.view')
            ->name('sale.invoice.pos.print');
        //Route::post('/store', [SaleController::class, 'store'])->name('sale.invoice.store');//Save operation
        Route::get('/pending/list', [PosPendingController::class, 'list'])
            ->middleware('can:sale.invoice.create')
            ->name('pos.pending.list');

        Route::get('/pending/{id}', [PosPendingController::class, 'show'])
            ->middleware('can:sale.invoice.create')
            ->name('pos.pending.show');
    });

    /**
     * Sale Bill
     * */
    Route::group(['prefix' => 'sale/invoice'], function () {

        /*Sale Order to Sale : Start*/
        Route::get('/convert/{id}', [SaleController::class, 'convertToSale'])
            ->middleware('can:sale.invoice.create')
            ->name('sale.invoice.convert'); //View
        Route::post('/convert-to/sale/save', [SaleController::class, 'store'])->name('sale-order.to.sale.save'); //save
        /*Sale Order to Sale : End*/

        /*Quotation to Sale : Start*/
        Route::get('/convert-quotation-to-sale/{id}', [SaleController::class, 'convertQuotationToSale'])
            ->middleware('can:sale.quotation.create')
            ->name('convert.quotation.to.sale.invoice'); //View
        //Route::post('/convert-quotation-to-sale/save', [SaleController::class, 'store'])->name('quotation.to.sale.save'); //save
        /*Quotation to Sale : End*/


        Route::get('/create', [SaleController::class, 'create'])
            ->middleware('can:sale.invoice.create')
            ->name('sale.invoice.create'); //View
        Route::get('/edit/{id}', [SaleController::class, 'edit'])
            ->middleware('can:sale.invoice.edit')
            ->name('sale.invoice.edit'); //Edit
        Route::put('/update', [SaleController::class, 'store'])->name('sale.invoice.update'); //Update
        Route::get('/list', [SaleController::class, 'list'])
            ->middleware('can:sale.invoice.view')
            ->name('sale.invoice.list'); //List
        Route::get('/details/{id}', [SaleController::class, 'details'])
            ->middleware('can:sale.invoice.view')
            ->name('sale.invoice.details');
        Route::get('/print/{invoiceFormat}/{id}', [SaleController::class, 'print'])
            ->middleware('can:sale.invoice.view')
            ->name('sale.invoice.print');
        Route::get('/pdf/{invoiceFormat}/{id}', [SaleController::class, 'generatePdf'])
            ->middleware('can:sale.invoice.view')
            ->name('sale.invoice.pdf');
        Route::get('/datatable-list', [SaleController::class, 'datatableList'])->name('sale.invoice.datatable.list'); //Datatable List
        Route::post('/store', [SaleController::class, 'store'])->name('sale.invoice.store'); //Save operation
        Route::post('/delete/', [SaleController::class, 'delete'])->middleware('can:sale.invoice.delete')->name('sale.invoice.delete'); //delete operation

        /**
         * Email
         * */
        Route::get('/email/get-content/{id}', [SaleController::class, 'getEmailContent'])
            ->middleware('can:sale.invoice.create');
        /**
         * SMS
         * */
        Route::get('/sms/get-content/{id}', [SaleController::class, 'getSMSContent'])
            ->middleware('can:sale.invoice.create');

        /**
         * Load Sold Items which is used in Sale Return Page
         */
        Route::get('/sold-items/{partyId}/{itemId?}', [SaleController::class, 'getSoldItemsData']);

        /**
         * Ajax selection box search
         * Load Invoice Details for Sale
         * */
        Route::get('/ajax/get-list', [SaleController::class, 'getAjaxSearchBarList']);
    });

    /**
     * Sale Return or Debit Note
     * */
    Route::group(['prefix' => 'sale/return'], function () {

        /*Sale Bill to Sale Return : Start*/
        Route::get('/convert/{id}', [SaleReturnController::class, 'convertToSaleReturn'])
            ->middleware('can:sale.return.create')
            ->name('sale.return.convert'); //View
        Route::post('/convert-from/sale-bill/save', [SaleReturnController::class, 'store'])->name('sale.to.sale.return.save'); //save
        /*Sale Bill to Sale Return : End*/


        Route::get('/create', [SaleReturnController::class, 'create'])
            ->middleware('can:sale.return.create')
            ->name('sale.return.create'); //View
        Route::get('/edit/{id}', [SaleReturnController::class, 'edit'])
            ->middleware('can:sale.return.edit')
            ->name('sale.return.edit'); //Edit
        Route::put('/update', [SaleReturnController::class, 'store'])->name('sale.return.update'); //Update
        Route::get('/list', [SaleReturnController::class, 'list'])
            ->middleware('can:sale.return.view')
            ->name('sale.return.list'); //List
        Route::get('/details/{id}', [SaleReturnController::class, 'details'])
            ->middleware('can:sale.return.view')
            ->name('sale.return.details');
        Route::get('/print/{id}', [SaleReturnController::class, 'print'])
            ->middleware('can:sale.return.view')
            ->name('sale.return.print');
        Route::get('/pdf/{id}', [SaleReturnController::class, 'generatePdf'])
            ->middleware('can:sale.return.view')
            ->name('sale.return.pdf');
        Route::get('/datatable-list', [SaleReturnController::class, 'datatableList'])->name('sale.return.datatable.list'); //Datatable List
        Route::post('/store', [SaleReturnController::class, 'store'])->name('sale.return.store'); //Save operation
        Route::post('/delete/', [SaleReturnController::class, 'delete'])->middleware('can:sale.return.delete')->name('sale.return.delete'); //delete operation

        /**
         * Email
         * */
        Route::get('/email/get-content/{id}', [SaleReturnController::class, 'getEmailContent'])
            ->middleware('can:sale.return.create');

        /**
         * SMS
         * */
        Route::get('/sms/get-content/{id}', [SaleReturnController::class, 'getSMSContent'])
            ->middleware('can:sale.return.create');
    });


    /**
     * Make Payment
     * */
    Route::group(['prefix' => 'payment/'], function () {
        /**
         * Sale Order
         * */
        Route::get('/sale-order/delete/{id}', [SaleOrderPaymentController::class, 'deleteSaleOrderPayment'])
            ->middleware('can:sale.invoice.delete');

        /**
         * Quotation
         * */
        Route::get('/quotation/delete/{id}', [QuotationPaymentController::class, 'deleteQuotationPayment'])
            ->middleware('can:sale.quotation.delete');


        /**
         * Sale Bill
         *
         * */
        //get Payment details
        Route::get('/sale-invoice/get/{id}', [SalePaymentController::class, 'getSaleBillPayment'])
            ->middleware('can:sale.invoice.create');
        //save payment
        Route::post('/sale-invoice/store', [SalePaymentController::class, 'storeSaleBillPayment'])->name('store.sale.invoice.payment'); //Save operation
        //get payment history
        Route::get('/sale-invoice/history/{id}', [SalePaymentController::class, 'getSaleBillPaymentHistory'])
            ->middleware('can:sale.invoice.view');
        Route::get('/sale-invoice/delete/{id}', [SalePaymentController::class, 'deleteSaleBillPayment'])
            ->middleware('can:sale.invoice.delete');
        Route::get('/sale-invoice/print/{id}', [SalePaymentController::class, 'printSaleBillPayment'])
            ->middleware('can:sale.invoice.view')
            ->name('sale.invoice.payment.print');
        Route::get('/sale-invoice/pdf/{id}', [SalePaymentController::class, 'pdfSaleBillPayment'])
            ->middleware('can:sale.invoice.view')
            ->name('sale.invoice.payment.pdf');

        /**
         * Sale Return
         *
         * */
        //get Payment details
        Route::get('/sale-return/get/{id}', [SaleReturnPaymentController::class, 'getSaleReturnPayment'])
            ->middleware('can:sale.return.create');
        //save payment
        Route::post('/sale-return/store', [SaleReturnPaymentController::class, 'storeSaleReturnPayment'])->name('store.sale.return.payment'); //Save operation
        //get payment history
        Route::get('/sale-return/history/{id}', [SaleReturnPaymentController::class, 'getSaleReturnPaymentHistory'])
            ->middleware('can:sale.return.view');
        Route::get('/sale-return/delete/{id}', [SaleReturnPaymentController::class, 'deleteSaleReturnPayment'])
            ->middleware('can:sale.return.delete');
        Route::get('/sale-return/print/{id}', [SaleReturnPaymentController::class, 'printSaleReturnPayment'])
            ->middleware('can:sale.return.view')
            ->name('sale.return.payment.print');
        Route::get('/sale-return/pdf/{id}', [SaleReturnPaymentController::class, 'pdfSaleReturnPayment'])
            ->middleware('can:sale.return.view')
            ->name('sale.return.payment.pdf');

        /**
         * Payment Out
         * */
        Route::get('/in', function () {
            return view('sale.payment-in.list');
        })->middleware('can:sale.invoice.view')
            ->name('sale.payment.in'); //View
        Route::get('/in/datatable-list', [SalePaymentController::class, 'datatableSaleBillPayment'])->name('sale.invoice.payment.datatable.list'); //Datatable List
    });

    /**
     * Sale Bill
     * */
    Route::group(['prefix' => 'transaction/'], function () {
        /**
         * Save Cash-In-Hand adjustment Entry
         * */
        Route::post('/cash/store', [CashController::class, 'storeCashTransaction'])->name('cash.transaction.store'); //Save operation

        //get Cash Adjustment details
        Route::get('/cash/adjustment/get/{id}', [CashController::class, 'getCashAdjustmentDetails']);
        //Return only value
        Route::get('/get/cash-in-hand', [CashController::class, 'returnCashInHandValue']);

        /**
         * Cheque
         * */
        Route::post('/cheque/store', [ChequeController::class, 'store'])->name('cheque.deposit.store'); //Save operation

        //get Cheque Adjustment details
        Route::get('/cheque/details/get/{id}', [ChequeController::class, 'getChequeTransactionDetails']);


        //Reopen cheque deposit
        Route::get('/cheque/re-open/{id}', [ChequeController::class, 'updateChequeReopen']);

        Route::get('/cash/list', [CashController::class, 'list'])
            ->middleware('can:transaction.cash.view')
            ->name('transaction.cash.list'); //List
        Route::get('/cheque/list', [ChequeController::class, 'list'])
            ->middleware('can:transaction.cheque.view')
            ->name('transaction.cheque.list'); //List
        Route::get('/bank/list', [BankController::class, 'list'])
            ->middleware('can:transaction.bank.view')
            ->name('transaction.bank.list'); //List


        Route::get('/cash/datatable-list', [CashController::class, 'datatableList']); //Datatable List
        Route::get('/cheque/datatable-list', [ChequeController::class, 'datatableList']); //Datatable List
        Route::get('/bank/datatable-list', [BankController::class, 'datatableList']); //Datatable List

        Route::post('/delete/', [CashController::class, 'delete'])->middleware('can:transaction.bank.delete')->name('cash.transaction.delete'); //delete operation

        Route::get(
            'cash/close',
            [CloseCashController::class, 'showCloseCashForm']
        )->name('transaction.close-cash');

        Route::get('/transaction/close-cash-print', [CloseCashController::class, 'printReceipt'])->name('transaction.close-cash-print');
        Route::post('/close-cash/insert', [CloseCashController::class, 'insertCloseCashData'])->name('transaction.close-cash.insert');
        Route::get('apply-close-cash-print', [CloseCashController::class, 'showApplyCloseCashPrint'])
            ->name('transaction.apply-close-cash-print');

        Route::get('close/cash/list', [CloseCashController::class, 'listCloseCash'])
            ->name('close.cash.list');

        Route::get('close-cash/datatable-list', [CloseCashController::class, 'datatableList'])
            ->name('transaction.closeCash.datatableList');

        Route::delete('close-cash/delete/{id}', [CloseCashController::class, 'delete'])
            ->name('close-cash.delete');

        // Ensure these routes exist exactly like this
        Route::get('close-cash/edit/{id}', [CloseCashController::class, 'edit'])->name('close-cash.edit');
        Route::put('close-cash/update/{id}', [CloseCashController::class, 'update'])->name('close-cash.update');

        Route::get('/print/list-close-cash/details/{id}', [CloseCashController::class, 'showCloseCashDetails'])->name('close-cash.details');

        Route::get('/print/list-close-cash/{id}/{type?}', [CloseCashController::class, 'printCloseCash'])
            ->name('close-cash.print');

        Route::get('/report-x-print', [CloseCashController::class, 'generateReportX'])
            ->middleware('can:transaction.cash.view')
            ->name('transaction.report-x.print');

        Route::get('/report-x-pdf', [CloseCashController::class, 'generateReportXPdf'])
            ->middleware('can:transaction.cash.view')
            ->name('transaction.report-x.pdf');

        Route::get('/report-z-print', [CloseCashController::class, 'generateReportZ'])
            ->middleware('can:transaction.cash.view')
            ->name('transaction.report-z.print');

        Route::get('/report-z-pdf', [CloseCashController::class, 'generateReportZPdf'])
            ->middleware('can:transaction.cash.view')
            ->name('transaction.report-z.pdf');
    });

    /**
     * Status History
     */
    Route::group(['prefix' => 'status-history/'], function () {

        Route::get('/sale-order/{id}', [SaleOrderController::class, 'getStatusHistory'])->middleware('can:sale.order.view');
        Route::get('/purchase-order/{id}', [PurchaseOrderController::class, 'getStatusHistory'])->middleware('can:purchase.order.view');
        Route::get('/quotation/{id}', [QuotationController::class, 'getStatusHistory'])->middleware('can:sale.quotation.view');
    });

    /**
     * Quotation
     * */
    Route::group(['prefix' => 'quotation'], function () {

        Route::get('/create', [QuotationController::class, 'create'])
            ->middleware('can:sale.quotation.create')
            ->name('sale.quotation.create'); //View
        Route::get('/edit/{id}', [QuotationController::class, 'edit'])
            ->middleware('can:sale.quotation.edit')
            ->name('sale.quotation.edit'); //Edit
        Route::put('/update', [QuotationController::class, 'store'])->name('sale.quotation.update'); //Update
        Route::get('/list', [QuotationController::class, 'list'])
            ->middleware('can:sale.quotation.view')
            ->name('sale.quotation.list'); //List
        Route::get('/details/{id}', [QuotationController::class, 'details'])
            ->middleware('can:sale.quotation.view')
            ->name('sale.quotation.details');
        Route::get('/print/{id}', [QuotationController::class, 'print'])
            ->middleware('can:sale.quotation.view')
            ->name('sale.quotation.print');
        Route::get('/pdf/{id}', [QuotationController::class, 'generatePdf'])
            ->middleware('can:sale.quotation.view')
            ->name('sale.quotation.pdf');
        Route::get('/datatable-list', [QuotationController::class, 'datatableList'])->name('sale.quotation.datatable.list'); //Datatable List
        Route::post('/store', [QuotationController::class, 'store'])->name('sale.quotation.store'); //Save operation
        Route::post('/delete/', [QuotationController::class, 'delete'])->middleware('can:sale.quotation.delete')->name('sale.quotation.delete'); //delete operation

        /**
         * Email
         * */
        Route::get('/email/get-content/{id}', [QuotationController::class, 'getEmailContent'])
            ->middleware('can:sale.quotation.create');

        /**
         * SMS
         * */
        Route::get('/sms/get-content/{id}', [QuotationController::class, 'getSMSContent'])
            ->middleware('can:sale.quotation.create');
    }); //Quotation

    /**
     * Currencies
     * */
    Route::group(['prefix' => 'currency'], function () {
        Route::get('/create', [CurrencyController::class, 'create'])
            ->middleware('can:currency.create')
            ->name('currency.create'); //View
        Route::get('/edit/{id}', [CurrencyController::class, 'edit'])
            ->middleware('can:currency.edit')
            ->name('currency.edit'); //Edit
        Route::put('/update', [CurrencyController::class, 'update'])->name('currency.update'); //Update
        Route::get('/list', [CurrencyController::class, 'list'])
            ->middleware('can:currency.view')
            ->name('currency.list'); //List
        Route::get('/datatable-list', [CurrencyController::class, 'datatableList'])->name('currency.datatable.list'); //Datatable List
        Route::post('/store', [CurrencyController::class, 'store'])->name('currency.store'); //Save operation
        Route::post('/delete/', [CurrencyController::class, 'delete'])
            ->middleware('can:currency.delete')
            ->name('currency.delete'); //delete operation
    }); //Currencies

    /**
     * Carriers
     * */
    Route::group(['prefix' => 'carrier'], function () {
        Route::get('/create', [CarrierController::class, 'create'])
            ->middleware('can:carrier.create')
            ->name('carrier.create'); //View
        Route::get('/edit/{id}', [CarrierController::class, 'edit'])
            ->middleware('can:carrier.edit')
            ->name('carrier.edit'); //Edit
        Route::put('/update', [CarrierController::class, 'update'])->name('carrier.update'); //Update
        Route::get('/list', [CarrierController::class, 'list'])
            ->middleware('can:carrier.view')
            ->name('carrier.list'); //List
        Route::get('/datatable-list', [CarrierController::class, 'datatableList'])->name('carrier.datatable.list'); //Datatable List
        Route::post('/store', [CarrierController::class, 'store'])->name('carrier.store'); //Save operation
        Route::post('/delete/', [CarrierController::class, 'delete'])
            ->middleware('can:carrier.delete')
            ->name('carrier.delete'); //delete operation
    }); //Carriers

});

Route::post('/pos/save-sorting-preference', [POSController::class, 'saveSortingPreference'])
    ->name('pos.saveSortingPreference');

/**
 * Items Excel File
 * Download Excel files
 * */
Route::get('/download-item-sheet', function () {
    $filePath = 'public/download-sheet/download-item-sheet.xlsx';

    if (Storage::exists($filePath)) {
        return Storage::download($filePath, 'Items-Import-Format.xlsx');
    }
    abort(404);
})->name('download-item-sheet');

/**
 * Items Excel File
 * Download Excel files
 * */
Route::get('/download-contact-sheet', function () {
    $filePath = 'public/download-sheet/download-contact-sheet.xlsx';

    if (Storage::exists($filePath)) {
        return Storage::download($filePath, 'Contacts-Import-Format.xlsx');
    }
    abort(404);
})->name('download-contact-sheet');


require __DIR__ . '/auth.php';
