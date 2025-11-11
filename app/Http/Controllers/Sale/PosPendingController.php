<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale\Sale;
use App\Services\CacheService; // you already use this in SaleController
use App\Traits\FormatsDateInputs;

class PosPendingController extends Controller
{
    use FormatsDateInputs;

    /**
     * List pending invoices for the sidebar (JSON).
     */
    public function list(Request $request): JsonResponse
    {
        // You can scope to current user if desired by adding ->where('created_by', Auth::id())
        $pending = Sale::with('party')
            ->where('invoice_status', 'pending')
            ->latest('created_at')
            ->get()
            ->map(function ($s) {
                return [
                    'id'          => $s->id,
                    'sale_code'   => $s->sale_code,
                    'party_name'  => optional($s->party)->getFullName() ?? 'Walk-in',
                    'grand_total' => number_format((float) $s->grand_total, 2, '.', ''),
                    'created_at'  => $s->created_at?->toDateTimeString(),
                ];
            });

        return response()->json([
            'status' => true,
            'count'  => $pending->count(),
            'data'   => $pending,
        ]);
    }

    /**
     * Return a single pending invoice, shaped for POS page to load in-place (JSON).
     */
    public function show(int $id): JsonResponse
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

        if ($sale->invoice_status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Only pending invoices can be returned to POS.',
            ], 422);
        }

        // Format sale_date for the input
        $formattedSaleDate = $this->toUserDateFormat($sale->sale_date);

        // Prepare item transactions with associated units, serials, and batch formatted dates
        $allUnits = CacheService::get('unit');

        $itemTransactions = $sale->itemTransaction->map(function ($transaction) use ($allUnits) {
            // Attach only the selected unit list (base + secondary) like your edit/convert flow
            $selectedUnits = getOnlySelectedUnits(
                $allUnits,
                $transaction->item->base_unit_id,
                $transaction->item->secondary_unit_id
            );

            // Map serial masters into a simple array
            $itemSerialTransactions = $transaction->itemSerialTransaction->map(function ($serialTxn) {
                return $serialTxn->itemSerialMaster->toArray();
            })->toArray();

            // If batch exists, ensure dates are presented in user format
            if ($transaction->batch?->itemBatchMaster) {
                $bm = $transaction->batch->itemBatchMaster;
                // Methods exist on model in your codebase to format dates; if not, we keep raw
                $bm->mfg_date = $bm->getFormattedMfgDateAttribute() ?? $bm->mfg_date;
                $bm->exp_date = $bm->getFormattedExpDateAttribute() ?? $bm->exp_date;
            }

            $data = $transaction->toArray();
            $data['unitList']              = $selectedUnits->toArray();
            $data['itemSerialTransactions'] = $itemSerialTransactions;

            return $data;
        })->toArray();

        return response()->json([
            'status' => true,
            'sale'   => [
                'id'                    => $sale->id,
                'sale_code'             => $sale->sale_code,
                'prefix_code'           => $sale->prefix_code,
                'count_id'              => $sale->count_id,
                'reference_no'          => $sale->reference_no,
                'party_id'              => $sale->party_id,
                'party_name'            => optional($sale->party)->getFullName() ?? 'Walk-in',
                'formatted_sale_date'   => $formattedSaleDate,
                'grand_total'           => (float) $sale->grand_total,
                'round_off'             => (float) $sale->round_off,
                'invoice_status'        => $sale->invoice_status,
            ],
            'itemTransactions' => $itemTransactions,
        ]);
    }
}
