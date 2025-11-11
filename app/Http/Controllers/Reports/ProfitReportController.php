<?php

namespace App\Http\Controllers\Reports;

use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

use App\Models\Items\Item;
use App\Models\Items\ItemTransaction;

use App\Models\Sale\SaleReturn;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Expenses\Expense;
use App\Enums\ItemTransactionUniqueCode;
use App\Models\Sale\Sale;
use App\Models\User;
use App\Services\Reports\ProfitAndLoss\SaleProfitService;
use App\Services\Reports\ProfitAndLoss\SaleReturnProfitService;
use App\Services\ItemTransactionService;


class ProfitReportController extends Controller
{
    use FormatsDateInputs;

    use FormatNumber;

    private $saleProfitService;

    private $saleReturnProfitService;

    public $itemTransactionService;

    public function __construct(SaleProfitService $saleProfitService, SaleReturnProfitService $saleReturnProfitService, ItemTransactionService $itemTransactionService)
    {
        $this->saleProfitService = $saleProfitService;
        $this->saleReturnProfitService = $saleReturnProfitService;
        $this->itemTransactionService = $itemTransactionService;
    }


    public function getProfitRecords(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'use_sale_avg_date_range' => ['nullable', 'boolean'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $fromDate           = $request->input('from_date');
            $fromDate           = $this->toSystemDateFormat($fromDate);
            $toDate             = $request->input('to_date');
            $toDate             = $this->toSystemDateFormat($toDate);
            $warehouseId        = $request->input('warehouse_id');
            $useSaleAvgDateRange = (bool) $request->has('use_sale_avg_date_range');

            /**
             * Get sale Total without tax
             * */
            $saleRecords = $this->saleProfitService->saleTotalAmount($fromDate, $toDate, $warehouseId);
            $saleTotalWithoutTaxAmount = $saleRecords['totalNetPrice'] - $saleRecords['totalTax'];

            /**
             * Get sale Return Total without tax
             * */
            $saleReturnRecords = $this->saleReturnProfitService->saleReturnTotalAmount($fromDate, $toDate, $warehouseId);
            $saleReturnTotalWithoutTaxAmount = $saleReturnRecords['totalNetPrice'] - $saleReturnRecords['totalTax'];

            /**
             * Get Purchase Total without Tax
             * */
            $purchaseRecords = $this->purchaseTotalAmount($fromDate, $toDate, $warehouseId);
            $purchaseTotalWithoutTaxAmount = $purchaseRecords['totalNetPrice'] - $purchaseRecords['totalTax'];

            /**
             * Get shipping charge from the purchase
             */
            $shippingChargeAmount = $purchaseRecords['totalShippingCharge'];

            /**
             * Get Purchase Return Total without Tax
             * */
            $purchaseReturnRecords = $this->purchaseReturnTotalAmount($fromDate, $toDate, $warehouseId);
            $purchaseReturnTotalWithoutTaxAmount = $purchaseReturnRecords['totalNetPrice'] - $purchaseReturnRecords['totalTax'];

            /**
             * Calculate Gross Profit
             * */
            $grossProfit = $saleTotalWithoutTaxAmount - $saleReturnTotalWithoutTaxAmount;

            $grossProfit = $grossProfit - ($purchaseTotalWithoutTaxAmount - $purchaseReturnTotalWithoutTaxAmount);


            /**
             * Get Expense Total
             * */
            $expenseTotalWithoutTaxAmount = $this->expenseTotalAmount($fromDate, $toDate);

            /**
             * Calculate Net profit
             * */
            $netProfit = $grossProfit - $expenseTotalWithoutTaxAmount - $shippingChargeAmount;

            /**
             * Get sale Total without tax
             * */
            $saleProfitTotalAmount = $this->saleProfitService->saleProfitTotalAmount($fromDate, $toDate, $warehouseId, $useSaleAvgDateRange);




            $recordsArray = [
                'sale_without_tax'              => $this->formatWithPrecision($saleTotalWithoutTaxAmount),
                'sale_return_without_tax'       => $this->formatWithPrecision($saleReturnTotalWithoutTaxAmount),
                'purchase_without_tax'          => $this->formatWithPrecision($purchaseTotalWithoutTaxAmount),
                'purchase_return_without_tax'   => $this->formatWithPrecision($purchaseReturnTotalWithoutTaxAmount),
                'gross_profit'                  => $this->formatWithPrecision($grossProfit),
                'indirect_expense_without_tax'  => $this->formatWithPrecision($expenseTotalWithoutTaxAmount),
                'shipping_charge'               => $this->formatWithPrecision($shippingChargeAmount),
                'net_profit'                    => $this->formatWithPrecision($netProfit),
                'sale_gross_profit'             => $this->formatWithPrecision($saleProfitTotalAmount['saleGrossProfit']),
                'sale_net_profit'               => $this->formatWithPrecision($saleProfitTotalAmount['saleNetProfit']),
            ];

            return response()->json([
                'status'    => true,
                'message'   => "Records are retrieved!!",
                'data'      => $recordsArray,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    public function purchaseTotalAmount($fromDate, $toDate, $warehouseId)
    {
        //If warehouseId is not provided, fetch warehouses accessible to the user
        $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

        $purchase = Purchase::with(['itemTransaction' => fn($q) => $q->whereIn('warehouse_id', $warehouseIds)])
            ->select('id', 'purchase_date', 'shipping_charge', 'is_shipping_charge_distributed', 'round_off')
            ->whereBetween('purchase_date', [$fromDate, $toDate])
            ->get();

        if ($purchase->isNotEmpty()) {
            $totalDiscount = $purchase->flatMap->itemTransaction->sum('discount_amount') + $purchase->sum('round_off');
            $totalNetPrice = $purchase->flatMap->itemTransaction->sum('total');

            // Calculate total shipping charge only for records where is_shipping_charge_distributed = 0
            $totalShippingCharge = $purchase->where('is_shipping_charge_distributed', 0)->sum('shipping_charge');

            $totalTax = $purchase->flatMap->itemTransaction->sum('tax_amount');
        }


        return [
            'totalDiscount' => $totalDiscount ?? 0,
            'totalNetPrice' => $totalNetPrice ?? 0,
            'totalShippingCharge' => $totalShippingCharge ?? 0,
            'totalTax' => $totalTax ?? 0,
        ];
    }

    public function purchaseReturnTotalAmount($fromDate, $toDate, $warehouseId)
    {
        //If warehouseId is not provided, fetch warehouses accessible to the user
        $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

        $purchase = PurchaseReturn::with(['itemTransaction' => fn($q) => $q->whereIn('warehouse_id', $warehouseIds)])
            ->select('id', 'return_date')
            ->whereBetween('return_date', [$fromDate, $toDate])
            ->get();

        if ($purchase->isNotEmpty()) {
            $totalDiscount = $purchase->flatMap->itemTransaction->sum('discount_amount') + $purchase->sum('round_off');
            $totalNetPrice = $purchase->flatMap->itemTransaction->sum('total');
            $totalTax = $purchase->flatMap->itemTransaction->sum('tax_amount');
        }

        return [
            'totalDiscount' => $totalDiscount ?? 0,
            'totalNetPrice' => $totalNetPrice ?? 0,
            'totalTax' => $totalTax ?? 0,
        ];
    }

    public function expenseTotalAmount($fromDate, $toDate)
    {
        return Expense::select('id', 'expense_date')
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->sum('grand_total');
    }

    /**
     * Sale Item Wise Profit & Loss Report
     */
    public function getItemWiseProfitRecords(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'item_warehouse_id' => ['nullable', 'exists:warehouses,id'],
                'use_sale_avg_date_range' => ['nullable', 'boolean'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $fromDate           = $request->input('from_date');
            $fromDate           = $this->toSystemDateFormat($fromDate);
            $toDate             = $request->input('to_date');
            $toDate             = $this->toSystemDateFormat($toDate);
            //$partyId            = $request->input('party_id');
            $itemId             = $request->input('item_id');
            $brandId             = $request->input('brand_id');
            $warehouseId        = $request->input('item_warehouse_id');
            $useSaleAvgDateRange = $request->input('use_sale_avg_date_range', false);

            //If warehouseId is not provided, fetch warehouses accessible to the user
            $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

            $preparedData = Sale::with('party', 'itemTransaction.item.brand', 'itemTransaction.warehouse')
                ->with(['itemTransaction' => function ($q) use ($itemId, $brandId, $warehouseIds) {
                    $q->whereIn('warehouse_id', $warehouseIds);
                    if ($itemId) {
                        $q->where('item_id', $itemId);
                    }
                    if ($brandId) {
                        $q->whereHas('item', function ($query) use ($brandId) {
                            $query->where('brand_id', $brandId);
                        });
                    }
                }])
                ->whereBetween('sale_date', [$fromDate, $toDate])
                // ->when($partyId, function ($query) use ($partyId) {
                //     return $query->where('party_id', $partyId);
                // })
                ->get();

            if ($preparedData->count() == 0) {
                throw new \Exception('No Records Found!!');
            }
            $recordsArray = [];

            /**
             * Group by item id and calculate average sale price and average purchase price
             */
            $itemTransactionGroups = $preparedData->flatMap->itemTransaction->groupBy('item_id');

            $itemIds = array_values($itemTransactionGroups->keys()->toArray());

            $avgPrices = $this->itemTransactionService->calculateEachItemSaleAndPurchasePrice($itemIds, $warehouseId, useGlobalPurchasePrice: true, saleTransactionDateRange: !$useSaleAvgDateRange ? [] : ['from_date' => $fromDate, 'to_date' => $toDate]);


            foreach ($itemTransactionGroups as $itemId => $transactions) {

                $quantity = $transactions->sum('quantity');
                $saleTotal = $avgPrices[$itemId]['sale']['average_sale_price'] * $quantity;
                $purchaseTotal = $avgPrices[$itemId]['purchase']['average_purchase_price'] * $quantity;
                $grossProfit = $saleTotal - $purchaseTotal;
                $netProfit = $grossProfit - $transactions->sum('tax_amount');

                $recordsArray[] = [
                    'item_name' => $transactions->first()->item->name,
                    'brand_name' => $transactions->first()->item->brand->name ?? '',
                    'avg_sale_price' => $this->formatWithPrecision($avgPrices[$itemId]['sale']['average_sale_price'], false),
                    'quantity' => $this->formatWithPrecision($quantity, false),
                    'avg_purchase_price' => $this->formatWithPrecision($avgPrices[$itemId]['purchase']['average_purchase_price'], false),
                    //'tax_amount' => $this->formatWithPrecision($taxAmount, false), // Not used in the response
                    'sale_total' => $this->formatWithPrecision($saleTotal, false),
                    'purchase_total' => $this->formatWithPrecision($purchaseTotal, false),

                    'gross_profit' => $this->formatWithPrecision($grossProfit, false),
                    'net_profit' => $this->formatWithPrecision($netProfit, false), // Net profit is same as gross profit in this context
                    'class_color' => $netProfit >= 0 ? 'text-success' : 'text-danger',
                ];
            }

            return response()->json([
                'status'    => true,
                'message' => "Records are retrieved!!",
                'data' => $recordsArray,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * Invoice Wise PRofit & Loss Report
     *
     */
    public function getInvoiceWiseProfitRecords(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'sale_id'           => ['nullable', 'exists:sales,id'],
                'use_sale_avg_date_range' => ['nullable', 'boolean'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $fromDate           = $request->input('from_date');
            $fromDate           = $this->toSystemDateFormat($fromDate);
            $toDate             = $request->input('to_date');
            $toDate             = $this->toSystemDateFormat($toDate);
            $saleId             = $request->input('sale_id');
            $useSaleAvgDateRange = $request->input('use_sale_avg_date_range', false);

            //If warehouseId is not provided, fetch warehouses accessible to the user
            $warehouseIds = User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

            $preparedData = Sale::with('party', 'itemTransaction.item.brand', 'itemTransaction.warehouse')
                ->with(['itemTransaction' => function ($q) use ($warehouseIds) {
                    $q->whereIn('warehouse_id', $warehouseIds);
                }])
                ->whereBetween('sale_date', [$fromDate, $toDate])
                ->when($saleId, function ($query) use ($saleId) {
                    return $query->where('id', $saleId);
                })
                ->get();

            if ($preparedData->count() == 0) {
                throw new \Exception('No Records Found!!');
            }


            //Get the Purchase Cost of the items sold in this sale invoice(s)
            $itemIdsArray = $preparedData->flatMap(function ($sale) {
                return $sale->itemTransaction->pluck('item_id');
            })->unique()->toArray();
            // Calculate average purchase price for the items sold in this sale invoice(s)
            $itemPrices = $this->itemTransactionService->calculateEachItemSaleAndPurchasePrice($itemIdsArray, warehouseId: null, useGlobalPurchasePrice: true, saleTransactionDateRange: !$useSaleAvgDateRange ? [] : ['from_date' => $fromDate, 'to_date' => $toDate]);

            $recordsArray = [];
            foreach ($preparedData as $sale) {
                $itemTransactions = $sale->itemTransaction;

                if ($itemTransactions->isEmpty()) {
                    continue; // Skip if no item transactions
                }

                $saleAmount = $itemTransactions->sum('total');

                //Find the Purchase Cost of the items sold in this sale
                $purchaseCost = $itemTransactions->sum(function ($transaction) use ($itemPrices) {
                    return $itemPrices[$transaction->item_id]['purchase']['average_purchase_price'] * $transaction->quantity;
                });

                $taxAmount = $itemTransactions->sum('tax_amount');
                //$discount = $itemTransactions->sum('discount_amount');//discount is already included in the sale total
                $grossProfit = $saleAmount - $purchaseCost;
                $netProfit = $grossProfit - $taxAmount;

                $recordsArray[] = [
                    'sale_date'         => $this->toUserDateFormat($sale->sale_date),
                    'sale_code'      => $sale->sale_code,
                    'customer_name'  => $sale->party->getFullName(),
                    'sale_amount'    => $this->formatWithPrecision($saleAmount, false),
                    'purchase_cost'  => $this->formatWithPrecision($purchaseCost, false),
                    'tax_amount'     => $this->formatWithPrecision($taxAmount, false),
                    //'discount'       => $this->formatWithPrecision($discount, false),
                    'gross_profit'   => $this->formatWithPrecision($grossProfit, false),
                    'net_profit'     => $this->formatWithPrecision($netProfit, false),
                    'class_color'    => $netProfit >= 0 ? 'text-success' : 'text-danger',
                ];
            }
            return response()->json([
                'status'    => true,
                'message' => "Records are retrieved!!",
                'data' => $recordsArray,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    public function getCustomerWiseProfitRecords(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'customer_id'           => ['nullable', 'exists:parties,id'],
                'use_sale_avg_date_range' => ['nullable', 'boolean'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $fromDate           = $request->input('from_date');
            $fromDate           = $this->toSystemDateFormat($fromDate);
            $toDate             = $request->input('to_date');
            $toDate             = $this->toSystemDateFormat($toDate);
            $customerId             = $request->input('party_id');
            $useSaleAvgDateRange = $request->input('use_sale_avg_date_range', false);

            //If warehouseId is not provided, fetch warehouses accessible to the user
            $warehouseIds = User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

            $preparedData = Sale::with('party', 'itemTransaction.item', 'itemTransaction.warehouse')
                ->with(['itemTransaction' => function ($q) use ($warehouseIds) {
                    $q->whereIn('warehouse_id', $warehouseIds);
                }])
                ->whereBetween('sale_date', [$fromDate, $toDate])
                ->when($customerId, function ($query) use ($customerId) {
                    return $query->where('party_id', $customerId);
                })
                ->get();

            if ($preparedData->count() == 0) {
                throw new \Exception('No Records Found!!');
            }

            // Group sales by customer
            $customerGroups = $preparedData->groupBy(function ($sale) {
                return $sale->party_id;
            });

            $recordsArray = [];
            foreach ($customerGroups as $partyId => $sales) {
                $customerName = $sales->first()->party->getFullName();

                // Collect all item transactions for this customer
                $itemTransactions = $sales->flatMap->itemTransaction;

                if ($itemTransactions->isEmpty()) {
                    continue;
                }

                $saleAmount = $itemTransactions->sum('total');

                // Get all unique item IDs for this customer
                $itemIdsArray = $itemTransactions->pluck('item_id')->unique()->toArray();

                // Calculate average purchase price for the items sold to this customer
                $itemPrices = $this->itemTransactionService->calculateEachItemSaleAndPurchasePrice($itemIdsArray, warehouseId: null, useGlobalPurchasePrice: true, saleTransactionDateRange: !$useSaleAvgDateRange ? [] : ['from_date' => $fromDate, 'to_date' => $toDate]);

                // Calculate purchase cost for all items
                $purchaseCost = $itemTransactions->sum(function ($transaction) use ($itemPrices) {
                    return $itemPrices[$transaction->item_id]['purchase']['average_purchase_price'] * $transaction->quantity;
                });

                $taxAmount = $itemTransactions->sum('tax_amount');
                $grossProfit = $saleAmount - $purchaseCost;
                $netProfit = $grossProfit - $taxAmount;

                $recordsArray[] = [
                    'customer_name'  => $customerName,
                    'sale_amount'    => $this->formatWithPrecision($saleAmount, false),
                    'purchase_cost'  => $this->formatWithPrecision($purchaseCost, false),
                    'tax_amount'     => $this->formatWithPrecision($taxAmount, false),
                    //'discount'       => $this->formatWithPrecision($discount, false),
                    'gross_profit'   => $this->formatWithPrecision($grossProfit, false),
                    'net_profit'     => $this->formatWithPrecision($netProfit, false),
                    'class_color'    => $netProfit >= 0 ? 'text-success' : 'text-danger',
                ];
            }

            return response()->json([
                'status'    => true,
                'message' => "Records are retrieved!!",
                'data' => $recordsArray,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    public function getBrandWiseProfitRecords(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'brand_id'          => ['nullable', 'exists:brands,id'],
                'brand_warehouse_id'      => ['nullable', 'exists:warehouses,id'],
                'use_sale_avg_date_range' => ['nullable', 'boolean'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $fromDate           = $request->input('from_date');
            $fromDate           = $this->toSystemDateFormat($fromDate);
            $toDate             = $request->input('to_date');
            $toDate             = $this->toSystemDateFormat($toDate);
            $brandId            = $request->input('brand_id');
            $warehouseId        = $request->input('brand_warehouse_id');
            $useSaleAvgDateRange = $request->input('use_sale_avg_date_range', false);

            //If warehouseId is not provided, fetch warehouses accessible to the user
            $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

            $preparedData = Sale::with('party', 'itemTransaction.item.brand', 'itemTransaction.warehouse')
                ->with(['itemTransaction' => function ($q) use ($brandId, $warehouseIds) {
                    if ($brandId) {
                        $q->whereHas('item', function ($query) use ($brandId) {
                            $query->where('brand_id', $brandId);
                        });
                    }
                    $q->whereIn('warehouse_id', $warehouseIds);
                }])
                ->whereBetween('sale_date', [$fromDate, $toDate])
                ->get();

            if ($preparedData->count() == 0) {
                throw new \Exception('No Records Found!!');
            }

            //Get the Purchase Cost of the items sold in this sale invoice(s)
            $itemIdsArray = $preparedData->flatMap(function ($sale) {
                return $sale->itemTransaction->pluck('item_id');
            })->unique()->toArray();
            // Calculate average purchase price for the items sold in this sale invoice(s)
            $purchaseCostOfEachItem = $this->itemTransactionService->calculateEachItemSaleAndPurchasePrice($itemIdsArray, $warehouseId, useGlobalPurchasePrice: true, saleTransactionDateRange: !$useSaleAvgDateRange ? [] : ['from_date' => $fromDate, 'to_date' => $toDate]);


            //Group by brand and calculate profit for each brand
            $brandWiseRecords = $preparedData->flatMap(function ($sale) use ($purchaseCostOfEachItem) {
                return $sale->itemTransaction->map(function ($transaction) use ($sale, $purchaseCostOfEachItem) {
                    $brandName = $transaction->item->brand->name ?? '';
                    $saleAmount = $transaction->total;
                    $purchaseCost = $purchaseCostOfEachItem[$transaction->item_id]['purchase']['average_purchase_price'] * $transaction->quantity;
                    $taxAmount = $transaction->tax_amount;
                    //$discount = $transaction->discount_amount;
                    $quantity = $transaction->quantity;

                    $grossProfit = $saleAmount - $purchaseCost;
                    $netProfit = $grossProfit - $taxAmount;
                    return [
                        'brand_name' => $brandName,
                        'sale_date' => $this->toUserDateFormat($sale->sale_date),
                        'sale_code' => $sale->sale_code,
                        'customer_name' => $sale->party->getFullName(),
                        'sale_amount' => $this->formatWithPrecision($saleAmount, false),
                        'purchase_cost' => $this->formatWithPrecision($purchaseCost, false),
                        'tax_amount' => $this->formatWithPrecision($taxAmount, false),
                        'quantity' => $this->formatWithPrecision($quantity, false),
                        'gross_profit' => $this->formatWithPrecision($grossProfit, false),
                        'net_profit' => $this->formatWithPrecision($netProfit, false),
                        'class_color' => $netProfit >= 0 ? 'text-success' : 'text-danger',
                    ];
                });
            })->groupBy('brand_name');
            $recordsArray = [];
            foreach ($brandWiseRecords as $brandName => $transactions) {
                $totalSaleAmount = $transactions->sum('sale_amount');
                $totalPurchaseCost = $transactions->sum('purchase_cost');
                $totalTaxAmount = $transactions->sum('tax_amount');
                $totalDiscount = $transactions->sum('discount');
                $totalGrossProfit = $transactions->sum('gross_profit');
                $totalNetProfit = $transactions->sum('net_profit');
                $totalQuantity = $transactions->sum('quantity');

                // Add the brand record to the records array
                $recordsArray[] = [
                    'brand_name' => $brandName,
                    'total_sale_amount' => $this->formatWithPrecision($totalSaleAmount, false),
                    'total_purchase_cost' => $this->formatWithPrecision($totalPurchaseCost, false),
                    'total_tax_amount' => $this->formatWithPrecision($totalTaxAmount, false),
                    'total_discount' => $this->formatWithPrecision($totalDiscount, false),
                    'total_quantity' => $this->formatWithPrecision($totalQuantity, false),
                    'total_gross_profit' => $this->formatWithPrecision($totalGrossProfit, false),
                    'total_net_profit' => $this->formatWithPrecision($totalNetProfit, false),
                    'class_color' => $totalNetProfit >= 0 ? 'text-success' : 'text-danger',
                ];
            }
            return response()->json([
                'status'    => true,
                'message' => "Records are retrieved!!",
                'data' => $recordsArray,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }


    public function getCategoryWiseProfitRecords(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'category_id'          => ['nullable', 'exists:item_categories,id'],
                'category_warehouse_id'      => ['nullable', 'exists:warehouses,id'],
                'use_sale_avg_date_range' => ['nullable', 'boolean'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $fromDate           = $request->input('from_date');
            $fromDate           = $this->toSystemDateFormat($fromDate);
            $toDate             = $request->input('to_date');
            $toDate             = $this->toSystemDateFormat($toDate);
            $categoryId            = $request->input('category_id');
            $warehouseId        = $request->input('category_warehouse_id');
            $useSaleAvgDateRange = $request->input('use_sale_avg_date_range', false);

            //If warehouseId is not provided, fetch warehouses accessible to the user
            $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

            $preparedData = Sale::with('party', 'itemTransaction.item.category', 'itemTransaction.warehouse')
                ->with(['itemTransaction' => function ($q) use ($categoryId, $warehouseIds) {
                    if ($categoryId) {
                        $q->whereHas('item', function ($query) use ($categoryId) {
                            $query->where('item_category_id', $categoryId);
                        });
                    }
                    $q->whereIn('warehouse_id', $warehouseIds);
                }])
                ->whereBetween('sale_date', [$fromDate, $toDate])
                ->get();

            if ($preparedData->count() == 0) {
                throw new \Exception('No Records Found!!');
            }

            //Get the Purchase Cost of the items sold in this sale invoice(s)
            $itemIdsArray = $preparedData->flatMap(function ($sale) {
                return $sale->itemTransaction->pluck('item_id');
            })->unique()->toArray();
            // Calculate average purchase price for the items sold in this sale invoice(s)
            $purchaseCostOfEachItem = $this->itemTransactionService->calculateEachItemSaleAndPurchasePrice($itemIdsArray, $warehouseId, useGlobalPurchasePrice: true, saleTransactionDateRange: !$useSaleAvgDateRange ? [] : ['from_date' => $fromDate, 'to_date' => $toDate]);


            //Group by category and calculate profit for each category
            $categoryWiseRecords = $preparedData->flatMap(function ($sale) use ($purchaseCostOfEachItem) {
                return $sale->itemTransaction->map(function ($transaction) use ($sale, $purchaseCostOfEachItem) {
                    $categoryName = $transaction->item->category->name ?? '';
                    $saleAmount = $transaction->total;
                    $purchaseCost = $purchaseCostOfEachItem[$transaction->item_id]['purchase']['average_purchase_price'] * $transaction->quantity;
                    $taxAmount = $transaction->tax_amount;
                    //$discount = $transaction->discount_amount;
                    $quantity = $transaction->quantity;

                    $grossProfit = $saleAmount - $purchaseCost;
                    $netProfit = $grossProfit - $taxAmount;
                    return [
                        'category_name' => $categoryName,
                        'sale_date' => $this->toUserDateFormat($sale->sale_date),
                        'sale_code' => $sale->sale_code,
                        'customer_name' => $sale->party->getFullName(),
                        'sale_amount' => $this->formatWithPrecision($saleAmount, false),
                        'purchase_cost' => $this->formatWithPrecision($purchaseCost, false),
                        'tax_amount' => $this->formatWithPrecision($taxAmount, false),
                        'quantity' => $this->formatWithPrecision($quantity, false),
                        'gross_profit' => $this->formatWithPrecision($grossProfit, false),
                        'net_profit' => $this->formatWithPrecision($netProfit, false),
                        'class_color' => $netProfit >= 0 ? 'text-success' : 'text-danger',
                    ];
                });
            })->groupBy('category_name');
            $recordsArray = [];
            foreach ($categoryWiseRecords as $categoryName => $transactions) {
                $totalSaleAmount = $transactions->sum('sale_amount');
                $totalPurchaseCost = $transactions->sum('purchase_cost');
                $totalTaxAmount = $transactions->sum('tax_amount');
                $totalDiscount = $transactions->sum('discount');
                $totalGrossProfit = $transactions->sum('gross_profit');
                $totalNetProfit = $transactions->sum('net_profit');
                $totalQuantity = $transactions->sum('quantity');

                // Add the category record to the records array
                $recordsArray[] = [
                    'category_name' => $categoryName,
                    'total_sale_amount' => $this->formatWithPrecision($totalSaleAmount, false),
                    'total_purchase_cost' => $this->formatWithPrecision($totalPurchaseCost, false),
                    'total_tax_amount' => $this->formatWithPrecision($totalTaxAmount, false),
                    'total_discount' => $this->formatWithPrecision($totalDiscount, false),
                    'total_quantity' => $this->formatWithPrecision($totalQuantity, false),
                    'total_gross_profit' => $this->formatWithPrecision($totalGrossProfit, false),
                    'total_net_profit' => $this->formatWithPrecision($totalNetProfit, false),
                    'class_color' => $totalNetProfit >= 0 ? 'text-success' : 'text-danger',
                ];
            }
            return response()->json([
                'status'    => true,
                'message' => "Records are retrieved!!",
                'data' => $recordsArray,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }
}
