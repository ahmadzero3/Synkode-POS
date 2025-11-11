<?php

namespace App\Http\Controllers\Reports;

use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Items\Item;
use App\Models\Items\ItemTransaction;
use App\Models\Items\ItemBatchTransaction;
use App\Models\Items\ItemSerialTransaction;
use App\Models\Items\ItemBatchQuantity;
use App\Enums\ItemTransactionUniqueCode;
use App\Models\User;
use App\Services\StockImpact;
use App\Services\ItemTransactionService;

class ItemTransactionReportController extends Controller
{
    use FormatsDateInputs;

    use FormatNumber;

    private $stockImpact;

    private $itemTransactionService;

    function __construct(StockImpact $stockImpact, ItemTransactionService $itemTransactionService)
    {
        $this->stockImpact = $stockImpact;
        $this->itemTransactionService = $itemTransactionService;
    }

    /**
     * Report -> Item Transaction -> Serial
     * @return JsonResponse
     * */
    function getBatchWiseTransactionRecords(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
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
            $batchMasterId      = $request->input('batch_id');
            $warehouseId        = $request->input('warehouse_id');

            //If warehouseId is not provided, fetch warehouses accessible to the user
            $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

            $preparedData = ItemBatchTransaction::with('itemTransaction.item.brand', 'itemBatchMaster')
                ->when($batchMasterId, function ($query) use ($batchMasterId) {
                    $query->where('item_batch_master_id', $batchMasterId);
                })
                ->whereIn('warehouse_id', $warehouseIds)
                ->when($itemId, function ($query) use ($itemId) {
                    return $query->whereHas('itemTransaction', function ($query) use ($itemId) {
                        $query->where('item_id', $itemId);
                    });
                })
                ->when($brandId, function ($query) use ($brandId) {
                    return $query->whereHas('itemTransaction.item', function ($query) use ($brandId) {
                        $query->where('brand_id', $brandId); // Corrected to `brand_id`
                    });
                })
                ->when($fromDate, function ($query) use ($fromDate, $toDate) {
                    return $query->whereHas('itemTransaction', function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween('transaction_date', [$fromDate, $toDate]);
                    });
                })
                ->get();


            if ($preparedData->count() == 0) {
                throw new \Exception('No Records Found!!');
            }

            $recordsArray = [];

