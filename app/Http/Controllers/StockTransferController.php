<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\StockTransfer;
use App\Models\Items\Item;
use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Enums\App;
use App\Enums\General;
use App\Services\GeneralDataService;
use App\Http\Requests\StockTransferRequest;
use App\Services\ItemTransactionService;
use App\Models\Items\ItemSerial;
use App\Models\Items\ItemBatchTransaction;
use App\Models\Items\ItemStockTransfer;
use Carbon\Carbon;
use App\Services\CacheService;
use App\Services\ItemService;
use App\Enums\ItemTransactionUniqueCode;
use App\Models\Prefix;
use Mpdf\Mpdf;

class StockTransferController extends Controller
{
    use FormatNumber;

    use FormatsDateInputs;

    protected $companyId;

    private $itemTransactionService;

    private $itemService;

    public $previousHistoryOfItems;


    public function __construct(
        ItemTransactionService $itemTransactionService,
        ItemService $itemService,
    ) {
        $this->companyId = App::APP_SETTINGS_RECORD_ID->value;
        $this->itemTransactionService = $itemTransactionService;
        $this->itemService = $itemService;
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
        $lastCountId = $this->getLastCountId();
        $data = [
            'prefix_code' => $prefix->stock_transfer,
            'count_id' => ($lastCountId + 1),
        ];

        return view('stock-transfer.create', compact('data'));
    }

    /**
     * Get last count ID
     * */
    public function getLastCountId()
    {
        return StockTransfer::select('count_id')->orderBy('id', 'desc')->first()?->count_id ?? 0;
    }

    /**
     * List the orders
     *
     * @return \Illuminate\View\View
     */
    public function list(): View
    {
        return view('stock-transfer.list');
    }

    /**
     * Edit a Sale Order.
     *
     * @param int $id The ID of the expense to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id): View
    {
        $transfer = StockTransfer::with(['itemTransaction' => function ($query) {
            $query->where('unique_code', 'STOCK_TRANSFER')
                ->with([
                    'item',
                    'tax',
                    'batch.itemBatchMaster',
                    'itemSerialTransaction.itemSerialMaster',
                    'itemStockTransfer',
                    'item.itemGeneralQuantities',
                ]);
        }])->findOrFail($id);

        $transfer->operation = 'update';

        // Add formatted dates from ItemBatchMaster model
        $transfer->itemTransaction->each(function ($transaction) {
            if (!$transaction->batch?->itemBatchMaster) {
                return;
            }
            $batchMaster = $transaction->batch->itemBatchMaster;
            $batchMaster->mfg_date = $batchMaster->getFormattedMfgDateAttribute();
            $batchMaster->exp_date = $batchMaster->getFormattedExpDateAttribute();
        });

        // Item Details
        // Prepare item transactions with associated units
        $allUnits = CacheService::get('unit');

        $itemTransactions = $transfer->itemTransaction->map(function ($transaction) use ($allUnits) {
            $itemData = $transaction->toArray();

            // Use the getOnlySelectedUnits helper function
            $selectedUnits = getOnlySelectedUnits(
                $allUnits,
                $transaction->item->base_unit_id,
                $transaction->item->secondary_unit_id
            );

            // Add unitList to the item data
            $itemData['unitList'] = $selectedUnits->toArray();

            $warehouseStock = $transaction->item->itemGeneralQuantities->where('warehouse_id', $transaction->warehouse_id)->first();

            $itemData['currentStock'] = $warehouseStock ? $warehouseStock->quantity : 0;

            // Get item serial transactions with associated item serial master data
            $itemSerialTransactions = $transaction->itemSerialTransaction->map(function ($serialTransaction) {
                return $serialTransaction->itemSerialMaster->toArray();
            })->toArray();

            // Add itemSerialTransactions to the item data
            $itemData['itemSerialTransactions'] = $itemSerialTransactions;

            return $itemData;
        })->toArray();

        $itemTransactionsJson = json_encode($itemTransactions);

        $taxList = CacheService::get('tax')->toJson();

        return view('stock-transfer.edit', compact('taxList', 'transfer', 'itemTransactionsJson'));
    }

    /**
     * View Sale Order details
     *
     * @param int $id, the ID of the order
     * @return \Illuminate\View\View
     */
    public function details($id): View
    {
        $transfer = StockTransfer::with(['itemTransaction' => function ($query) {
            $query->where('unique_code', 'STOCK_TRANSFER')
                ->with([
                    'item',
                    'tax',
                    'batch.itemBatchMaster',
                    'itemSerialTransaction.itemSerialMaster'
                ]);
        }])->findOrFail($id);

        return view('stock-transfer.details', compact('transfer'));
    }


