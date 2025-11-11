<?php

namespace App\Http\Controllers\Items;

use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Requests\ItemRequest;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Items\Item;
use App\Models\Items\ItemSerial;
use App\Models\Items\ItemBatchTransaction;
use App\Models\Items\ItemTransaction;
use App\Models\Items\ItemBatchMaster;
use App\Models\Items\ItemSerialMaster;
use App\Models\Items\ItemSerialQuantity;
use App\Models\Items\ItemBatchQuantity;
use App\Models\Tax;
use App\Models\Unit;
use Carbon\Carbon;
use App\Models\Warehouse;
use App\Services\ItemTransactionService;
use App\Services\ItemService;
use App\Services\CacheService;
use App\Services\AccountTransactionService;
use App\Enums\ItemTransactionUniqueCode;
use App\Models\Party\Party;

use Spatie\Image\Image;

class ItemController extends Controller
{
    use FormatsDateInputs;

    use FormatNumber;

    public $itemTransactionService;

    public $itemService;

    public $accountTransactionService;

    public $previousHistoryOfItems;

    public function __construct(
        ItemTransactionService $itemTransactionService,
        ItemService $itemService,
        AccountTransactionService $accountTransactionService,
    ) {
        $this->itemTransactionService = $itemTransactionService;
        $this->itemService = $itemService;
        $this->accountTransactionService = $accountTransactionService;
        $this->previousHistoryOfItems = [];
    }

    public function test()
    {
        $this->itemTransactionService->updateItemGeneralQuantityWarehouseWise(2193);
        return response()->json(['message' => 'success']);

        $items = Item::all();
        foreach ($items as $item) {
            $this->itemTransactionService->updateItemGeneralQuantityWarehouseWise($item->id);
        }
        return response()->json(['message' => 'success']);
    }
    /**
     * Create a new item.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        //return $this->test();
        $data = [
            'count_id' => str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
        ];
        return view('items.item.create', compact('data'));
    }
    /**
     * Get last count ID
     * */
    public function getLastCountId()
    {
        return Item::select('count_id')->orderBy('id', 'desc')->first()?->count_id ?? 0;
    }

    /**
     * Edit a item.
     *
     * @param int $id The ID of the item to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id): View
    {

        $item = Item::find($id);
        $transaction = $item->itemTransaction()->first(); // no need ->get()->first()
        $transactionId = ($transaction) ? $transaction->id : null;

        /**
         * Get Batch Records from ItemBatch Model using service Class
         * */
        $batchArray = $this->itemTransactionService->getBatchWiseRecords($transactionId);
        $batchJson = count($batchArray) ? json_encode($batchArray) : '';

        /**
         * Get Serial Records from ItemSerial Model using service Class
         * */
        $serviceArray = $this->itemTransactionService->getSerialWiseRecords($transactionId);
        $serviceJson = count($serviceArray) ? json_encode($serviceArray) : '';

        /**
         * Todays Date
         * */
        $todaysDate = $this->toUserDateFormat(now());

