<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Prefix;
use App\Models\Sale\SaleOrder;
use App\Models\Sale\Sale;
use App\Models\Items\Item;
use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Enums\App;
use App\Enums\General;
use App\Services\PaymentTypeService;
use App\Services\GeneralDataService;
use App\Services\PaymentTransactionService;
use App\Http\Requests\SaleRequest;
use App\Services\AccountTransactionService;
use App\Services\ItemTransactionService;
use App\Models\Items\ItemSerial;
use App\Models\Items\ItemBatchTransaction;
use Carbon\Carbon;
use App\Services\CacheService;
use App\Services\ItemService;
use App\Services\PartyService;
use App\Services\Communication\Email\SaleEmailNotificationService;
use App\Services\Communication\Sms\SaleSmsNotificationService;
use App\Enums\ItemTransactionUniqueCode;
use App\Models\Sale\Quotation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use App\Models\Payment\PaymentType;
use App\Models\PaymentTypes;

use Mpdf\Mpdf;

class SaleController extends Controller
{
    use FormatNumber;

    use FormatsDateInputs;

    protected $companyId;

    private $paymentTypeService;

    private $paymentTransactionService;

    private $accountTransactionService;

    private $itemTransactionService;

    private $itemService;

    private $partyService;

    public $previousHistoryOfItems;

    public $saleEmailNotificationService;

    public $saleSmsNotificationService;

    public function __construct(
        PaymentTypeService $paymentTypeService,
        PaymentTransactionService $paymentTransactionService,
        AccountTransactionService $accountTransactionService,
        ItemTransactionService $itemTransactionService,
        ItemService $itemService,
        PartyService $partyService,
        SaleEmailNotificationService $saleEmailNotificationService,
        SaleSmsNotificationService $saleSmsNotificationService
    ) {
        $this->companyId = App::APP_SETTINGS_RECORD_ID->value;
        $this->paymentTypeService = $paymentTypeService;
        $this->paymentTransactionService = $paymentTransactionService;
        $this->accountTransactionService = $accountTransactionService;
        $this->itemTransactionService = $itemTransactionService;
        $this->itemService = $itemService;
        $this->partyService = $partyService;
        $this->saleEmailNotificationService = $saleEmailNotificationService;
        $this->saleSmsNotificationService = $saleSmsNotificationService;
        $this->previousHistoryOfItems = [];
    }

    /**
     * Create a new order.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $prefix = Prefix::findOrNew($this->companyId);

        $user = auth()->user();
        $register = \App\Models\Register::where('user_id', $user->id)->first();

        if ($register) {
            // Cashier → use his register counter
            $countId = $register->last_count_id + 1;
        } else {
            // Non-cashier → use global counter
            $lastGlobal = Sale::max('last_global_count_id') ?? 0;
            $countId = $lastGlobal + 1;
        }

        $selectedPaymentTypesArray = json_encode($this->paymentTypeService->selectedPaymentTypesArray());

        $data = [
            'prefix_code' => $prefix->sale,
            'count_id' => $countId,
        ];

        return view('sale.invoice.create', compact('data', 'selectedPaymentTypesArray'));
    }

    /**
     * Create a POS sale.
     *
     * @return \Illuminate\View\View
     */
    public function posCreate(): View
    {
        $prefix = Prefix::findOrNew($this->companyId);

        $user = auth()->user();
        $register = \App\Models\Register::where('user_id', $user->id)->first();

        if ($register) {
            // Cashier → use his register counter
            $countId = $register->last_count_id + 1;
        } else {
            // Non-cashier → use global counter
            $lastGlobal = Sale::max('last_global_count_id') ?? 0;
            $countId = $lastGlobal + 1;
        }

        $selectedPaymentTypesArray = json_encode($this->paymentTypeService->selectedPaymentTypesArray());
        $pendingInvoicesCount = Sale::where('invoice_status', 'pending')->count();

        $displayUserName = $user->username ?: ($user->first_name ?? '');
        $registerDisplayName = $register
            ? $register->name . ' - (' . $displayUserName . ')'
            : null;

        $data = [
            'prefix_code' => $prefix->sale,
            'count_id' => $countId,
            'pending_invoices_count' => $pendingInvoicesCount,
            'register_display' => $registerDisplayName, // clean value for Blade
        ];

        return view('sale.invoice.pos.create', compact('data', 'selectedPaymentTypesArray'));
    }

