<?php

namespace App\Http\Controllers\Reports;

use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

use App\Models\Items\ItemTransaction;
// use App\Models\Items\ItemBatchTransaction;
// use App\Models\Items\ItemSerialTransaction;
use App\Models\StockAdjustment;
use App\Models\Items\ItemStockAdjustment;
use App\Enums\ItemTransactionUniqueCode;

class StockAdjustmentReportController extends Controller
{
    use FormatsDateInputs;

    use FormatNumber;

    public function getStockAdjustmentRecords(Request $request) : JsonResponse{
        try{
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:'.implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:'.implode(',', $this->getDateFormats())],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $fromDate           = $request->input('from_date');
            $fromDate           = $this->toSystemDateFormat($fromDate);
            $toDate             = $request->input('to_date');
            $toDate             = $this->toSystemDateFormat($toDate);

            $preparedData = StockAdjustment::with('user')->whereBetween('adjustment_date', [$fromDate, $toDate])->get();

            if($preparedData->count() == 0){
                throw new \Exception('No Records Found!!');
            }

            $recordsArray = [];

            foreach ($preparedData as $data) {
                $recordsArray[] = [
                                    'adjustment_code'         => $data->adjustment_code,
                                    'adjustment_date'         => $this->toUserDateFormat($data->adjustment_date),
                                    'reference_no'           => $data->reference_no??'',
                                    'created_by'           => $data->user->username??'',
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
     * Item Sale Report
     * */
    function getStockAdjustmentItemRecords(Request $request): JsonResponse{
        try{

            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:'.implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:'.implode(',', $this->getDateFormats())],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $fromDate           = $request->input('from_date');
            $fromDate           = $this->toSystemDateFormat($fromDate);
            $toDate             = $request->input('to_date');
            $toDate             = $this->toSystemDateFormat($toDate);
            $itemId             = $request->input('item_id');
            $brandId             = $request->input('brand_id');
            $warehouseId    = $request->input('warehouse_id');

            $preparedData = StockAdjustment::with(['itemTransaction' => function($query) {
                            $query->whereIn('unique_code', [ItemTransactionUniqueCode::STOCK_ADJUSTMENT_INCREASE->value, ItemTransactionUniqueCode::STOCK_ADJUSTMENT_DECREASE->value])
                                  ->with([  'item',
                                            'unit',
                                            'batch.itemBatchMaster',
                                            'itemSerialTransaction.itemSerialMaster',
                                            'item.itemGeneralQuantities',
                                        ]);
                        }])
                        ->whereBetween('adjustment_date', [$fromDate, $toDate])
                        ->when($warehouseId, function ($query) use ($warehouseId) {
                            // Filter by fromWarehouseId
                            return $query->whereHas('itemTransaction', function ($query) use ($warehouseId) {
                                return $query->where('warehouse_id', $warehouseId);
                            });
                        })
                        ->when($brandId, function ($query) use ($brandId) {
                            return $query->whereHas('itemTransaction.item', function ($query) use ($brandId) {
                                $query->where('brand_id', $brandId); // Corrected to `brand_id`
                            });
                        })
                        ->when($itemId, function ($query) use ($itemId) {
                            return $query->whereHas('itemTransaction', function ($query) use ($itemId) {
                                return $query->where('item_id', $itemId);
                            });
                        })
                        ->get();

            if($preparedData->count() == 0){
                throw new \Exception('No Records Found!!');
            }
            $recordsArray = [];

            foreach ($preparedData as $data) {
                foreach($data->itemTransaction as $transaction){
                    $itemSerialTransactions = $transaction->itemSerialTransaction->map(function ($serialTransaction) {
                        return $serialTransaction->itemSerialMaster->serial_code; // Assuming 'serial_number' is the field you want
                    })->implode(','); // Converts the array to a comma-separated string

                    $recordsArray[] = [
                                    'adjustment_code'         => $data->adjustment_code,
                                    'adjustment_date'         => $this->toUserDateFormat($data->adjustment_date),
                                    'warehouse_name'        => $transaction->warehouse->name,
                                    'item_name'             => $transaction->item->name,
                                    'brand_name'             => $transaction->item->brand->name??'',
                                    'serial_code'           => $itemSerialTransactions,
                                    'batch_no'              => $transaction->batch->itemBatchMaster->batch_no??'',
                                    'quantity'              => $this->formatWithPrecision($transaction->quantity, comma:false),
                                    'unit_name'             => $transaction->unit->name,
                                    'adjustment_type'       => $transaction->unique_code == ItemTransactionUniqueCode::STOCK_ADJUSTMENT_INCREASE->value ? 'Increase' : 'Decrease',
                                ];

                }

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