        return view('items.item.edit', compact('item', 'transaction', 'batchJson', 'serviceJson', 'todaysDate'));
    }

    public function store(ItemRequest $request)
    {
        try {
            DB::beginTransaction();

            $filename = null;
            $jsonSerialsDecode = [];

            $validatedData = $request->validated();
            $operation = $request->operation;

            // 1. Handle image upload
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $filename = $this->uploadImage($request->file('image'));
            }

            // 2. Prepare fields to save
            $recordsToSave = [
                'is_service'        =>  $request->is_service,
                'item_code'         =>  $request->item_code,
                'name'              =>  $request->name,
                'description'       =>  $request->description,
                'hsn'               =>  $request->hsn,
                'sku'               =>  $request->sku,
                'item_category_id'  =>  $request->item_category_id,
                'brand_id'          =>  $request->brand_id,
                'base_unit_id'      =>  $request->base_unit_id,
                'secondary_unit_id' =>  $request->secondary_unit_id,
                'conversion_rate'   => ($request->base_unit_id == $request->secondary_unit_id) ? 1 : $request->conversion_rate,

                'sale_price'                =>  $request->sale_price,
                'is_sale_price_with_tax'    =>  $request->is_sale_price_with_tax,
                'sale_price_discount'       =>  $request->sale_price_discount,
                'sale_price_discount_type'  =>  $request->sale_price_discount_type,
                'purchase_price'            =>  $request->purchase_price,
                'is_purchase_price_with_tax' =>  $request->is_purchase_price_with_tax,
                'tax_id'                    =>  $request->tax_id,
                'wholesale_price'           =>  $request->wholesale_price,
                'is_wholesale_price_with_tax' => $request->is_wholesale_price_with_tax,

                'profit_margin'             =>  $request->profit_margin,
                'mrp'                       =>  $request->mrp,
                'msp'                       =>  $request->msp,

                'tracking_type'             =>  $request->tracking_type,
                'min_stock'                 =>  $request->min_stock,
                'item_location'             =>  $request->item_location,
                'status'                    =>  $request->status,
            ];

            // 3. CREATE vs UPDATE
            if ($request->operation == 'save') {
                // 👉 First time create
                $recordsToSave['count_id']   = $this->getLastCountId() + 1;
                $recordsToSave['image_path'] = $filename;
                $itemModel = Item::create($recordsToSave);

                // ✅ Attach immediately
                $request->attributes->set('itemModel', $itemModel);
            } else {
                // 👉 Update existing item
                $itemModel = Item::find($request->item_id);
                if (!$itemModel) {
                    throw new \Exception('Item not found for update.');
                }

                if (!empty($filename)) {
                    $recordsToSave['image_path'] = $filename;
                }

                $this->previousHistoryOfItems = $this->itemTransactionService->getHistoryOfItems($itemModel);

                $itemModel->update($recordsToSave);

                // ✅ Attach immediately
                $request->attributes->set('itemModel', $itemModel);

                // ✅ Adjust opening quantity if changed
                if ($request->has('opening_quantity')) {
                    $newQty = (float) $request->opening_quantity;

                    $oldTransaction = $itemModel->itemTransaction()
                        ->where('transaction_type', ItemTransactionUniqueCode::ITEM_OPENING->value)
                        ->first();

                    if ($oldTransaction) {
                        $oldQty = (float) $oldTransaction->quantity;
                        if ($newQty != $oldQty) {
                            $oldTransaction->quantity = $newQty;
                            $oldTransaction->unit_price = $request->at_price;
                            $oldTransaction->warehouse_id = $request->warehouse_id;
                            $oldTransaction->save();

                            $this->itemTransactionService->updateItemGeneralQuantityWarehouseWise($itemModel->id);
                        }
                    } else {
                        if ($newQty > 0) {
                            $transaction = $this->recordInItemTransactionEntry($request);
                            if (!$transaction) {
                                throw new \Exception('Failed to record opening transaction.');
                            }
                        }
                    }
                }
            }

            // 4. Handle tracking types (serial / batch / regular)
            if ($request->tracking_type == 'serial') {
                if ($request->opening_quantity > 0) {
                    $jsonSerials = $request->serial_number_json;
                    $jsonSerialsDecode = json_decode($jsonSerials);
                    $countRecords = (!empty($jsonSerialsDecode)) ? count($jsonSerialsDecode) : 0;
                    if ($countRecords != $request->opening_quantity) {
                        throw new \Exception(__('item.opening_quantity_not_matched_with_serial_records'));
                    }
                    $transaction = $this->recordInItemTransactionEntry($request);
                    if (!$transaction) {
                        throw new \Exception(__('item.failed_to_record_item_transactions'));
                    }
                    foreach ($jsonSerialsDecode as $serialNumber) {
                        $serialArray = ['serial_code' =>  $serialNumber];
                        $serialTransaction = $this->itemTransactionService->recordItemSerials(
                            $transaction->id,
                            $serialArray,
                            $request->attributes->get('itemModel')->id,
                            $request->warehouse_id,
                            ItemTransactionUniqueCode::ITEM_OPENING->value
                        );
                        if (!$serialTransaction) {
                            throw new \Exception(__('item.failed_to_save_serials'));
                        }
                    }
                }
            } else if ($request->tracking_type == 'batch') {
                if ($request->opening_quantity > 0) {
                    $jsonBatches = $request->batch_details_json;
                    $jsonBatchDecode = json_decode($jsonBatches);
                    $totalOpeningQuantity = (!empty($jsonBatchDecode)) ? array_sum(array_column($jsonBatchDecode, 'openingQuantity')) : 0;
                    if ($totalOpeningQuantity != $request->opening_quantity) {
                        throw new \Exception(__('item.opening_quantity_not_matched_with_batch_records'));
                    }
                    $transaction = $this->recordInItemTransactionEntry($request);
                    if (!$transaction) {
                        throw new \Exception(__('item.failed_to_record_item_transactions'));
                    }
                    foreach ($jsonBatchDecode as $batchRecord) {
                        $batchArray = [
                            'batch_no'  => $batchRecord->batchNo,
                            'mfg_date'  => $batchRecord->mfgDate ? $this->toSystemDateFormat($batchRecord->mfgDate) : null,
                            'exp_date'  => $batchRecord->expDate ? $this->toSystemDateFormat($batchRecord->expDate) : null,
                            'model_no'  => $batchRecord->modelNo,
                            'mrp'       => $batchRecord->mrp ?? 0,
                            'color'     => $batchRecord->color,
                            'size'      => $batchRecord->size,
                            'quantity'  => $batchRecord->openingQuantity,
                        ];
                        $batchTransaction = $this->itemTransactionService->recordItemBatches(
                            $transaction->id,
                            $batchArray,
                            $request->attributes->get('itemModel')->id,
                            $request->warehouse_id,
                            ItemTransactionUniqueCode::ITEM_OPENING->value
                        );
                        if (!$batchTransaction) {
                            throw new \Exception(__('item.failed_to_save_batch_records'));
                        }
                    }
                }
            } else {
                if ($request->opening_quantity > 0 && $request->operation == 'save') {
                    $transaction = $this->recordInItemTransactionEntry($request);
                    if (!$transaction) {
                        throw new \Exception(__('item.failed_to_record_item_transactions'));
                    }
                }
            }

            // 5. Handle Offer Items (Combos)
            $offerItemsJson = $request->input('offer_items_json');
            if (!empty($offerItemsJson)) {
                $components = json_decode($offerItemsJson, true) ?: [];
                $components = array_values(array_filter($components, function ($r) {
                    return isset($r['item_id']) && (float)($r['quantity'] ?? 0) >= 1;
                }));
                if (count($components) < 2) {
                    throw new \Exception('Offer/Combo must contain at least 2 items.');
                }
                \App\Models\Items\ItemOfferComponent::where('offer_item_id', $itemModel->id)->delete();
                $rows = [];
                foreach ($components as $c) {
                    $rows[] = [
                        'offer_item_id'     => $itemModel->id,
                        'component_item_id' => $c['item_id'],
                        'quantity'          => $c['quantity'],
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
                if (!empty($rows)) {
                    \DB::table('item_offer_components')->insert($rows);
                }
            }

            // 6. Update history + average purchase price
            $this->itemTransactionService->updatePreviousHistoryOfItems($request->attributes->get('itemModel'), $this->previousHistoryOfItems);
            $this->itemTransactionService->updateItemMasterAveragePurchasePrice([$request->attributes->get('itemModel')->id]);

            DB::commit();

            return response()->json([
                'status'    => true,
                'message'   => __('app.record_saved_successfully'),
                'id'        => $request->attributes->get('itemModel')->id,
                'name'      => $request->attributes->get('itemModel')->name,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function recordInItemTransactionEntry($request)
    {
        $itemModel = $request->attributes->get('itemModel');
        if (!$itemModel) {
            throw new \Exception('Item model not found in request.');
        }

        $transaction = $this->itemTransactionService->recordItemTransactionEntry($itemModel, [
            'item_id'        => $itemModel->id,
            'transaction_date' => $request->transaction_date,
            'warehouse_id'   => $request->warehouse_id,
            'tracking_type'  => $request->tracking_type,
            'mrp'            => 0,
            'quantity'       => $request->opening_quantity,
            'unit_id'        => $request->base_unit_id,
            'unit_price'     => $request->at_price,
            'discount_type'  => 'percentage',
            'tax_id'         => $request->tax_id,
            'tax_type'       => ($request->is_sale_price_with_tax) ? 'inclusive' : 'exclusive',
            'total'          => $request->opening_quantity * $request->at_price,
        ]);

        return $transaction;
    }



    private function uploadImage($image): string
    {
        // Generate a unique filename for the image
        $random = uniqid();
        $filename = $random . '.' . $image->getClientOriginalExtension();
        $directory = 'images/items';

        // Create the directory if it doesn't exist
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Store the file in the 'items' directory with the specified filename
        Storage::disk('public')->putFileAs($directory, $image, $filename);

        // Create Thumbnail
        // Generate temporary file path for thumbnail
        $thumbnailDirectory = $directory . '/thumbnail';
        if (!Storage::disk('public')->exists($thumbnailDirectory)) {
            Storage::disk('public')->makeDirectory($thumbnailDirectory);
        }


        // Load the image
        $imagePath = Storage::disk('public')->path($directory . '/' . $filename);

        //Thumbnai Path
        $thumbnailPath = Storage::disk('public')->path($thumbnailDirectory . '/' . $filename);

        //Load Actual Image
        $thumbImage = Image::load($imagePath)
            ->width(200)
            ->height(200)
            ->save($thumbnailPath);

        // Return both the original filename and the thumbnail data URI
        return $filename;
    }

    public function list(): View
    {
        return view('items.item.list');
    }

    public function datatableList(Request $request)
    {
        $warehouseId = request('warehouse_id');
        $data = Item::with(['tax', 'itemGeneralQuantities', 'brand', 'category'])
            ->when($request->item_category_id, function ($query) use ($request) {
                return $query->where('item_category_id', $request->item_category_id);
            })
            ->when($request->brand_id, function ($query) use ($request) {
                return $query->where('brand_id', $request->brand_id);
            })
            ->when($request->tracking_type, function ($query) use ($request) {
                return $query->where('tracking_type', $request->tracking_type);
            })
            ->when(isset($request->is_service), function ($query) use ($request) {
                if ($request->is_service == 0) {
                    return $query->where('is_service', 0);
                } else if ($request->is_service == 1) {
                    return $query->where('is_service', 1);
                }
            })
            ->when($request->created_by, function ($query) use ($request) {
                return $query->where('created_by', $request->created_by);
            });

        return DataTables::of($data)
            ->filter(function ($query) use ($request) {
                if ($request->has('search')) {
                    $searchTerm = $request->search['value'];
                    $query->where(function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('description', 'like', "%{$searchTerm}%")
                            ->orWhere('sku', 'like', "%{$searchTerm}%")
                            ->orWhere('sale_price', 'like', "%{$searchTerm}%")
                            ->orWhere('item_code', 'like', "%{$searchTerm}%")
                            ->orWhere('item_location', 'like', "%{$searchTerm}%")
                            ->orWhere('tracking_type', 'like', "%{$searchTerm}%")
                            // Add more columns as needed

                            ->orWhereHas('tax', function ($taxQuery) use ($searchTerm) {
                                $taxQuery->where('name', 'like', "%{$searchTerm}%");
                            })
                            ->orWhereHas('brand', function ($brandQuery) use ($searchTerm) {
                                $brandQuery->where('name', 'like', "%{$searchTerm}%");
                            })
                            ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                                $categoryQuery->where('name', 'like', "%{$searchTerm}%");
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
            ->addColumn('tracking_type', function ($row) {
                return ucfirst($row->tracking_type);
            })
            ->addColumn('sale_price', function ($row) {
                return $this->formatWithPrecision($row->sale_price);
            })
            ->addColumn('brand_name', function ($row) {
                return $row->brand->name ?? '';
            })
            ->addColumn('item_location', function ($row) {
                return $row->item_location ?? '';
            })
            ->addColumn('category_name', function ($row) {
                return $row->category->name;
            })
            ->addColumn('purchase_price', function ($row) {
                return $this->formatWithPrecision($row->purchase_price);
            })
            ->addColumn('current_stock', function ($row) use ($warehouseId) {
                if ($warehouseId) {
                    $warehouseQuantity = $row->itemGeneralQuantities
                        ->where('warehouse_id', $warehouseId)
                        ->first();

                    $quantity = $warehouseQuantity ? $warehouseQuantity->quantity : 0;
                } else {
                    $quantity = $row->current_stock;
                }
                //return $this->formatQuantity($quantity);
                return $this->itemService->getQuantityInUnit($quantity, $row->id);
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $editUrl = route('item.edit', ['id' => $id]);
                $deleteUrl = route('item.delete', ['id' => $id]);
                $transactionUrl = route('item.transaction.list', ['id' => $id]);


                $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> ' . __('app.edit') . '</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="' . $transactionUrl . '"><i class="bi bi-trash"></i><i class="bx bx-transfer-alt"></i> ' . __('app.transactions') . '</a>
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

    public function delete(Request $request): JsonResponse
    {

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = Item::find($recordId);
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
            Item::whereIn('id', $selectedRecordIds)->chunk(100, function ($items) {
                foreach ($items as $item) {
                    //Load Item Transactions like Opening Balance
                    $itemTransactions = $item->itemTransaction;

                    //Delete only if Opening Stock transaction exist, else don't allow to delete
                    $filter = ItemTransaction::where('item_id', $item->id)
                        ->whereNotIn('unique_code', [ItemTransactionUniqueCode::ITEM_OPENING->value])
                        ->get();
                    if ($filter->count() == 0) {
                        foreach ($itemTransactions as $itemTransaction) {
                            //Delete Item Account Transactions
                            $itemTransaction->accountTransaction()->delete();

                            //Delete Item Transaction
                            $itemTransaction->delete();
                        }
                    } else {
                        throw new \Exception(__('app.cannot_delete_records') . "<br>Item Name: " . $item->name);
                    }
                }
            });

            // Delete Complete Item
            $itemModel = Item::whereIn('id', $selectedRecordIds)->delete();

            return response()->json([
                'status'    => true,
                'message' => __('app.record_deleted_successfully'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {

            return response()->json([
                'status'    => false,
                'message' => __('app.cannot_delete_records'),
            ], 409);
        }
    }

    /**
     * Get Item Records
     * @return JsonResponse
     * */
    function getRecords(Request $request): JsonResponse
    {
        $selectedRecordId = $request->input('item_id');

        $record = Item::where('id', $selectedRecordId)
            ->select('id', 'name', 'description', 'unit_price', 'tax_id', 'tax_type', 'status')
            ->first();
        /**
         * If no records
         * @return JsonResponse
         * */
        if ($record->count() == 0) {
            return response()->json([
                'status'    => false,
                'message' => __('app.record_not_found'),
            ]);
        }
        /**
         * Return JsonResponse with Actual Records
         * */

        $preparedData = [
            'id'                => $record->id,
            'name'              => $record->name,
            'description'       => $record->description ?? '',
            'quantity'          => 1,
            'unit_price'        => $record->unit_price,
            'total_price'       => $record->total_price,
            'discount'          => 0,
            'discount_type'     => 'percentage',
            'discount_amount'   => 0,
            'total_price_after_discount'   => 0,
            'start_at'          => null,
            'end_at'            => null,
            'tax_id'            => $record->tax_id,
            'tax_type'          => $record->tax_type,
            'tax_amount'        => 0,
            'status'            => $record->status,
            'assigned_user_id'  => $record->assigned_user_id ?? '',
            'assigned_user_note' => $record->assigned_user_note ?? '',
            'taxList'           => CacheService::get('tax'),
        ];

        return response()->json([
            'status'    => true,
            'message' => null,
            'data' => $preparedData,
        ]);
    }

    /**
     * Ajax Response
     * Search for Select2 Bar list
     * */
    function getAjaxSearchBarList()
    {
        $search = request('search');
        $categoryId = request('category_id');

        $items = Item::where(function ($query) use ($search) {
            $query->whereRaw('UPPER(name) LIKE ?', ['%' . strtoupper($search) . '%']);
        })
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->where('item_category_id', $categoryId);
            })
            ->select('id', 'name')
            ->get();

        $response = [
            'results' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->name,
                ];
            })->toArray(),
        ];
        return json_encode($response);
    }

    /**
     * Ajax Response
     * Search for Select2 Bar list
     * */
    function getAjaxItemBatchSearchBarList()
    {
        $search = request('search');
        $itemId = request('item_id');

        $items = ItemBatchMaster::where(function ($query) use ($search) {
            $query->whereRaw('UPPER(batch_no) LIKE ?', ['%' . strtoupper($search) . '%']);
        })
            ->when($itemId, function ($query) use ($itemId) {
                return $query->where('item_id', $itemId);
            })
            ->select('id', 'batch_no')
            ->get();

        $response = [
            'results' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->batch_no,
                ];
            })->toArray(),
        ];
        return json_encode($response);
    }

    /**
     * Ajax Response
     * Search for Select2 Bar list
     * */
    function getAjaxItemSerialSearchBarList()
    {
        $search = request('search');
        $itemId = request('item_id');

        $items = ItemSerialMaster::where(function ($query) use ($search) {
            $query->whereRaw('UPPER(serial_code) LIKE ?', ['%' . strtoupper($search) . '%']);
        })
            ->when($itemId, function ($query) use ($itemId) {
                return $query->where('item_id', $itemId);
            })
            ->select('id', 'serial_code')
            ->get();

        $response = [
            'results' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->serial_code,
                ];
            })->toArray(),
        ];
        return json_encode($response);
    }

    /**
     * Ajax Response
     * Search Bar list
     * */
    function getAjaxItemSearchBarList()
    {
        $search = request('search');

        /**
         * Party Wise Wholesale & Retail Price listing in Sales
         * */
        $showWholesalePrice = Party::select('is_wholesale_customer')
            ->find(request('party_id'))
            ?->is_wholesale_customer ?? false;

        $itemMaster = Item::with('tax', 'brand')
            ->where('name', 'LIKE', "%{$search}%")
            ->orWhere('item_code', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get();
        $response = $this->returnRequiredFormatData($itemMaster, $showWholesalePrice);
        return json_encode($response);
    }

    public function returnItemJsonData($itemId)
    {
        $itemMaster = Item::with('tax', 'brand')->whereId($itemId)
            ->limit(10)
            ->get();
        return $this->returnRequiredFormatData($itemMaster);
    }

    public function getAjaxItemSearchPOSList()
    {
        $search = request('search');
        $categoryId = request('item_category_id');
        $brandId = request('item_brand_id');
        $warehouseId = request('warehouse_id');
        $page = request('page', 1); // Get the page from the request, default to 1
        $sortOrder = request('sort', 'a_to_z'); // Get the sorting order from the request
        $showWholesalePrice = Party::select('is_wholesale_customer')
            ->find(request('party_id'))
            ?->is_wholesale_customer ?? false;

        // Modified Query: Join with item_serial_masters and adjust search condition
        $itemMaster = Item::with([
            'tax',
            'brand',
            'itemGeneralQuantities' => function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }
        ])
            // Join with item_serial_masters to access serial_code
            ->leftJoin('item_serial_masters', 'items.id', '=', 'item_serial_masters.item_id')
            // Modify the where condition to include serial_code search
            ->where(function ($query) use ($search) {
                $query->where('items.name', 'LIKE', "%{$search}%")
                    ->orWhere('items.item_code', 'LIKE', "%{$search}%")
                    ->orWhere('item_serial_masters.serial_code', 'LIKE', "%{$search}%"); // Search serial_code
            })
            ->select('items.*') // Select only items columns to avoid conflicts
            ->distinct() // Ensure unique items if matched by multiple criteria or serials
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->where('items.item_category_id', $categoryId); // Qualify column name
            })
            ->when($brandId, function ($query) use ($brandId) {
                return $query->where('items.brand_id', $brandId); // Qualify column name
            })
            ->orderBy('items.name', $sortOrder === 'z_to_a' ? 'desc' : 'asc') // Apply sorting order, qualify column
            ->paginate(15, ['*'], 'page', $page); // Use pagination for infinite scroll

        $response = $this->returnRequiredFormatData($itemMaster, $showWholesalePrice);
        return response()->json($response);
    }
    function returnRequiredFormatData($itemMaster, $showWholesalePrice = false)
    {
        $isPermiteToViewPurchasePrice = (bool) auth()->user()->can('general.allow.to.view.item.purchase.price');

        $warehouseId = request('warehouse_id');

        // Cache the Tax list
        $taxList = \App\Services\CacheService::get('tax');

        // Cache the Unit list
        $unitList = \App\Services\CacheService::get('unit');

        $itemMaster->load('itemGeneralQuantities', 'brand');

        return $itemMaster->map(function ($item) use ($taxList, $unitList, $warehouseId, $showWholesalePrice, $isPermiteToViewPurchasePrice) {

            $warehouseStock = $item->itemGeneralQuantities->where('warehouse_id', $warehouseId)->first();

            $warehouseName = '';
            if ($warehouseId) {
                $warehouseName = Warehouse::find($warehouseId)?->name ?? '--';
            }

            $itemsArray = [
                'id'                        => $item->id,
                'name'                      => $item->name,
                'description'               => $item->description ?? '',
                'brand_name'                => $item->brand->name ?? '--',
                'item_code'                 => $item->item_code ?? '',
                'is_service'                => $item->is_service,
                'selected_unit_id'          => $item->base_unit_id,
                'base_unit_id'              => $item->base_unit_id,
                'secondary_unit_id'         => $item->secondary_unit_id,
                'conversion_rate'           => $item->conversion_rate,
                'created_at'                => $item->created_at->toIso8601String(),

                'purchase_price'            => ($isPermiteToViewPurchasePrice) ?
                    (($item->is_purchase_price_with_tax == 1) ?
                        calculatePrice($item->purchase_price, $item->tax->rate, true) :
                        $item->purchase_price) : 0,
                'is_purchase_price_with_tax' => $item->is_purchase_price_with_tax,
                'tax_id'                    => $item->tax_id,
                'tracking_type'             => $item->tracking_type,
                'item_location'             => $item->item_location,

                'current_stock'             => $warehouseStock ? $warehouseStock->quantity : 0,
                'stock_in_unit'             => (new ItemService())
                    ->getQuantityInUnit($warehouseStock ? $warehouseStock->quantity : 0, $item->id),
                'warehouse_name'            => $warehouseName,

                'image_path'                => $item->image_path ?? 'no',
                'mrp'                       => $item->mrp,
                'quantity'                  => 1,
                'taxList'                   => $taxList,
                'unitList'                  => getOnlySelectedUnits($unitList, $item->base_unit_id, $item->secondary_unit_id),

                'purchase_price_discount'   => 0,
                'discount_type'             => 'percentage',
                'total_price_after_discount' => 0,
                'discount_amount'           => 0,
                'tax_amount'                => 0,
                'warehouse_id'              => $warehouseId,
            ];

            if ($showWholesalePrice) {
                $itemsArray['sale_price'] = ($item->is_wholesale_price_with_tax == 1) ?
                    calculatePrice($item->wholesale_price, $item->tax->rate, true) : $item->wholesale_price;
                $itemsArray['is_sale_price_with_tax'] = $item->is_wholesale_price_with_tax;
                $itemsArray['sale_price_discount'] = 0;
                $itemsArray['sale_price_discount_type'] = 0;
            } else {
                $itemsArray['sale_price'] = ($item->is_sale_price_with_tax == 1) ?
                    calculatePrice($item->sale_price, $item->tax->rate, true) : $item->sale_price;
                $itemsArray['is_sale_price_with_tax'] = $item->is_sale_price_with_tax;
                $itemsArray['sale_price_discount'] = (app('company')['show_discount']) ? $item->sale_price_discount : 0;
                $itemsArray['sale_price_discount_type'] = $item->sale_price_discount_type;
            }

            return $itemsArray;
        })->toArray();
    }
    /**
     * Ajax Response
     * Search Bar list
     * */
    function getAjaxItemSerialIMEISearchBarList()
    {

        $search = request('search');

        $warehouseId = request('warehouse_id');

        $itemId = request('item_id');

        $serialMaster = ItemSerialQuantity::with('itemSerialMaster.item')
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('itemSerialMaster', function ($query) use ($search) {
                    $query->whereRaw('UPPER(serial_code) LIKE ?', ['%' . strtoupper($search) . '%']);
                });
            })
            ->get();

        $response = $serialMaster->map(function ($serial) {
            return [
                'id'                        => $serial->id,
                'name'                      => $serial->itemSerialMaster->serial_code,
                'item_name'                 => $serial->itemSerialMaster->item->name,
            ];
        })->toArray();

        return json_encode($response);
    }

    public function getAjaxItemBatchStockList()
    {
        $warehouseId = request('warehouse_id');

        $itemId = request('item_id');

        $batchQuantity = ItemBatchQuantity::with('itemBatchMaster.item')
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->get();

        $itemData = $this->returnItemJsonData($itemId);

        $response = $batchQuantity->map(function ($quantity) use ($itemData) {
            return [
                'id'                        => $quantity->id,
                'item_name'                 => $quantity->itemBatchMaster->item->name,
                'batchNo'                   => $quantity->itemBatchMaster->batch_no ?? '',
                'mfgDate'                   => ($quantity->itemBatchMaster->mfg_date) ? $quantity->itemBatchMaster->formatted_mfg_date : '',
                'expDate'                   => ($quantity->itemBatchMaster->exp_date) ? $quantity->itemBatchMaster->formatted_exp_date : '',
                'modelNo'                   => $quantity->itemBatchMaster->model_no ?? '',
                'mrp'                       => $this->formatWithPrecision($quantity->itemBatchMaster->mrp, comma: false),
                'color'                     => $quantity->itemBatchMaster->color ?? '',
                'size'                      => $quantity->itemBatchMaster->size ?? '',
                'availableStock'            => $this->formatQuantity($quantity->quantity),
                'saleQuantity'              => '',
                'itemData'                  => $itemData,
            ];
        });

        $response = $response->toArray();

        return json_encode($response);
    }

    public function lowStockCount(Request $request)
    {
        $company = app('company');

        if (empty($company['enable_minimum_stock_qty']) || empty($company['minimum_stock_qty'])) {
            return response()->json([
                'enabled' => false,
                'count' => 0,
                'items' => [],
            ]);
        }

        $threshold = (int) $company['minimum_stock_qty'];

        $lowStockItems = Item::where('current_stock', '<=', $threshold)
            ->select('id', 'name', 'current_stock')
            ->orderBy('current_stock', 'asc')
            ->get();

        return response()->json([
            'enabled' => true,
            'count' => $lowStockItems->count(),
            'items' => $lowStockItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'stock' => $item->current_stock,
                ];
            }),
        ]);
    }
}