    public function apiShow($id)
    {
        try {
            $sale = \App\Models\Sale\Sale::find($id);

            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Sale not found'], 404);
            }

            $effectiveCountId = $sale->count_id > 0 ? $sale->count_id : $sale->last_global_count_id;

            return response()->json([
                'success' => true,
                'sale' => [
                    'id' => $sale->id,
                    'count_id' => $effectiveCountId,
                    'prefix_code' => $sale->prefix_code,
                    'invoice_status' => $sale->invoice_status,
                    'sale_date' => $sale->sale_date,
                    'reference_no' => $sale->reference_no,
                    'note' => $sale->note,
                    'grand_total' => $sale->grand_total,
                    'paid_amount' => $sale->paid_amount,
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get last count ID
     * */
    public function getLastCountId()
    {
        return Sale::select('count_id')->orderBy('id', 'desc')->first()?->count_id ?? 0;
    }

    /**
     * List the orders
     *
     * @return \Illuminate\View\View
     */
    public function list(): View
    {
        return view('sale.invoice.list');
    }

    /**
     * Convert Quotation to Sale
     *
     * @return \Illuminate\Http\View | RedirectResponse
     */
    public function convertQuotationToSale($id, $convertingFrom = 'Quotation'): View|RedirectResponse
    {
        return $this->convertToSale($id, $convertingFrom);
    }
    /**
     * Edit a Sale Order.
     *
     * @param int $id The ID of the expense to edit.
     * @return \Illuminate\View\View
     */
    public function convertToSale($id, $convertingFrom = 'Sale Order'): View|RedirectResponse
    {

        if ($convertingFrom == 'Sale Order') {
            //Validate Existance of Converted Sale Orders


            $convertedBill = Sale::where('sale_order_id', $id)->first();

            if ($convertedBill) {
                session([
                    'record' => [
                        'type' => 'success',
                        'status' => __('sale.already_converted'), //Save or update
                    ]
                ]);
                //Already Converted, Redirect it.
                return redirect()->route('sale.invoice.details', ['id' => $convertedBill->id]);
            }

            $sale = SaleOrder::with([
                'party',
                'itemTransaction' => [
                    'item',
                    'tax',
                    'batch.itemBatchMaster',
                    'itemSerialTransaction.itemSerialMaster'
                ]
            ])->findOrFail($id);
        } elseif ($convertingFrom == 'Quotation') {



            $convertedQuotation = Sale::where('quotation_id', $id)->first();

            if ($convertedQuotation) {
                session([
                    'record' => [
                        'type' => 'success',
                        'status' => __('sale.already_converted'), //Save or update
                    ]
                ]);
                //Already Converted, Redirect it.
                return redirect()->route('sale.invoice.details', ['id' => $convertedQuotation->id]);
            }

            $sale = Quotation::with([
                'party',
                'itemTransaction' => [
                    'item',
                    'tax',
                    'batch.itemBatchMaster',
                    'itemSerialTransaction.itemSerialMaster'
                ]
            ])->findOrFail($id);
        }

        // Add formatted dates from ItemBatchMaster model
        $sale->itemTransaction->each(function ($transaction) {
            if (!$transaction->batch?->itemBatchMaster) {
                return;
            }
            $batchMaster = $transaction->batch->itemBatchMaster;
            $batchMaster->mfg_date = $batchMaster->getFormattedMfgDateAttribute();
            $batchMaster->exp_date = $batchMaster->getFormattedExpDateAttribute();
        });

        //Convert Code adjustment - start
        $sale->operation = 'convert';
        $sale->converting_from = $convertingFrom;
        //$sale->formatted_sale_date = $this->toSystemDateFormat($sale->order_date);
        $sale->reference_no = '';
        //Convert Code adjustment - end



        $prefix = Prefix::findOrNew($this->companyId);
        $lastCountId = $this->getLastCountId();
        $sale->prefix_code = $prefix->sale;
        $sale->count_id = ($lastCountId + 1);

        $sale->formatted_sale_date = $this->toUserDateFormat(date('Y-m-d'));

        // Item Details
        // Prepare item transactions with associated units
        $allUnits = CacheService::get('unit');

        $itemTransactions = $sale->itemTransaction->map(function ($transaction) use ($allUnits) {
            $itemData = $transaction->toArray();

            // Use the getOnlySelectedUnits helper function
            $selectedUnits = getOnlySelectedUnits(
                $allUnits,
                $transaction->item->base_unit_id,
                $transaction->item->secondary_unit_id
            );

            // Add unitList to the item data
            $itemData['unitList'] = $selectedUnits->toArray();

            // Get item serial transactions with associated item serial master data
            $itemSerialTransactions = $transaction->itemSerialTransaction->map(function ($serialTransaction) {
                return $serialTransaction->itemSerialMaster->toArray();
            })->toArray();

            // Add itemSerialTransactions to the item data
            $itemData['itemSerialTransactions'] = $itemSerialTransactions;

            return $itemData;
        })->toArray();

        $itemTransactionsJson = json_encode($itemTransactions);

        //Payment Details
        $selectedPaymentTypesArray = json_encode($this->paymentTransactionService->getPaymentRecordsArray($sale));

        $taxList = CacheService::get('tax')->toJson();

        $paymentHistory = [];

        return view('sale.invoice.edit', compact('taxList', 'sale', 'itemTransactionsJson', 'selectedPaymentTypesArray', 'paymentHistory'));
    }

    /**
     * Edit a Sale Order.
     *
     * @param int $id The ID of the expense to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id): View
    {
        $sale = Sale::with([
            'party',
            'itemTransaction' => [
                'item',
                'tax',
                'batch.itemBatchMaster',
                'itemSerialTransaction.itemSerialMaster'
            ]
        ])->findOrFail($id);

        // Add formatted dates from ItemBatchMaster model
        $sale->itemTransaction->each(function ($transaction) {
            if (!$transaction->batch?->itemBatchMaster) {
                return;
            }
            $batchMaster = $transaction->batch->itemBatchMaster;
            $batchMaster->mfg_date = $batchMaster->getFormattedMfgDateAttribute();
            $batchMaster->exp_date = $batchMaster->getFormattedExpDateAttribute();
        });

        $sale->operation = 'update';

        // Item Details
        // Prepare item transactions with associated units
        $allUnits = CacheService::get('unit');

        $itemTransactions = $sale->itemTransaction->map(function ($transaction) use ($allUnits) {
            $itemData = $transaction->toArray();

            // Use the getOnlySelectedUnits helper function
            $selectedUnits = getOnlySelectedUnits(
                $allUnits,
                $transaction->item->base_unit_id,
                $transaction->item->secondary_unit_id
            );

            // Add unitList to the item data
            $itemData['unitList'] = $selectedUnits->toArray();

            // Get item serial transactions with associated item serial master data
            $itemSerialTransactions = $transaction->itemSerialTransaction->map(function ($serialTransaction) {
                return $serialTransaction->itemSerialMaster->toArray();
            })->toArray();

            // Add itemSerialTransactions to the item data
            $itemData['itemSerialTransactions'] = $itemSerialTransactions;

            return $itemData;
        })->toArray();

        $itemTransactionsJson = json_encode($itemTransactions);

        //Default Payment Details
        $selectedPaymentTypesArray = json_encode($this->paymentTypeService->selectedPaymentTypesArray());

        $paymentHistory = $this->paymentTransactionService->getPaymentRecordsArray($sale);

        $taxList = CacheService::get('tax')->toJson();

        return view('sale.invoice.edit', compact('taxList', 'sale', 'itemTransactionsJson', 'selectedPaymentTypesArray', 'paymentHistory'));
    }

    /**
     * View Sale Order details
     *
     * @param int $id, the ID of the order
     * @return \Illuminate\View\View
     */
    public function details($id): View
    {
        $sale = Sale::with([
            'party',
            'itemTransaction' => [
                'item',
                'tax',
                'batch.itemBatchMaster',
                'itemSerialTransaction.itemSerialMaster'
            ]
        ])->findOrFail($id);

        //Payment Details
        $selectedPaymentTypesArray = json_encode($this->paymentTransactionService->getPaymentRecordsArray($sale));

        //Batch Tracking Row count for invoice columns setting
        $batchTrackingRowCount = (new GeneralDataService())->getBatchTranckingRowCount();

        return view('sale.invoice.details', compact('sale', 'selectedPaymentTypesArray', 'batchTrackingRowCount'));
    }

    /**
     * Print Sale
     *
     * @param int $id, the ID of the sale
     * @return \Illuminate\View\View
     */
    public function posPrint($id, $isPdf = false): View
    {

        $sale = Sale::with([
            'party',
            'user',
            'itemTransaction' => [
                'item',
                'tax',
                'batch.itemBatchMaster',
                'itemSerialTransaction.itemSerialMaster'
            ]
        ])->findOrFail($id);
        //Payment Details
        $selectedPaymentTypesArray = json_encode($this->paymentTransactionService->getPaymentRecordsArray($sale));

        //Batch Tracking Row count for invoice columns setting
        $batchTrackingRowCount = (new GeneralDataService())->getBatchTranckingRowCount();

        $invoiceData = [
            'name' => __('sale.invoice'),
        ];

        return view('print.sale.pos.print', compact('isPdf', 'invoiceData', 'sale', 'selectedPaymentTypesArray', 'batchTrackingRowCount'));
    }

    /**
     * Print Sale
     *
     * @param int $id, the ID of the sale
     * @return \Illuminate\View\View
     */
    public function print($invoiceFormat = 'format-1', $id, $isPdf = false): View
    {

        $sale = Sale::with([
            'party',
            'itemTransaction' => [
                'item',
                'tax',
                'batch.itemBatchMaster',
                'itemSerialTransaction.itemSerialMaster'
            ]
        ])->findOrFail($id);

        //Payment Details
        $selectedPaymentTypesArray = json_encode($this->paymentTransactionService->getPaymentRecordsArray($sale));

        //Batch Tracking Row count for invoice columns setting
        $batchTrackingRowCount = (new GeneralDataService())->getBatchTranckingRowCount();

        $invoiceData = [
            'name' => __('sale.invoice'),
        ];
        if ($invoiceFormat == 'format-4') {
            //Format 4
            //A5 Print
            return view('print.sale.print-format-4', compact('isPdf', 'invoiceData', 'sale', 'selectedPaymentTypesArray', 'batchTrackingRowCount'));
        }
        if ($invoiceFormat == 'format-3') {
            //Format 3
            return view('print.sale.print-format-3', compact('isPdf', 'invoiceData', 'sale', 'selectedPaymentTypesArray', 'batchTrackingRowCount'));
        } else if ($invoiceFormat == 'format-2') {
            //Format 2
            return view('print.sale.print-format-2', compact('isPdf', 'invoiceData', 'sale', 'selectedPaymentTypesArray', 'batchTrackingRowCount'));
        } else {
            //Format 1
            return view('print.sale.print', compact('isPdf', 'invoiceData', 'sale', 'selectedPaymentTypesArray', 'batchTrackingRowCount'));
        }
    }


    /**
     * Generate PDF using View: print() method
     * */
    public function generatePdf($invoiceFormat = 'format-1', $id, $destination = 'D')
    {
        $random = uniqid();

        $html = $this->print(invoiceFormat: $invoiceFormat, id: $id, isPdf: true);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 2,
            'margin_bottom' => 2,
            'default_font' => 'dejavusans',
            //'direction' => 'rtl',
        ]);

        $mpdf->showImageErrors = true;
        $mpdf->WriteHTML($html);
        /**
         * Display in browser
         * 'I'
         * Downloadn PDF
         * 'D'
         * Return String
         * 'S'
         * File Save
         * 'F'
         * */
        $fileName = 'Sale-Bill-' . $id . '-' . $random . '.pdf';

        $mpdf->Output($fileName, $destination);
    }

    public function store(SaleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $saleId = $request->input('sale_id');

            // Payment Type
            $paymentTypeId = $request->payment_type_id[0] ?? null;
            $paymentTypeName = null;
            if ($paymentTypeId) {
                $paymentType = PaymentTypes::find($paymentTypeId);
                $paymentTypeName = $paymentType ? $paymentType->name : null;
            }

            // Payment calculations
            $changeReturn = 0;
            $totalPayment = array_sum($request->payment_amount);
            if ($totalPayment > $request->grand_total) {
                $changeReturn = $totalPayment - $request->grand_total;
            }
            $balance = $request->grand_total - $totalPayment;

            // Prevent too many pending invoices
            $pendingCount = Sale::where('invoice_status', 'pending')->count();
            if ($pendingCount >= 10 && $request->invoice_status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'You Cannot Save this Invoice , Check Pending Invoices !!'
                ], 422);
            }

            $user = auth()->user();

            // 🔹 STEP 1: Decide which counter to use
            $register = \App\Models\Register::where('user_id', $user->id)->lockForUpdate()->first();

            if ($register) {
                // Cashier → Use register counter
                $newCountId = $register->last_count_id + 1;
                $newGlobalCountId = 0;
            } else {
                // Non-cashier → Use global counter from sales table
                $lastGlobal = Sale::max('last_global_count_id') ?? 0;
                $newCountId = 0;
                $newGlobalCountId = $lastGlobal + 1;
            }

            // ✅ CREATE
            if ($request->operation == 'save' || $request->operation == 'convert') {
                $newSale = new Sale($validatedData);
                $newSale->invoice_status = $request->input('invoice_status', 'pending');
                $newSale->payment_type = $paymentTypeName;
                $newSale->payment_amount = $request->payment_amount[0] ?? 0;
                $newSale->change_return = $changeReturn;
                $newSale->balance = $balance;

                // Record who made the invoice
                $newSale->created_by = $user->id;
                // ★ NEW: also write to user_id so relations & username work everywhere
                $newSale->user_id = $user->id;

                // 🔹 Assign correct counter
                $newSale->count_id = $newCountId;
                $newSale->last_global_count_id = $newGlobalCountId;

                $newSale->save();
                $saleId = $newSale->id;

                // 🔹 Update only registers if cashier
                if ($register) {
                    $register->last_count_id = $newCountId;
                    $register->save();
                }
            } elseif ($request->operation == 'update' && $saleId) {
                $newSale = Sale::findOrFail($saleId);

                $fillableColumns = [
                    'party_id' => $validatedData['party_id'] ?? $newSale->party_id,
                    'sale_date' => $validatedData['sale_date'] ?? $newSale->sale_date,
                    'reference_no' => $validatedData['reference_no'] ?? $newSale->reference_no,
                    'prefix_code' => $validatedData['prefix_code'] ?? $newSale->prefix_code,
                    'count_id' => $validatedData['count_id'] ?? $newSale->count_id,
                    'last_global_count_id' => $validatedData['last_global_count_id'] ?? $newSale->last_global_count_id,
                    'sale_code' => $validatedData['sale_code'] ?? $newSale->sale_code,
                    'note' => $validatedData['note'] ?? $newSale->note,
                    'round_off' => $validatedData['round_off'] ?? $newSale->round_off,
                    'grand_total' => $validatedData['grand_total'] ?? $newSale->grand_total,
                    'state_id' => $validatedData['state_id'] ?? $newSale->state_id,
                    'currency_id' => $validatedData['currency_id'] ?? $newSale->currency_id,
                    'exchange_rate' => $validatedData['exchange_rate'] ?? $newSale->exchange_rate,
                    'payment_type' => $paymentTypeName,
                    'payment_amount' => $request->payment_amount[0] ?? 0,
                    'change_return' => $changeReturn,
                    'balance' => $balance,
                    'invoice_status' => $request->input('invoice_status', $newSale->invoice_status),
                ];

                $newSale->update($fillableColumns);

                // Reset items/payments
                $this->previousHistoryOfItems = $this->itemTransactionService->getHistoryOfItems($newSale);
                $newSale->itemTransaction()->delete();
                foreach ($newSale->accountTransaction as $saleAccount) {
                    $saleAccountId = $saleAccount->account_id;
                    $saleAccount->delete();
                    $this->accountTransactionService->calculateAccounts($saleAccountId);
                }
                foreach ($newSale->paymentTransaction as $payment) {
                    $payment->delete();
                }
            }

            // Save items & payments
            $request->merge(['sale_id' => $saleId, 'modelName' => $newSale]);
            $SaleItemsArray = $this->saveSaleItems($request);
            if (!$SaleItemsArray['status'])
                throw new \Exception($SaleItemsArray['message']);
            $salePaymentsArray = $this->saveSalePayments($request);
            if (!$salePaymentsArray['status'])
                throw new \Exception($salePaymentsArray['message']);

            // Update paid/balance
            $paidAmount = $newSale->refresh('paymentTransaction')->paymentTransaction->sum('amount');
            $newSale->paid_amount = $paidAmount;
            $newSale->balance = $newSale->grand_total - $paidAmount;
            if ($request->input('invoice_status') === 'finished') {
                $newSale->invoice_status = 'finished';
            }
            $newSale->save();

            $this->itemTransactionService->updatePreviousHistoryOfItems($request->modelName, $this->previousHistoryOfItems);

            DB::commit();
            return response()->json(['status' => true, 'message' => __('app.record_saved_successfully'), 'id' => $saleId]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 409);
        }
    }





    public function saveSalePayments($request)
    {
        $paymentCount = $request->row_count_payments;
        $grandTotal = $request->grand_total;

        //This is only for POS Page Payments
        if ($request->is_pos_form) {
            $paymentTotal = 0;
            /**
             * Used if Payment is greater then the payment.
             * Data index start from 0
             * payment_amount[0] & payment_amount[1] because POS page has only 2 payments static code
             * */
            //#0
            $payment_0 = $request->payment_amount[0];
            //#1
            $payment_1 = $request->payment_amount[1];

            //Only if single Payment has the value
            if ($payment_1 == 0) { // #1
                if ($payment_0 > 0 && $payment_0 > $grandTotal) {
                    $request->merge([
                        'payment_amount' => array_replace($request->input('payment_amount', []), [0 => $grandTotal]) // Replace 0th index value
                    ]);
                }
            }
        }

        for ($i = 0; $i <= $paymentCount; $i++) {

            /**
             * If array record not exist then continue forloop
             * */
            if (!isset($request->payment_amount[$i])) {
                continue;
            }

            /**
             * Data index start from 0
             * */
            $amount = $request->payment_amount[$i];

            if ($amount > 0) {
                if (!isset($request->payment_type_id[$i])) {
                    return [
                        'status' => false,
                        'message' => __('payment.missed_to_select_payment_type') . "#" . $i,
                    ];
                }

                $paymentsArray = [
                    'transaction_date' => $request->sale_date,
                    'amount' => $amount,
                    'payment_type_id' => $request->payment_type_id[$i],
                    'note' => $request->payment_note[$i],
                    'payment_from_unique_code' => General::INVOICE->value,
                ];
                if (!$transaction = $this->paymentTransactionService->recordPayment($request->modelName, $paymentsArray)) {
                    throw new \Exception(__('payment.failed_to_record_payment_transactions'));
                }
            } //amount>0
        } //for end

        return ['status' => true];
    }


    public function restrictToSellAboveMRP($itemModal, $request, $i)
    {

        //If auto update sale price is disabled then return
        if (!app('company')['restrict_to_sell_above_mrp']) {
            return;
        }

        //Validate is Restricted to sell above MRP
        if ($itemModal->mrp > 0) {
            /**
             * check is item sale price is greater than MRP
             * where, item sale price = unit_price - discount + tax
             */
            // Calculate price per unit correctly
            $pricePerUnit = $request->total[$i] / ($request->quantity[$i]);

            if ($pricePerUnit > $itemModal->mrp) {
                throw new \Exception("Restricted to sell! Item '{$itemModal->name}' has an MRP of {$this->formatWithPrecision($itemModal->mrp)}, but you are selling each unit at a price of " . $this->formatWithPrecision($pricePerUnit) . ".");
            }
        }
        return true;
    }

    public function restrictToSellBelowMSP($itemModal, $request, $i)
    {

        //If auto update sale price is disabled then return
        if (!app('company')['restrict_to_sell_below_msp']) {
            return;
        }
        //Validate is Restricted to sell below MSP
        if ($itemModal->msp > 0) {
            /**
             * check is item sale price is less than MSP
             * where, item sale price = unit_price - discount + tax
             */
            // Calculate price per unit correctly
            $pricePerUnit = $request->total[$i] / ($request->quantity[$i]);

            if ($pricePerUnit < $itemModal->msp) {
                throw new \Exception("Restricted to sell! Item '{$itemModal->name}' has an MSP of {$this->formatWithPrecision($itemModal->msp)}, but you are selling each unit at a price of " . $this->formatWithPrecision($pricePerUnit) . ".");
            }
        }
        return true;
    }

    public function updateItemMasterSalePrice($request, $isWholesaleCustomer, $i)
    {

        //If auto update sale price is disabled then return
        if (!app('company')['auto_update_sale_price']) {
            return;
        }

        $updateItemMaster = Item::find($request->item_id[$i]);
        if (!empty($request->sale_price[$i]) && $request->sale_price[$i] > 0) {
            if ($updateItemMaster->base_unit_id != $request->unit_id[$i]) {
                $salePrice = $request->sale_price[$i] * $updateItemMaster->conversion_rate;
            } else {
                $salePrice = $request->sale_price[$i];
            }

            if ($isWholesaleCustomer) {
                $updateItemMaster->wholesale_price = $salePrice;
                $updateItemMaster->is_wholesale_price_with_tax = ($request->tax_type[$i] == 'inclusive') ? 1 : 0;
            } else {
                $updateItemMaster->sale_price = $salePrice;
                $updateItemMaster->is_sale_price_with_tax = ($request->tax_type[$i] == 'inclusive') ? 1 : 0;
            }

            $updateItemMaster->save();
        }
    }


    public function saveSaleItems($request)
    {
        $itemsCount = $request->row_count;

        $isWholesaleCustomer = $request->only('is_wholesale_customer')['is_wholesale_customer'];

        for ($i = 0; $i < $itemsCount; $i++) {
            // skip holes
            if (!isset($request->item_id[$i])) {
                continue;
            }

            $itemDetails = \App\Models\Items\Item::with('offerComponents.componentItem')->find($request->item_id[$i]);
            $itemName = $itemDetails->name;

            // Quantity validation
            $itemQuantity = $request->quantity[$i];
            if (empty($itemQuantity) || $itemQuantity === 0 || $itemQuantity < 0) {
                return [
                    'status' => false,
                    'message' => ($itemQuantity < 0)
                        ? __('item.item_qty_negative', ['item_name' => $itemName])
                        : __('item.please_enter_item_quantity', ['item_name' => $itemName]),
                ];
            }

            // ===== FIX: Skip re-deducting offer components if reopening pending invoice =====
            $skipOfferDeduction = (
                $request->operation === 'update'
                && $request->invoice_status === 'finished'
                && $request->modelName->getOriginal('invoice_status') === 'pending'
            );

            // ===== COMBO/OFFER: validate component stock (only if not skipping) =====
            if ($itemDetails->offerComponents && $itemDetails->offerComponents->count() > 0 && !$skipOfferDeduction) {
                foreach ($itemDetails->offerComponents as $comp) {
                    $need = (float) $comp->quantity * (float) $itemQuantity;

                    // Use existing service-level stock validation for each component
                    $ok = $this->itemTransactionService->validateRegularItemQuantity(
                        $comp->componentItem,
                        $request->warehouse_id[$i],
                        $need,
                        \App\Enums\ItemTransactionUniqueCode::SALE->value
                    );

                    if (!$ok) {
                        return [
                            'status' => false,
                            'message' => "Insufficient stock for component '{$comp->componentItem->name}' to sell combo '{$itemName}'.",
                        ];
                    }
                }
            }

            // Validate general item (as you already had)
            $regularItemTransaction = $this->itemTransactionService->validateRegularItemQuantity(
                $itemDetails,
                $request->warehouse_id[$i],
                $itemQuantity,
                \App\Enums\ItemTransactionUniqueCode::SALE->value
            );

            if (!$regularItemTransaction) {
                throw new \Exception(__('item.failed_to_save_regular_item_record'));
            }

            // Restrict rules you already have
            $this->restrictToSellAboveMRP($itemDetails, $request, $i);
            $this->restrictToSellBelowMSP($itemDetails, $request, $i);

            // Auto-Update Item Master Sale Price (existing behavior)
            $this->updateItemMasterSalePrice($request, $isWholesaleCustomer, $i);

            /**
             * Record the sale line for the chosen item (existing behavior)
             */
            $transaction = $this->itemTransactionService->recordItemTransactionEntry($request->modelName, [
                'warehouse_id' => $request->warehouse_id[$i],
                'transaction_date' => $request->sale_date,
                'item_id' => $request->item_id[$i],
                'description' => $request->description[$i],
                'tracking_type' => $itemDetails->tracking_type,
                'quantity' => $itemQuantity,
                'unit_id' => $request->unit_id[$i],
                'unit_price' => $request->sale_price[$i],
                'mrp' => $request->mrp[$i] ?? 0,
                'discount_amount' => $request->discount[$i] ?? 0,
                'discount_type' => $request->discount_type[$i] ?? 'percentage',
                'tax_id' => $request->tax_id[$i] ?? null,
                'tax_type' => $request->tax_type[$i] ?? 'exclusive',
                'tax_amount' => $request->tax_amount[$i] ?? 0,
                'total' => $request->total[$i] ?? 0,
            ]);

            // ===== COMBO/OFFER: consume component stock (movement only, zero price) =====
            if ($itemDetails->offerComponents && $itemDetails->offerComponents->count() > 0 && !$skipOfferDeduction) {
                foreach ($itemDetails->offerComponents as $comp) {
                    $consumeQty = (float) $comp->quantity * (float) $itemQuantity;

                    // Record a movement for the component so warehouse stock is decreased.
                    // unit_price/total kept 0 to avoid double revenue; this is pure stock consumption.
                    $this->itemTransactionService->recordItemTransactionEntry($request->modelName, [
                        'warehouse_id' => $request->warehouse_id[$i],
                        'transaction_date' => $request->sale_date,
                        'item_id' => $comp->component_item_id,
                        'description' => "Consumed by combo: {$itemName}",
                        'tracking_type' => $comp->componentItem->tracking_type,
                        'quantity' => $consumeQty,
                        'unit_id' => $comp->componentItem->base_unit_id,
                        'unit_price' => 0,
                        'mrp' => 0,
                        'discount_amount' => 0,
                        'discount_type' => 'percentage',
                        'tax_id' => null,
                        'tax_type' => 'exclusive',
                        'tax_amount' => 0,
                        'total' => 0,
                    ]);
                }
            }

            /**
             * Serial/Batch blocks you already have continue here unchanged...
             */
            if ($itemDetails->tracking_type == 'serial') {
                if ($itemQuantity > 0) {
                    $jsonSerials = $request->serial_numbers[$i];
                    $jsonSerialsDecode = json_decode($jsonSerials);

                    $countRecords = (!empty($jsonSerialsDecode)) ? count($jsonSerialsDecode) : 0;
                    if ($countRecords != $itemQuantity) {
                        throw new \Exception(__('item.opening_quantity_not_matched_with_serial_records'));
                    }

                    foreach ($jsonSerialsDecode as $serialNumber) {
                        $serialArray = ['serial_code' => $serialNumber];
                        $serialTransaction = $this->itemTransactionService->recordItemSerials(
                            $transaction->id,
                            $serialArray,
                            $request->item_id[$i],
                            $request->warehouse_id[$i],
                            \App\Enums\ItemTransactionUniqueCode::SALE->value
                        );
                        if (!$serialTransaction) {
                            throw new \Exception(__('item.failed_to_save_serials'));
                        }
                    }
                }
            } else if ($itemDetails->tracking_type == 'batch') {
                if ($itemQuantity > 0) {
                    $batchArray = [
                        'batch_no' => $request->batch_no[$i],
                        'mfg_date' => $request->mfg_date[$i] ? $this->toSystemDateFormat($request->mfg_date[$i]) : null,
                        'exp_date' => $request->exp_date[$i] ? $this->toSystemDateFormat($request->exp_date[$i]) : null,
                        'model_no' => $request->model_no[$i],
                        'mrp' => $request->mrp[$i] ?? 0,
                        'color' => $request->color[$i],
                        'size' => $request->size[$i],
                        'quantity' => $itemQuantity,
                    ];
                    $batchTransaction = $this->itemTransactionService->recordItemBatches(
                        $transaction->id,
                        $batchArray,
                        $request->item_id[$i],
                        $request->warehouse_id[$i],
                        \App\Enums\ItemTransactionUniqueCode::SALE->value
                    );
                    if (!$batchTransaction) {
                        throw new \Exception(__('item.failed_to_save_batch_records'));
                    }
                }
            } else {
                // regular: already handled
            }
        } // for end

        return ['status' => true];
    }

    /**
     * Datatabale
     * */
    public function datatableList(Request $request)
    {

        $data = Sale::with('user', 'party')
            ->when($request->party_id, function ($query) use ($request) {
                return $query->where('party_id', $request->party_id);
            })
            ->when($request->user_id, function ($query) use ($request) {
                return $query->where('created_by', $request->user_id);
            })
            ->when($request->from_date, function ($query) use ($request) {
                return $query->where('sale_date', '>=', $this->toSystemDateFormat($request->from_date));
            })
            ->when($request->to_date, function ($query) use ($request) {
                return $query->where('sale_date', '<=', $this->toSystemDateFormat($request->to_date));
            })
            ->when(
                !optional(auth()->user())->can('sale.invoice.can.view.other.users.sale.invoices'),
                function ($query) {
                    return $query->where('created_by', auth()->user()->id);
                }
            );

        return DataTables::of($data)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $searchTerm = $request->search['value'];
                    $query->where(function ($q) use ($searchTerm) {
                        $q->where('sale_code', 'like', "%{$searchTerm}%")
                            ->orWhere('grand_total', 'like', "%{$searchTerm}%")
                            ->orWhereHas('party', function ($partyQuery) use ($searchTerm) {
                                $partyQuery->where('first_name', 'like', "%{$searchTerm}%")
                                    ->orWhere('last_name', 'like', "%{$searchTerm}%");
                            })
                            ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                                $userQuery->where('username', 'like', "%{$searchTerm}%");
                            });
                    });
                }
            })
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format(app('company')['date_format']);
            })
            ->addColumn('username', function ($row) {
                return $row->user->username ?? '';
            })
            ->addColumn('sale_date', function ($row) {
                return $row->formatted_sale_date;
            })
            ->addColumn('sale_code', function ($row) {
                return $row->sale_code;
            })
            ->addColumn('status', function ($row) {
                if ($row->saleOrder) {
                    return [
                        'text' => "Converted from Sale Order",
                        'code' => $row->saleOrder->order_code,
                        'url' => route('sale.order.details', ['id' => $row->saleOrder->id]), // Sale Order link
                    ];
                } elseif ($row->quotation) {
                    return [
                        'text' => "Converted from Quotation",
                        'code' => $row->quotation->quotation_code,
                        'url' => route('sale.quotation.details', ['id' => $row->quotation->id]), // Quotation link
                    ];
                }

                return [
                    'text' => "",
                    'code' => "",
                    'url' => "",
                ];
            })


            ->addColumn('is_return_raised', function ($row) {
                $returns = $row->saleReturn()->get(); // Get all return records

                if ($returns->isNotEmpty()) {
                    $returnCodes = $returns->pluck('return_code')->toArray(); // Get return codes
                    $returnIds = $returns->pluck('id')->toArray(); // Get return IDs

                    return [
                        'status' => "Return Raised",
                        'codes' => implode(', ', $returnCodes), // Convert codes to comma-separated string
                        'urls' => array_map(function ($id) {
                            return route('sale.return.details', ['id' => $id]);
                        }, $returnIds), // Generate URLs for each return ID
                    ];
                }
                return [
                    'status' => "",
                    'codes' => "",
                    'urls' => [],
                ];
            })



            ->addColumn('party_name', function ($row) {
                return $row->party->first_name . " " . $row->party->last_name;
            })
            ->addColumn('grand_total', function ($row) {
                return $this->formatWithPrecision($row->grand_total);
            })
            ->addColumn('balance', function ($row) {
                return $this->formatWithPrecision($row->grand_total - $row->paid_amount);
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $editUrl = route('sale.invoice.edit', ['id' => $id]);
                $deleteUrl = route('sale.invoice.delete', ['id' => $id]);
                $detailsUrl = route('sale.invoice.details', ['id' => $id]);
                $printUrl = route('sale.invoice.print', ['id' => $id, 'invoiceFormat' => 'format-1']);
                $printUrlPOS = route('sale.invoice.pos.print', ['id' => $id]);
                $pdfUrl = route('sale.invoice.pdf', ['id' => $id, 'invoiceFormat' => 'format-1']);

                //Verify is it converted or not
                /*if($row->saleReturn){
                                $convertToSale = route('sale.return.details', ['id' => $row->saleReturn->id]);
                                $convertToSaleText = __('app.view_bill');
                                $convertToSaleIcon = 'check-double';
                            }else{*/
                $convertToSale = route('sale.return.convert', ['id' => $id]);
                $convertToSaleText = __('sale.convert_to_return');
                $convertToSaleIcon = 'transfer-alt';
                //}

                $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> ' . __('app.edit') . '</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="' . $convertToSale . '"><i class="bx bx-' . $convertToSaleIcon . '"></i> ' . $convertToSaleText . '</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="' . $detailsUrl . '"></i><i class="bx bx-show-alt"></i> ' . __('app.details') . '</a>
                                </li>
                                <li>
                                    <a target="_blank" class="dropdown-item" href="' . $printUrl . '"></i><i class="bx bx-printer "></i> ' . __('app.print') . '</a>
                                </li>
                                <li>
                                    <a target="_blank" class="dropdown-item" href="' . $pdfUrl . '"></i><i class="bx bxs-file-pdf"></i> ' . __('app.pdf') . '</a>
                                </li>
                                <li>
                                    <a target="_blank" class="dropdown-item" href="' . $printUrlPOS . '"></i><i class="bx bx-printer" type="solid"></i> ' . __('sale.pos_print') . '</a>
                                </li>
                                <li>
                                    <a class="dropdown-item make-payment" data-invoice-id="' . $id . '" role="button"></i><i class="bx bx-money"></i> ' . __('payment.receive_payment') . '</a>
                                </li>
                                <li>
                                    <a class="dropdown-item payment-history" data-invoice-id="' . $id . '" role="button"></i><i class="bx bx-table"></i> ' . __('payment.history') . '</a>
                                </li>
                                <li>
                                    <a class="dropdown-item notify-through-email" data-model="sale/invoice" data-id="' . $id . '" role="button"></i><i class="bx bx-envelope"></i> ' . __('app.send_email') . '</a>
                                </li>
                                <li>
                                    <a class="dropdown-item notify-through-sms" data-model="sale/invoice" data-id="' . $id . '" role="button"></i><i class="bx bx-envelope"></i> ' . __('app.send_sms') . '</a>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id=' . $id . '><i class="bx bx-trash"></i> ' . __('app.delete') . '</button>
                                </li>
                            </ul>
                        </div>';
                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Permanently delete a sale invoice.
     */
    public function delete(Request $request)
    {
        $saleId = $request->input('sale_id');
        $sale = \App\Models\Sale\Sale::find($saleId);

        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.']);
        }

        $sale->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Update invoice status (AJAX)
     */
    public function updateInvoiceStatus(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'status' => 'required|in:pending,finished'
        ]);

        $sale = \App\Models\Sale\Sale::findOrFail($request->sale_id);
        $sale->invoice_status = $request->status;
        $sale->save();

        return response()->json(['success' => true, 'message' => 'Invoice status updated.']);
    }

    /**
     * Prepare Email Content to view
     * */
    public function getEmailContent($id)
    {
        $model = Sale::with('party')->find($id);

        $emailData = $this->saleEmailNotificationService->saleCreatedEmailNotification($id);

        $subject = ($emailData['status']) ? $emailData['data']['subject'] : '';
        $content = ($emailData['status']) ? $emailData['data']['content'] : '';

        $data = [
            'email' => $model->party->email,
            'subject' => $subject,
            'content' => $content,
        ];
        return $data;
    }

    /**
     * Prepare SMS Content to view
     * */
    public function getSMSContent($id)
    {
        $model = Sale::with('party')->find($id);

        $emailData = $this->saleSmsNotificationService->saleCreatedSmsNotification($id);

        $mobile = ($emailData['status']) ? $emailData['data']['mobile'] : '';
        $content = ($emailData['status']) ? $emailData['data']['content'] : '';

        $data = [
            'mobile' => $mobile,
            'content' => $content,
        ];
        return $data;
    }

    /**
     *
     * Load Sold Items Data, this is used in Sale Return Page
     */
    function getSoldItemsData($partyId, $itemId = null)
    {
        try {
            $sales = Sale::with([
                'party',
                'itemTransaction' => fn($query) => $query->when($itemId, fn($q) => $q->where('item_id', $itemId)),
                'itemTransaction.item.brand',
                'itemTransaction.item.tax',
                'itemTransaction.warehouse'
            ])
                ->where('party_id', $partyId)
                ->get();

            if ($sales->isEmpty()) {
                throw new \Exception('No Records found!!');
            }

            // Extract the first party name for display (assuming all sales belong to the same party)
            $partyName = $sales->first()->party->getFullName();

            $data = $sales->map(function ($sale) {
                return [
                    'sold_items' => $sale->itemTransaction->map(function ($transaction) use ($sale) {
                        return [
                            'id' => $transaction->id,
                            'sale_code' => $sale->sale_code,
                            'sale_date' => $this->toUserDateFormat($sale->sale_date),
                            'warehouse_name' => $transaction->warehouse->name,

                            'item_id' => $transaction->item_id,
                            'item_name' => "<span class='text-primary'>{$transaction->item->name}</span><br><i>[<b>Code: </b>{$transaction->item->item_code}]</i>",

                            'brand_name' => $transaction->brand->name ?? '',

                            'unit_price' => $this->formatWithPrecision($transaction->unit_price),
                            'quantity' => $this->formatQuantity($transaction->quantity),
                            'discount_amount' => $this->formatQuantity($transaction->discount_amount),
                            'tax_id' => $transaction->tax_id,
                            'tax_name' => $transaction->item->tax->name,
                            'tax_amount' => $this->formatQuantity($transaction->tax_amount),
                            'total' => $this->formatQuantity($transaction->total),
                        ];
                    })->toArray(),
                ];
            });

            // Include the party name in the response
            return [
                'party_name' => $partyName,
                'sold_items' => $data->flatMap(function ($sale) {
                    return $sale['sold_items'];
                })->toArray(),
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * Ajax Response
     * Search Bar for select2 list
     * */
    public function getAjaxSearchBarList()
    {
        $search = request('search');
        $page = request('page', 1);
        $perPage = 10;

        $query = Sale::with('party')
            ->where(function ($q) use ($search) {
                $q->where('sale_code', 'LIKE', "%{$search}%")
                    ->orWhereHas('party', function ($partyQuery) use ($search) {
                        $partyQuery->where('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('last_name', 'LIKE', "%{$search}%");
                    });
            });

        $total = $query->count();
        $invoices = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $results = $invoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'text' => $invoice->sale_code,
                'party_name' => optional($invoice->party)->getFullName(),
            ];
        });

        return response()->json([
            'results' => $results,
            'hasMore' => ($page * $perPage) < $total
        ]);
    }
}