    /**
     * Store Records
     * */
    public function store(StockTransferRequest $request): JsonResponse
    {
        try {

            DB::beginTransaction();
            // Get the validated data from the expenseRequest
            $validatedData = $request->validated();


            if ($request->operation == 'save') {
                // Create a new sale record using Eloquent and save it
                $newTransfer = StockTransfer::create($validatedData);

                $request->request->add(['transfer_id' => $newTransfer->id]);
            } else {
                $fillableColumns = [
                    'transfer_date'         => $validatedData['transfer_date'],
                    'prefix_code'           => $validatedData['prefix_code'],
                    'count_id'              => $validatedData['count_id'],
                    'transfer_code'         => $validatedData['transfer_code'],
                    'note'                  => $validatedData['note'],
                ];

                $newTransfer = StockTransfer::findOrFail($validatedData['transfer_id']);
                $newTransfer->update($fillableColumns);

                /**
                 * Before deleting ItemTransaction data take the
                 * old data of the item_serial_master_id
                 * to update the item_serial_quantity
                 * */
                $this->previousHistoryOfItems = $this->itemTransactionService->getHistoryOfItems($newTransfer);
                $newTransfer->itemTransaction()->delete();
            }

            $request->request->add(['modelName' => $newTransfer]);

            /**
             * Save Table Items in Transfer Items Table
             * */
            $transferItemsArray = $this->saveTransferItems($request);
            if (!$transferItemsArray['status']) {
                throw new \Exception($transferItemsArray['message']);
            }

            /**
             * Record item transaction entry in one separate table
             * Model: ItemStockTransfer
             * */
            $itemStockTransfer = $this->recordItemTransactionInItemStockTransfer($request->modelName);
            if (!$itemStockTransfer) {
                throw new \Exception($transferItemsArray['message']);
            }

            /**
             * UPDATE HISTORY DATA
             * LIKE: ITEM SERIAL NUMBER QUNATITY, BATCH NUMBER QUANTITY, GENERAL DATA QUANTITY
             * */
            $this->itemTransactionService->updatePreviousHistoryOfItems($request->modelName, $this->previousHistoryOfItems);

            DB::commit();

            return response()->json([
                'status'    => true,
                'message' => __('app.record_saved_successfully'),
                'id' => $request->transfer_id,

            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    public function recordItemTransactionInItemStockTransfer($model)
    {
        $itemTransactions = $model->refresh('itemTransaction')->itemTransaction;

        $fromItemTransactionId  = null;
        $toItemTransactionId    = null;
        $fromWarehouseId    = null;

        if ($itemTransactions->count() > 0) {
            foreach ($itemTransactions as $transaction) {
                /**
                 * STOCK_TRANSFER
                 * serial #1
                 *
                 * STOCK_RECEIVE
                 * serial #2
                 * */
                if ($transaction->unique_code === ItemTransactionUniqueCode::STOCK_TRANSFER->value) {
                    //STOCK_TRANSFER
                    $fromItemTransactionId = $transaction->id;
                    $fromWarehouseId = $transaction->warehouse_id;
                } else {
                    //STOCK_RECEIVE
                    $toItemTransactionId    = $transaction->id;
                    $toWarehouseId          = $transaction->warehouse_id;

                    $insertInItemStockTransfer = ItemStockTransfer::create([
                        'stock_transfer_id'             =>  $model->id,
                        'from_item_transaction_id'      =>  $fromItemTransactionId,
                        'to_item_transaction_id'        =>  $toItemTransactionId,
                        'from_warehouse_id'             =>  $fromWarehouseId,
                        'to_warehouse_id'               =>  $toWarehouseId,
                        'item_id'                       =>  $transaction->item_id,
                    ]);
                    if (!$insertInItemStockTransfer) {
                        throw new \Exception('Failed to insert records in ItemStockTransfer model!!');
                    }


                    $fromItemTransactionId = null;
                    $toItemTransactionId = null;
                    $fromWarehouseId = null;
                    $toWarehouseId = null;
                }
            }
        }
        return true;
    }

    public function saveTransferItems($request)
    {
        $itemsCount = $request->row_count;

        /**
         * For One warehouse to another warehouse
         * */

        for ($i = 0; $i < $itemsCount; $i++) {
            //
            /**
             * If array record not exist then continue forloop
             * */
            if (!isset($request->item_id[$i])) {
                continue;
            }

            /**
             * Data index start from 0
             * */
            $itemDetails = Item::find($request->item_id[$i]);
            $itemName    = $itemDetails->name;

            //validate input Quantity
            $itemQuantity       = $request->quantity[$i];
            if (empty($itemQuantity) || $itemQuantity === 0 || $itemQuantity < 0) {
                return [
                    'status' => false,
                    'message' => ($itemQuantity < 0) ? __('item.item_qty_negative', ['item_name' => $itemName]) : __('item.please_enter_quantity_to_transfer', ['item_name' => $itemName]),
                ];
            }

            for ($j = 1; $j <= 2; $j++) {
                $uniqueCode = ($j == 1) ? ItemTransactionUniqueCode::STOCK_TRANSFER->value : ItemTransactionUniqueCode::STOCK_RECEIVE->value;

                $warehouseId = ($j == 1) ? $request->warehouse_id[$i] : $request->to_warehouse_id[$i];

                $worthItemsDetails = $this->itemTransactionService->worthItemsDetails($request->warehouse_id[$i], [$request->item_id[$i]]);

                $averageItemPurchasePrice = $worthItemsDetails['totalPurchaseCost'] > 0 ? $worthItemsDetails['totalPurchaseCost'] / $worthItemsDetails['totalAvailableQuantity'] : 0;
                /**
                 *
                 * Item Transaction Entry
                 * */
                $transaction = $this->itemTransactionService->recordItemTransactionEntry($request->modelName, [
                    'unique_code'               => $uniqueCode,
                    'warehouse_id'              => $warehouseId,
                    'transaction_date'          => $request->transfer_date,
                    'item_id'                   => $request->item_id[$i],
                    'tracking_type'             => $itemDetails->tracking_type,
                    'quantity'                  => $itemQuantity,
                    'unit_id'                   => $request->unit_id[$i],
                    'unit_price'                => $averageItemPurchasePrice,
                    'tax_id'                    => $itemDetails->tax_id,
                    'tax_type'                  => 'inclusive',
                    'mrp'                       => 0,
                    'total'                     => $averageItemPurchasePrice * $itemQuantity,
                ]);

                //return $transaction;
                if (!$transaction) {
                    throw new \Exception("Failed to record Item Transaction Entry!");
                }

                /**
                 * Tracking Type:
                 * regular
                 * batch
                 * serial
                 * */
                if ($itemDetails->tracking_type == 'serial') {
                    //Serial validate and insert records
                    if ($itemQuantity > 0) {
                        $jsonSerials = $request->serial_numbers[$i];
                        $jsonSerialsDecode = json_decode($jsonSerials);

                        /**
                         * Serial number count & Enter Quntity must be equal
                         * */
                        $countRecords = (!empty($jsonSerialsDecode)) ? count($jsonSerialsDecode) : 0;
                        if ($countRecords != $itemQuantity) {
                            throw new \Exception(__('item.opening_quantity_not_matched_with_serial_records') . '<br>Item Name:' . $itemDetails->name);
                        }

                        foreach ($jsonSerialsDecode as $serialNumber) {
                            $serialArray = [
                                'serial_code'       =>  $serialNumber,
                            ];

                            $serialTransaction = $this->itemTransactionService->recordItemSerials($transaction->id, $serialArray, $request->item_id[$i], $warehouseId, $uniqueCode);

                            if (!$serialTransaction) {
                                throw new \Exception(__('item.failed_to_save_serials'));
                            }
                        }
                    }
                } else if ($itemDetails->tracking_type == 'batch') {
                    //Serial validate and insert records
                    if ($itemQuantity > 0) {
                        /**
                         * Record Batch Entry for each batch
                         * */
                        $batchArray = [
                            'batch_no'              =>  $request->batch_no[$i],
                            'mfg_date'              =>  $request->mfg_date[$i] ? $this->toSystemDateFormat($request->mfg_date[$i]) : null,
                            'exp_date'              =>  $request->exp_date[$i] ? $this->toSystemDateFormat($request->exp_date[$i]) : null,
                            'model_no'              =>  $request->model_no[$i],
                            'mrp'                   =>  $request->mrp[$i] ?? 0,
                            'color'                 =>  $request->color[$i],
                            'size'                  =>  $request->size[$i],
                            'quantity'              =>  $itemQuantity,
                        ];

                        $batchTransaction = $this->itemTransactionService->recordItemBatches($transaction->id, $batchArray, $request->item_id[$i], $warehouseId, $uniqueCode);

                        if (!$batchTransaction) {
                            throw new \Exception(__('item.failed_to_save_batch_records'));
                        }
                    }
                } else {
                    //Regular item transaction entry already done before if() condition
                }
            }
        } //for end


        return ['status' => true];
    }


    /**
     * Datatabale
     * */
    public function datatableList(Request $request)
    {

        $data = StockTransfer::with('user')
            ->when($request->user_id, function ($query) use ($request) {
                return $query->where('created_by', $request->user_id);
            })
            ->when($request->from_date, function ($query) use ($request) {
                return $query->where('transfer_date', '>=', $this->toSystemDateFormat($request->from_date));
            })
            ->when($request->to_date, function ($query) use ($request) {
                return $query->where('transfer_date', '<=', $this->toSystemDateFormat($request->to_date));
            })
            ->when(!optional(auth()->user())->can('stock_transfer.can.view.other.users.stock.transfers'), function ($query) use ($request) {
                return $query->where('created_by', auth()->user()->id);
            });

        return DataTables::of($data)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $searchTerm = $request->search['value'];
                    $query->where(function ($q) use ($searchTerm) {
                        $q->WhereHas('user', function ($userQuery) use ($searchTerm) {
                            $userQuery->where('username', 'like', "%{$searchTerm}%")
                                ->orWhere('transfer_date', 'like', "%{$searchTerm}%")
                                ->orWhere('transfer_code', 'like', "%{$searchTerm}%")
                            ;
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
            ->addColumn('transfer_date', function ($row) {
                return $row->formatted_transfer_date;
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $editUrl = route('stock_transfer.edit', ['id' => $id]);
                $deleteUrl = route('stock_transfer.delete', ['id' => $id]);
                $detailsUrl = route('stock_transfer.details', ['id' => $id]);
                $printUrl = route('stock_transfer.print', ['id' => $id]);
                $pdfUrl = route('stock_transfer.pdf', ['id' => $id]);

                $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> ' . __('app.edit') . '</a>
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
     * Delete Sale Records
     * @return JsonResponse
     * */
    public function delete(Request $request): JsonResponse
    {

        DB::beginTransaction();

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = StockTransfer::find($recordId);
            if (!$record) {
                // Invalid record ID, handle the error (e.g., show a message, log, etc.)
                return response()->json([
                    'status'    => false,
                    'message' => __('app.invalid_record_id', ['record_id' => $recordId]),
                ]);
            }
            // You can perform additional validation checks here if needed before deletion
        }

        /**
         * All selected record IDs are valid, proceed with the deletion
         * Delete all records with the selected IDs in one query
         * */


        try {
            // Attempt deletion (as in previous responses)
            StockTransfer::whereIn('id', $selectedRecordIds)->chunk(100, function ($transfers) {
                foreach ($transfers as $transfer) {
                    /**
                     * Before deleting ItemTransaction data take the
                     * old data of the item_serial_master_id
                     * to update the item_serial_quantity
                     * */
                    $this->previousHistoryOfItems = $this->itemTransactionService->getHistoryOfItems($transfer);

                    $itemIdArray = [];

                    //Purchasr Item delete and update the stock
                    foreach ($transfer->itemTransaction as $itemTransaction) {
                        //get item id
                        $itemId = $itemTransaction->item_id;

                        //delete item Transactions
                        $itemTransaction->delete();

                        $itemIdArray[] = $itemId;
                    } //transfer account

                    /**
                     * UPDATE HISTORY DATA
                     * LIKE: ITEM SERIAL NUMBER QUNATITY, BATCH NUMBER QUANTITY, GENERAL DATA QUANTITY
                     * */
                    $this->itemTransactionService->updatePreviousHistoryOfItems($transfer, $this->previousHistoryOfItems);

                    //Delete Sale
                    $transfer->delete();

                    //Update stock update in master
                    if (count($itemIdArray) > 0) {
                        foreach ($itemIdArray as $id) {
                            $this->itemService->updateItemStock($itemId);
                        }
                    }
                } //transfers

            }); //chunk

            //Delete Sale
            $deletedCount = StockTransfer::whereIn('id', $selectedRecordIds)->delete();

            DB::commit();

            return response()->json([
                'status'    => true,
                'message' => __('app.record_deleted_successfully'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response()->json([
                'status'    => false,
                'message' => __('app.cannot_delete_records'),
            ], 409);
        }
    }

    /**
     * Print Stock Transfer
     *
     * @param int $id, the ID of the sale
     * @return \Illuminate\View\View
     */
    public function print($id, $isPdf = false): View
    {

        $transfer = StockTransfer::with(['itemTransaction' => function ($query) {
            $query->where('unique_code', 'STOCK_TRANSFER')
                ->with([
                    'item',
                    'tax',
                    'batch.itemBatchMaster',
                    'itemSerialTransaction.itemSerialMaster'
                ]);
        }])->findOrFail($id);

        //Batch Tracking Row count for invoice columns setting
        $batchTrackingRowCount = (new GeneralDataService())->getBatchTranckingRowCount();

        $printData = [
            'name' => __('warehouse.stock_transfer_details'),
        ];

        return view('print.stock-transfer.print', compact('isPdf', 'printData', 'transfer', 'batchTrackingRowCount'));
    }


    /**
     * Generate PDF using View: print() method
     * */
    public function generatePdf($id, $destination = 'D')
    {
        $random = uniqid();

        $html = $this->print($id, isPdf: true);

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
        $fileName = 'Stock-Transfer-' . $id . '-' . $random . '.pdf';

        $mpdf->Output($fileName, $destination);
    }
}