            foreach ($preparedData as $data) {
                $transactionType = $data->itemTransaction->transaction_type;
                $uniqueCode = $data->itemTransaction->unique_code;

                /**
                 * Make changes in transaction type based on unique code
                 * */
                $suffix = match ($uniqueCode) {
                    ItemTransactionUniqueCode::STOCK_TRANSFER->value => '(OUT)',
                    ItemTransactionUniqueCode::STOCK_RECEIVE->value => '(IN)',
                    default => '',
                };

                $recordsArray[] = [
                    'transaction_date'      => $this->toUserDateFormat($data->itemTransaction->transaction_date),
                    'transaction_type'      => $transactionType . $suffix,
                    'invoice_or_bill_code'  => $data->itemTransaction->transaction->getTableCode() ?? '',
                    'party_name'            => $data->itemTransaction->transaction->party ? $data->itemTransaction->transaction->party->getFullName() : '',
                    'warehouse'             => $data->warehouse->name,
                    'item_name'             => $data->itemTransaction->item->name,
                    'brand_name'            => $data->itemTransaction->item->brand->name ?? '',
                    'batch_no'              => $data->itemBatchMaster->batch_no ?? '',
                    'mfg_date'              => $data->itemBatchMaster->formatted_mfg_date ?? '',
                    'exp_date'              => $data->itemBatchMaster->formatted_exp_date ?? '',
                    'model_no'              => $data->itemBatchMaster->model_no ?? '',
                    'color'                 => $data->itemBatchMaster->color ?? '',
                    'size'                  => $data->itemBatchMaster->size ?? '',
                    'quantity'              => $data->quantity,
                    'stock_impact'          => $this->stockImpact->returnStockImpact($data->itemTransaction->unique_code, $data->quantity)['quantity'],
                    'stock_impact_color'    => $this->stockImpact->returnStockImpact($data->itemTransaction->unique_code, $data->quantity)['color'],
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
     * Report -> Item Transaction -> Serial
     * @return JsonResponse
     * */
    function getSerialWiseTransactionRecords(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
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
            $serialMasterId      = $request->input('serial_id');
            $warehouseId        = $request->input('warehouse_id');

            //If warehouseId is not provided, fetch warehouses accessible to the user
            $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

            $preparedData = ItemSerialTransaction::with('itemTransaction.item.brand', 'itemSerialMaster')
                ->when($serialMasterId, function ($query) use ($serialMasterId) {
                    $query->where('item_serial_master_id', $serialMasterId); // Corrected to 'id'
                })
                ->whereIn('warehouse_id', $warehouseIds)
                ->when($itemId, function ($query) use ($itemId) {
                    return $query->whereHas('itemTransaction', function ($query) use ($itemId) {
                        $query->where('item_id', $itemId);
                    });
                })
                ->when($brandId, function ($query) use ($brandId) {
                    return $query->whereHas('itemTransaction.item', function ($query) use ($brandId) {
                        $query->where('brand_id', $brandId); // Corrected to `brand_id`
                    });
                })
                ->when($fromDate, function ($query) use ($fromDate, $toDate) {
                    return $query->whereHas('itemTransaction', function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween('transaction_date', [$fromDate, $toDate]);
                    });
                })
                ->get();


            if ($preparedData->count() == 0) {
                throw new \Exception('No Records Found!!');
            }

            $recordsArray = [];

            foreach ($preparedData as $data) {
                $transactionType = $data->itemTransaction->transaction_type;
                $uniqueCode = $data->unique_code;

                /**
                 * Make changes in transaction type based on unique code
                 * */
                $suffix = match ($uniqueCode) {
                    ItemTransactionUniqueCode::STOCK_TRANSFER->value => '(OUT)',
                    ItemTransactionUniqueCode::STOCK_RECEIVE->value => '(IN)',
                    default => '',
                };

                $recordsArray[] = [
                    'transaction_date'      => $this->toUserDateFormat($data->itemTransaction->transaction_date),
                    'transaction_type'      => $transactionType . $suffix,
                    'invoice_or_bill_code'  => $data->itemTransaction->transaction->getTableCode() ?? '',
                    'party_name'            => $data->itemTransaction->transaction->party ? $data->itemTransaction->transaction->party->getFullName() : '',
                    'warehouse'             => $data->warehouse->name,
                    'item_name'             => $data->itemTransaction->item->name,
                    'brand_name'            => $data->itemTransaction->item->brand->name ?? '',
                    'serial_code'           => $data->itemSerialMaster->serial_code ?? '',
                    'stock_impact'          => $this->stockImpact->returnStockImpact($data->unique_code, quantity: 1)['color'],
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

    function getGeneralTransactionRecords(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
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
            $warehouseId        = $request->input('warehouse_id');

            //If warehouseId is not provided, fetch warehouses accessible to the user
            $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

            $preparedData = ItemTransaction::with('item.brand')
                ->when($itemId, function ($query) use ($itemId) {
                    return $query->where('item_id', $itemId);
                })
                ->whereIn('warehouse_id', $warehouseIds)
                ->when($brandId, function ($query) use ($brandId) {
                    return $query->whereHas('item', function ($query) use ($brandId) {
                        $query->where('brand_id', $brandId); // Corrected to `brand_id`
                    });
                })
                ->when($fromDate, function ($query) use ($fromDate, $toDate) {
                    return $query->whereBetween('transaction_date', [$fromDate, $toDate]);
                })
                ->get();


            if ($preparedData->count() == 0) {
                throw new \Exception('No Records Found!!');
            }

            $recordsArray = [];

            foreach ($preparedData as $data) {
                $transactionType = $data->transaction_type;
                $uniqueCode = $data->unique_code;

                /**
                 * Make changes in transaction type based on unique code
                 * */
                $suffix = match ($uniqueCode) {
                    ItemTransactionUniqueCode::STOCK_TRANSFER->value => '(OUT)',
                    ItemTransactionUniqueCode::STOCK_RECEIVE->value => '(IN)',
                    default => '',
                };


                $recordsArray[] = [
                    'transaction_date'      => $this->toUserDateFormat($data->transaction_date),
                    'transaction_type'      => $transactionType . $suffix,
                    'invoice_or_bill_code'  => $data->transaction->getTableCode() ?? '',
                    'party_name'            => $data->transaction->party ? $data->transaction->party->getFullName() : '',
                    'warehouse'             => $data->warehouse->name,
                    'item_name'             => $data->item->name,
                    'brand_name'             => $data->item->brand->name ?? '',
                    'quantity'              => $this->formatWithPrecision($data->quantity, comma: false),
                    'stock_impact'          => $this->stockImpact->returnStockImpact($data->unique_code, $data->quantity)['quantity'],
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
    function getExpiredItemRecords(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $rules = [
                'from_date'         => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
                'to_date'           => ['required', 'date_format:' . implode(',', $this->getDateFormats())],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            /**
             * Radio Button Values:
             * use_date
             * expired_till_date
             * days_remainig_to_expire
             * */
            $filterType         = $request->filter_type;

            /**
             * Days Remaining
             * */
            $daysRemaining      = $request->days_remaining;

            $fromDate           = $request->input('from_date');
            $fromDate           = $this->toSystemDateFormat($fromDate);

            $toDate             = $request->input('to_date');
            $toDate             = $this->toSystemDateFormat($toDate);

            $itemId             = $request->input('item_id');
            $brandId             = $request->input('brand_id');
            $batchMasterId      = $request->input('batch_id');
            $warehouseId        = $request->input('warehouse_id');

            //If warehouseId is not provided, fetch warehouses accessible to the user
            $warehouseIds = $warehouseId ? [$warehouseId] : User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

            $preparedData = ItemBatchQuantity::with('itemBatchMaster.item')
                ->when($batchMasterId, function ($query) use ($batchMasterId) {
                    $query->where('item_batch_master_id', $batchMasterId);
                })
                ->whereIn('warehouse_id', $warehouseIds)
                ->when($itemId, function ($query) use ($itemId) {
                    $query->where('item_id', $itemId);
                })
                ->when($brandId, function ($query) use ($brandId) {
                    return $query->whereHas('itemBatchMaster.item', function ($query) use ($brandId) {
                        $query->where('brand_id', $brandId); // Corrected to `brand_id`
                    });
                })
                ->when($fromDate, function ($query) use ($fromDate, $toDate, $filterType, $daysRemaining) {
                    return $query->whereHas('itemBatchMaster', function ($query) use ($fromDate, $toDate, $filterType, $daysRemaining) {
                        if ($filterType == 'use_date') {
                            $query->whereBetween('exp_date', [$fromDate, $toDate]);
                        } else if ($filterType == 'expired_till_date') {
                            $query->where('exp_date', '<=', $toDate);
                        } else {
                            //days_remainig_to_expire
                            $today = Carbon::today();
                            $futureDate = $today->copy()->addDays($daysRemaining);
                            $query->whereBetween('exp_date', [$today, $futureDate]);
                        }
                    });
                })
                ->get();


            if ($preparedData->count() == 0) {
                throw new \Exception('No Records Found!!');
            }

            $recordsArray = [];

            foreach ($preparedData as $data) {
                $recordsArray[] = [
                    'warehouse'             => $data->warehouse->name,
                    'item_name'             => $data->itemBatchMaster->item->name,
                    'brand_name'             => $data->itemBatchMaster->item->brand->name ?? '',
                    'batch_no'              => $data->itemBatchMaster->batch_no ?? '',
                    'mfg_date'              => $data->itemBatchMaster->formatted_mfg_date ?? '',
                    'exp_date'              => $data->itemBatchMaster->formatted_exp_date ?? '',
                    'days_until_expiry'     => $this->itemTransactionService->daysDifferenceByDate($data->itemBatchMaster->exp_date),
                    'model_no'              => $data->itemBatchMaster->model_no ?? '',
                    'color'                 => $data->itemBatchMaster->color ?? '',
                    'size'                  => $data->itemBatchMaster->size ?? '',
                    'quantity'              => $data->quantity,
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

    function getReorderItemRecords(Request $request): JsonResponse
    {
        try {
            $itemId     = $request->input('item_id');
            $brandId    = $request->input('brand_id');
            $categoryId = $request->input('item_category_id');

            $preparedData = Item::with('baseUnit', 'brand', 'category')
                ->when($itemId, function ($query) use ($itemId) {
                    return $query->where('id', $itemId);
                })
                ->when($categoryId, function ($query) use ($categoryId) {
                    return $query->where('item_category_id', $categoryId);
                })
                ->when($brandId, function ($query) use ($brandId) {
                    $query->where('brand_id', $brandId);
                })
                ->whereColumn('current_stock', '<=', 'min_stock')
                ->get();

            if ($preparedData->count() == 0) {
                return response()->json([
                    'status'  => true,
                    'message' => "No Records Found!!",
                    'data'    => []
                ]);
            }

            $recordsArray = [];
            foreach ($preparedData as $data) {
                $recordsArray[] = [
                    'item_name'     => $data->name,
                    'brand_name'    => $data->brand->name ?? '',
                    'category_name' => $data->category->name ?? '',
                    'min_stock'     => $data->min_stock,
                    'quantity'      => $this->formatWithPrecision($data->current_stock, comma: false),
                    'unit_name'     => $data->baseUnit->name ?? '',
                ];
            }

            return response()->json([
                'status'  => true,
                'message' => "Records are retrieved!!",
                'data'    => $recordsArray,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }
}
