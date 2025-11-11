<?php

namespace App\Services\Reports\ProfitAndLoss;

use App\Models\Items\ItemTransaction;
use App\Models\Purchase\Purchase;
use App\Services\PaymentTransactionService;
use App\Models\Sale\Sale;
use App\Models\User;
use App\Services\ItemTransactionService;

class SaleProfitService
{

    private $paymentTransactionService;

    private $itemTransactionService;

    public function __construct(PaymentTransactionService $paymentTransactionService, ItemTransactionService $itemTransactionService)
    {
        $this->paymentTransactionService = $paymentTransactionService;
        $this->itemTransactionService = $itemTransactionService;
    }

    public function saleProfitTotalAmount($fromDate, $toDate, $warehouseId = null, $useSaleAvgDateRange = false)
    {
        // If warehouseId is not provided, fetch warehouses accessible to the user
        $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

        // Fetch sale IDs within the date range
        $salesIds = Sale::whereBetween('sale_date', [$fromDate, $toDate])->pluck('id')->toArray();

        $saleItems = ItemTransaction::whereIn('warehouse_id', $warehouseIds)
            ->whereIn('transaction_id', $salesIds)
            ->where('transaction_type', 'Sale')
            ->get(['item_id', 'quantity', 'tax_amount']);


        $totalAvgPurchasePrice = 0;
        $totalAvgSalePrice = 0;
        $totalSaleTaxAmount = 0;

        $itemIds = $saleItems->pluck('item_id')->unique()->values()->toArray();
        $itemSaleAndPurchaseData = $this->itemTransactionService->calculateEachItemSaleAndPurchasePrice($itemIds, $warehouseId, useGlobalPurchasePrice: true, saleTransactionDateRange: !$useSaleAvgDateRange ? [] : ['from_date' => $fromDate, 'to_date' => $toDate]);

        foreach ($saleItems as $item) {
            $itemId = $item['item_id'];
            $quantity = $item['quantity'];

            if (isset($itemSaleAndPurchaseData[$itemId])) {
                $purchaseData = $itemSaleAndPurchaseData[$itemId]['purchase'] ?? [];
                $saleData = $itemSaleAndPurchaseData[$itemId]['sale'] ?? [];

                $totalAvgPurchasePrice += ($purchaseData['average_purchase_price'] ?? 0) * $quantity;
                $totalAvgSalePrice += ($saleData['average_sale_price'] ?? 0) * $quantity;
                $totalSaleTaxAmount += $item['tax_amount'];
            }
        }


        return [
            'totalAvgPurchasePrice' => $totalAvgPurchasePrice,
            'totalAvgSalePrice' => $totalAvgSalePrice,
            'totalSaleTaxAmount' => $totalSaleTaxAmount,
            'saleGrossProfit' => $totalAvgSalePrice - $totalAvgPurchasePrice,
            'saleNetProfit' => ($totalAvgSalePrice - $totalAvgPurchasePrice) - $totalSaleTaxAmount,

        ];
    }

    public function saleTotalAmount($fromDate, $toDate, $warehouseId)
    {

        //If warehouseId is not provided, fetch warehouses accessible to the user
        $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

        $sales = Sale::with(['itemTransaction' => fn($q) => $q->whereIn('warehouse_id', $warehouseIds)])
            ->select('id', 'sale_date')
            ->whereBetween('sale_date', [$fromDate, $toDate])
            ->get();

        if ($sales->isNotEmpty()) {
            $totalDiscount = $sales->flatMap->itemTransaction->sum('discount_amount') + $sales->sum('round_off');
            $totalNetPrice = $sales->flatMap->itemTransaction->sum('total');
            $totalTax = $sales->flatMap->itemTransaction->sum('tax_amount');
        }

        return [
            'totalDiscount' => $totalDiscount ?? 0,
            'totalNetPrice' => $totalNetPrice ?? 0,
            'totalTax' => $totalTax ?? 0,
        ];
    }

    public function getItemPurchasePriceFromPurchaseEntry($newSaleItemsCollection)
    {
        // Ensure morph map keys are defined
        $this->paymentTransactionService->usedTransactionTypeValue();

        $purchasePriceData = []; // To store adjusted sale price information

        $finalSaleItemsCollection = $newSaleItemsCollection->transform(function ($saleItems) {

            $remainingQuantity  = $saleItems['sale_qty_minus_opening_qty'];

            $saleItems['remaining_quantity'] = 0;

            ItemTransaction::where('transaction_type', 'Purchase')
                ->orderBy('transaction_date')
                ->where('item_id', $saleItems['sale_item_id'])
                ->chunk(30, function ($purchaseItems) use (&$remainingQuantity, &$purchasePriceData) {
                    foreach ($purchaseItems as $transaction) {

                        if ($remainingQuantity <= 0) {
                            break;
                        }
                        $purchasePrice = $transaction->unit_price;

                        $purchaseReturn = ItemTransaction::where('transaction_type', 'Purchase Return')->where('item_id', $transaction->item_id)->get();
                        if ($purchaseReturn->count() > 0) {
                        }

                        if ($transaction->quantity > 0) {
                            if ($transaction->quantity <= $remainingQuantity) {

                                $purchasePriceData[] = [
                                    'transaction_id'    => $transaction->id,
                                    'quantity'          => $transaction->quantity,
                                    'purchase_price'    => $purchasePrice,
                                    'total'             => $transaction->quantity * $purchasePrice,
                                    //'remainingQuantity' => $remainingQuantity - $transaction->quantity,
                                ];
                                $remainingQuantity -= $transaction->quantity;
                            } else {

                                $purchasePriceData[] = [
                                    'transaction_id'    => $transaction->id,
                                    'quantity'          => $remainingQuantity,
                                    'purchase_price'    => $purchasePrice,
                                    'total'             => $remainingQuantity * $purchasePrice,
                                    //'remainingQuantity' => $remainingQuantity - $transaction->quantity,
                                ];
                                $transaction->quantity -= $remainingQuantity;

                                $remainingQuantity = 0;
                            }
                        }
                    } // foreach
                });

            $saleItems['remaining_quantity'] = $purchasePriceData ?? $saleItems['sale_qty_minus_opening_qty'];

            // After processing, you can calculate profit and loss based on $purchasePriceData
            $totalCost = is_array($purchasePriceData) ? array_sum(array_column($purchasePriceData, 'total')) : 0; // Total cost of adjustments

            $saleItems['remaining_quantity_total_purchase_price'] = $totalCost;

            return $saleItems;
        }); //transform end

        return $finalSaleItemsCollection;
    }
}
