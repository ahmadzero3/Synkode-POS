<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Models\Items\ItemBatchTransaction;
use App\Models\Items\ItemSerial;
use App\Services\ItemService;
use App\Models\Items\Item;
use App\Models\Items\ItemBatchMaster;
use App\Models\Items\ItemBatchQuantity;
use App\Models\Items\ItemSerialMaster;
use App\Models\Items\ItemSerialTransaction;
use App\Models\Items\ItemSerialQuantity;
use App\Models\Items\ItemGeneralQuantity;
use App\Models\Items\ItemTransaction;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Sale\SaleOrder;
use App\Models\Sale\Sale;
use App\Models\Sale\SaleReturn;
use App\Models\StockTransfer;
use App\Enums\ItemTransactionUniqueCode;
use App\Models\Sale\Quotation;
use App\Models\StockAdjustment;
use App\Models\User;

class ItemTransactionService
{

    use FormatNumber;

    use FormatsDateInputs;

    var $itemService;

    var $canAllowNegativeStockBilling;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
        $this->canAllowNegativeStockBilling = app('company')['allow_negative_stock_billing'];
    }
    public function transactionUniqueCode($model)
    {

        $baseNameOfClass = class_basename($model);

        if ($baseNameOfClass == 'Item') {
            //opening Stock recording
            $uniqueCode = ItemTransactionUniqueCode::ITEM_OPENING->value;
        } else if ($baseNameOfClass == 'PurchaseOrder') {
            // Purchase Order Entry
            $uniqueCode = ItemTransactionUniqueCode::PURCHASE_ORDER->value;
        } else if ($baseNameOfClass == 'Purchase') {
            // Purchase Order Entry
            $uniqueCode = ItemTransactionUniqueCode::PURCHASE->value;
        } else if ($baseNameOfClass == 'PurchaseReturn') {
            // Purchase Order Entry
            $uniqueCode = ItemTransactionUniqueCode::PURCHASE_RETURN->value;
        } else if ($baseNameOfClass == 'SaleOrder') {
            // sale Order Entry
            $uniqueCode = ItemTransactionUniqueCode::SALE_ORDER->value;
        } else if ($baseNameOfClass == 'Sale') {
            // sale Order Entry
            $uniqueCode = ItemTransactionUniqueCode::SALE->value;
        } else if ($baseNameOfClass == 'SaleReturn') {
            // sale Order Entry
            $uniqueCode = ItemTransactionUniqueCode::SALE_RETURN->value;
        } else if ($baseNameOfClass == 'Quotation') {
            // sale Order Entry
            $uniqueCode = ItemTransactionUniqueCode::QUOTATION->value;
        } else if ($baseNameOfClass == 'StockAdjustment') {
            // sale Order Entry
            $uniqueCode = ItemTransactionUniqueCode::STOCK_ADJUSTMENT->value;
        }


        return $uniqueCode;
    }
    /**
     * Record Item Transactions
     *
     * */
    public function recordItemTransactionEntry(Item|PurchaseOrder|Purchase|PurchaseReturn|SaleOrder|Sale|SaleReturn|StockTransfer|Quotation|StockAdjustment $model, array $data)
    {
        $itemId             = $data['item_id'];
        $transactionDate     = $this->toSystemDateFormat($data['transaction_date']);
        $warehouseId         = $data['warehouse_id'];
        $trackingType         = $data['tracking_type'];
        //$itemLocation 		= $data['item_location'];
        $quantity             = $data['quantity'];
        $atPrice             = $data['unit_price'];
        $tax_type           = $data['tax_type'];
        $unitId             = $data['unit_id'];
        $mrp                = $data['mrp'];

        /**
         * unique_code defined in stock transfer
         * */
        $uniqueCode =   (isset($data['unique_code'])) ? $data['unique_code'] : $this->transactionUniqueCode($model);


        $transaction = $model->itemTransaction()->create(
            [
                'transaction_date'      =>  $transactionDate,
                'warehouse_id'          =>  $warehouseId,
                'item_id'               =>  $itemId,
                'description'           =>  $data['description'] ?? null,
                'tracking_type'         =>  $trackingType,
                //'item_location'         =>  $itemLocation,

                'mrp'                   =>  $mrp,

                'quantity'              =>  $quantity,
                'unit_price'            =>  $atPrice,
                'unit_id'               =>  $unitId,
                'unique_code'           =>  $uniqueCode,

                'discount'              =>  $data['discount'] ?? 0,
                'discount_type'         =>  $data['discount_type'] ?? 'percentage',
                'discount_amount'       =>  $data['discount_amount'] ?? 0,

                'tax_id'                =>  $data['tax_id'] ?? null,
                'tax_type'              =>  $tax_type,
                'tax_amount'            =>  $data['tax_amount'] ?? 0,

                'charge_type'           =>  $data['charge_type'] ?? null,
                'charge_amount'         =>  $data['charge_amount'] ?? 0,

                'total'                 =>  $data['total'] ?? 0,
            ]
        );

        /**
         * Record Item All
         * */
        $updateQuantityWarehouseWise = $this->updateItemGeneralQuantityWarehouseWise($itemId);
        if (!$updateQuantityWarehouseWise) {
            throw new \Exception('Failed to record General Items Stock Warehouse Wise!');
        }

        return $transaction;
    }


    /**
     * For Batch Numbers
     * @return array
     * */
    public function getBatchWiseRecords($itemTransactionId = null): array
    {
        $batchArray = [];

        $batchRecords = ItemBatchTransaction::with(['itemBatchMaster', 'item'])->where("item_transaction_id", $itemTransactionId)->get();

        if ($batchRecords->count() > 0) {
            foreach ($batchRecords as $batch) {

                /**
                 * Note: These array id's also used in
                 * custom\js\items\batch-tracking.js
                 * */
                $tempArray = [
                    'batchNo'           => $batch->itemBatchMaster->batch_no ?? '',
                    'mfgDate'           => ($batch->itemBatchMaster->mfg_date) ? $batch->itemBatchMaster->formatted_mfg_date : '',
                    'expDate'           => ($batch->itemBatchMaster->exp_date) ? $batch->itemBatchMaster->formatted_exp_date : '',
                    'modelNo'           => $batch->itemBatchMaster->model_no ?? '',
                    'mrp'               => $this->formatWithPrecision($batch->itemBatchMaster->mrp, comma: false),
                    'color'             => $batch->itemBatchMaster->color ?? '',
                    'size'              => $batch->itemBatchMaster->size ?? '',
                    'openingQuantity'   => $this->formatQuantity($batch->quantity),
                ];

                array_push($batchArray, $tempArray);
            }
        }

        return $batchArray;
    }
    /**
     * For Serial Numbers
     * @return array
     * */
    public function getSerialWiseRecords($itemTransactionId = null): array
    {
        $serialArray = [];

        $serialRecords = ItemSerialTransaction::with('itemSerialMaster')->where("item_transaction_id", $itemTransactionId)->get();
        if ($serialRecords->count() > 0) {
            foreach ($serialRecords as $serial) {
                $serialArray[] = $serial->itemSerialMaster->serial_code;
            }
        }
        return $serialArray;
    }

    public function recordItemSerials($itemTransactionId, $serialArray, $itemId, $warehouseId, $uniqueCode)
    {
        $itemSerialMasterData = [
            'item_id'               =>  $itemId,
            'serial_code'           =>  $serialArray['serial_code'],
        ];
        /**
         * Validate Serial wise record exist in the ItemSerialMaster or not
         * */
        $itemSerialMaster = ItemSerialMaster::firstOrCreate($itemSerialMasterData);
        if (!$itemSerialMaster) {
            throw new \Exception('Failed to update Item Batch Master');
        }

        /**
         * Record Item Serial Transactions
         * */
        $itemSerialTransactionData = [
            'unique_code'           =>  $uniqueCode,
            'item_transaction_id'   =>  $itemTransactionId,
            'item_serial_master_id' =>  $itemSerialMaster->id,
            'warehouse_id'          =>  $warehouseId,
            'item_id'               =>  $itemId,
        ];

        $recordItemSerials = ItemSerialTransaction::create($itemSerialTransactionData);
        if (!$recordItemSerials) {
            throw new \Exception(__('item.failed_to_save_batch_records'));
        }

        /**
         * Update ItemSerialMaster status & warehouse
         * */
        $updateSerialMaster = $this->updateItemSerialCurrentStatusWarehouseWise($itemSerialMaster->id);
        if (!$updateSerialMaster) {
            throw new \Exception(__('item.failed_to_update_serial_master'));
        }

        return true;
    }

    /**
     * Update ItemSerialMaster & Warehouse
     * */
    public function updateItemSerialCurrentStatusWarehouseWise($itemSerialMasterId)
    {

        $CONST_ITEM_OPENING     = ItemTransactionUniqueCode::ITEM_OPENING->value;
        $CONST_PURCHASE_ORDER   = ItemTransactionUniqueCode::PURCHASE_ORDER->value;
        $CONST_PURCHASE         = ItemTransactionUniqueCode::PURCHASE->value;
        $CONST_PURCHASE_RETURN  = ItemTransactionUniqueCode::PURCHASE_RETURN->value;
        $CONST_SALE_ORDER       = ItemTransactionUniqueCode::SALE_ORDER->value;
        $CONST_SALE             = ItemTransactionUniqueCode::SALE->value;
        $CONST_SALE_RETURN      = ItemTransactionUniqueCode::SALE_RETURN->value;
        $CONST_STOCK_TRANSFER   = ItemTransactionUniqueCode::STOCK_TRANSFER->value;
        $CONST_STOCK_RECEIVE    = ItemTransactionUniqueCode::STOCK_RECEIVE->value;
        $CONST_STOCK_ADJUSTMENT_INCREASE = ItemTransactionUniqueCode::STOCK_ADJUSTMENT_INCREASE->value;
        $CONST_STOCK_ADJUSTMENT_DECREASE = ItemTransactionUniqueCode::STOCK_ADJUSTMENT_DECREASE->value;

        $itemSerialTransactions = ItemSerialTransaction::where('item_serial_master_id', $itemSerialMasterId)
            ->whereNotIn('unique_code', [$CONST_PURCHASE_ORDER, $CONST_SALE_ORDER])
            ->get();

        //Delete item_serial_master_id records from the ItemSerialQuantity model
        ItemSerialQuantity::where('item_serial_master_id', $itemSerialMasterId)->delete();

        if ($itemSerialTransactions->count() > 0) {

            //Collection Group by warehouse
            $itemSerialTransactions = $itemSerialTransactions->groupBy('warehouse_id')->toArray();

            $quantityCollection = collect();

            //MULTIPLE SERIAL TRANSACTIONS
            foreach ($itemSerialTransactions as $warehouseId => $itemSerialTransaction) {
                foreach ($itemSerialTransaction as $transaction) {
                    //Delete existing records item_serial_master

                    switch ($transaction['unique_code']) {
                        case $CONST_ITEM_OPENING:
                        case $CONST_PURCHASE:
                        case $CONST_SALE_RETURN:
                        case $CONST_STOCK_RECEIVE:
                        case $CONST_STOCK_ADJUSTMENT_INCREASE:
                            $operation = 'add';
                            break;

                        case $CONST_PURCHASE_RETURN:
                        case $CONST_STOCK_TRANSFER:
                        case $CONST_SALE:
                        case $CONST_STOCK_ADJUSTMENT_DECREASE:
                            $operation = 'remove';
                            break;
                    }

                    $arrayData = [
                        'item_id'               => $transaction['item_id'],
                        'warehouse_id'          => $warehouseId,
                        'item_serial_master_id' => $itemSerialMasterId,
                    ];

                    //Insert data in collection variable
                    if ($operation == 'add') {
                        $quantityCollection->push($arrayData);
                    } else {
                        // Remove data from collection if all conditions are exactly the same
                        $quantityCollection = $quantityCollection->reject(function ($item) use ($arrayData) {
                            return $item == $arrayData;
                        });
                    }
                }
            }

            //Insert data in table
            if ($quantityCollection->isNotEmpty()) {
                foreach ($quantityCollection as $itemData) {
                    ItemSerialQuantity::create($itemData);
                }
            }
        } //isNotEmpty


        //Find the item id
        $itemId = ItemSerialMaster::where('id', $itemSerialMasterId)->first()->item_id;

        /**
         * Record Item All
         * */
        $updateQuantityWarehouseWise = $this->updateItemGeneralQuantityWarehouseWise($itemId);
        if (!$updateQuantityWarehouseWise) {
            throw new \Exception('Failed to record General Items Stock Warehouse Wise!');
        }

        return true;
    }

    /**
     * Item Tracking Type = regular
     * Validate negative stock entry allowed or not
     * while making sale
     * */
    public function validateRegularItemQuantity(Item $itemDetails, $warehouseId, $saleQuantity, $uniqueCode)
    {

        if (in_array($uniqueCode, [ItemTransactionUniqueCode::SALE->value])) {

            //If negative stck is not allowed
            if (!$this->canAllowNegativeStockBilling) {

                if ($itemDetails->tracking_type === 'regular' && !$itemDetails->is_service) {

                    $itemGeneralQuantity = ItemGeneralQuantity::where('item_id', $itemDetails->id)
                        ->where('warehouse_id', $warehouseId)
                        ->first();

                    if ($itemGeneralQuantity) {
                        if ($itemGeneralQuantity->quantity < 0) {
                            throw new \Exception('Stock not available for the item: ' . $itemDetails->name . '<br>Warehouse: ' . $itemGeneralQuantity->warehouse->name . '<br>Quantity: ' . $this->formatQuantity($itemGeneralQuantity->quantity));
                        }
                        if ($itemGeneralQuantity->quantity < $saleQuantity) {
                            throw new \Exception('Stock not available for the item: ' . $itemDetails->name . '<br>Warehouse: ' . $itemGeneralQuantity->warehouse->name . '<br>Quantity: ' . $this->formatQuantity($itemGeneralQuantity->quantity));
                        }
                    }
                }
            }
        }
        return true;
    }



    /**
     * Insert or Record Item Batches
     * */
    public function recordItemBatches($itemTransactionId, $batchArray, $itemId, $warehouseId, $uniqueCode)
    {
        $itemDetails = Item::find($itemId);

        //Batch Number should not be empty
        //$batchArray['batch_no'] should not be empty or null
        if (app('company')['is_batch_compulsory'] && empty($batchArray['batch_no'])) {
            throw new \Exception("Batch Number is required!<br>Item: '" . $itemDetails->name . "'");
        }

        /**
         *
         * Only for Sale and Sale Order
         */
        if (in_array($uniqueCode, [ItemTransactionUniqueCode::SALE->value, ItemTransactionUniqueCode::SALE_ORDER->value])) {

            //Validate The Given batch number exist in the ItemBatchMaster ?
            $itemBatchMaster = ItemBatchMaster::where('batch_no', $batchArray['batch_no'])->where('item_id', $itemId)->first();
            if (!$itemBatchMaster && app('company')['is_batch_compulsory']) {
                throw new \Exception('Batch Number: ' . $batchArray['batch_no'] . '<br>Not found in the system!<br>Item: ' . $itemDetails->name);
            }

            //If negative stck is not allowed
            if (!$this->canAllowNegativeStockBilling) {

                //Check Stock Availability
                $itemBatchQuantity = ItemBatchQuantity::where('item_batch_master_id', $itemBatchMaster->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if ($itemBatchQuantity) {
                    if ($itemBatchQuantity->quantity < 0 || $itemBatchQuantity->quantity < $batchArray['quantity']) {
                        throw new \Exception('Stock not available for the batch number: ' . $batchArray['batch_no'] . '<br>Item: ' . $itemDetails->name . '<br>Warehouse: ' . $itemBatchQuantity->warehouse->name . '<br>Quantity: ' . $this->formatQuantity($itemBatchQuantity->quantity));
                    }
                }
            }
        }

        $itemBatchMasterData = [
            'item_id'               =>  $itemId,
            'batch_no'              =>  $batchArray['batch_no'] ?? null,
            'mfg_date'              =>  $batchArray['mfg_date'] ? $this->toSystemDateFormat($batchArray['mfg_date']) : null,
            'exp_date'              =>  $batchArray['exp_date'] ? $this->toSystemDateFormat($batchArray['exp_date']) : null,
            'model_no'              =>  $batchArray['model_no'] ?: null,
            'mrp'                   =>  $batchArray['mrp'] ?? 0,
            'color'                 =>  $batchArray['color'] ?: null,
            'size'                  =>  $batchArray['size'] ?: null,
        ];


        /**
         * Validate Batch wise record exist in the ItemBatchMaster or not
         * */
        $itemBatchMaster = ItemBatchMaster::firstOrCreate($itemBatchMasterData);
        if (!$itemBatchMaster) {
            throw new \Exception('Failed to update Item Batch Master');
        }


        /**
         * Record Item Batch Transactions
         * */
        $itemBatchTransactionData = [
            'unique_code'           =>  $uniqueCode,
            'item_transaction_id'   =>  $itemTransactionId,
            'item_batch_master_id'  =>  $itemBatchMaster->id,
            'warehouse_id'          =>  $warehouseId,
            'item_id'               =>  $itemId,
            'quantity'              =>  $batchArray['quantity'],
        ];

        $recordItemBatches = ItemBatchTransaction::create($itemBatchTransactionData);
        if (!$recordItemBatches) {
            throw new \Exception(__('item.failed_to_save_batch_records'));
        }

        /**
         * Update Item Batch Quantity Warehouse wise
         * */
        $updateBatchQuantity = $this->updateItemBatchQuantityWarehouseWise($itemBatchMaster->id);
        if (!$updateBatchQuantity) {
            throw new \Exception('Failed to update Item Batch Quantity Warehouse wise');
        }

        return true;
    }

    /**
     * Indivisual method
     * Update the stock of the batch item
     * */
    public function updateItemBatchQuantityWarehouseWise($itemBatchMasterId)
    {
        //Delete Reords from ItemBatchQuantity
        ItemBatchQuantity::where('item_batch_master_id', $itemBatchMasterId)->delete();

        $itemBatchTransactions = ItemBatchTransaction::selectRaw('
                     (
                        COALESCE(SUM(CASE WHEN unique_code = "' . ItemTransactionUniqueCode::PURCHASE->value . '" THEN quantity ELSE 0 END), 0)
                        -
                        COALESCE(SUM(CASE WHEN unique_code = "' . ItemTransactionUniqueCode::PURCHASE_RETURN->value . '" THEN quantity ELSE 0 END), 0)
                        -
                        COALESCE(SUM(CASE WHEN unique_code = "' . ItemTransactionUniqueCode::SALE->value . '" THEN quantity ELSE 0 END), 0)
                        +
                        COALESCE(SUM(CASE WHEN unique_code = "' . ItemTransactionUniqueCode::SALE_RETURN->value . '" THEN quantity ELSE 0 END), 0)
                        +
                        COALESCE(SUM(CASE WHEN unique_code = "' . ItemTransactionUniqueCode::ITEM_OPENING->value . '" THEN quantity ELSE 0 END), 0)
                        -
                        COALESCE(SUM(CASE WHEN unique_code = "' . ItemTransactionUniqueCode::STOCK_TRANSFER->value . '" THEN quantity ELSE 0 END), 0)
                        +
                        COALESCE(SUM(CASE WHEN unique_code = "' . ItemTransactionUniqueCode::STOCK_RECEIVE->value . '" THEN quantity ELSE 0 END), 0)
                        +
                        COALESCE(SUM(CASE WHEN unique_code = "' . ItemTransactionUniqueCode::STOCK_ADJUSTMENT_INCREASE->value . '" THEN quantity ELSE 0 END), 0)
                        -
                        COALESCE(SUM(CASE WHEN unique_code = "' . ItemTransactionUniqueCode::STOCK_ADJUSTMENT_DECREASE->value . '" THEN quantity ELSE 0 END), 0)
                    ) as item_batch_warehouse_stock,
                    item_id,
                    warehouse_id,
                    item_batch_master_id
                ')
            ->where('item_batch_master_id', $itemBatchMasterId)
            ->whereNotIn('unique_code', [ItemTransactionUniqueCode::PURCHASE_ORDER->value, ItemTransactionUniqueCode::SALE_ORDER->value])
            ->groupBy('item_id', 'warehouse_id', 'item_batch_master_id')
            ->get();

        if ($itemBatchTransactions->isNotEmpty()) {

            //Collection Group by warehouse
            $itemBatchTransactions = $itemBatchTransactions->groupBy('warehouse_id')->toArray();

            //MULTIPLE SERIAL TRANSACTIONS
            foreach ($itemBatchTransactions as $warehouseId => $batchTransactions) {
                foreach ($batchTransactions as $itemBatchTransaction) {
                    //Record ItemBatchQuantity
                    $readyData = [
                        'item_id'               => $itemBatchTransaction['item_id'],
                        'warehouse_id'          => $warehouseId,
                        'item_batch_master_id'  => $itemBatchTransaction['item_batch_master_id'],
                        'quantity'              => $itemBatchTransaction['item_batch_warehouse_stock'],
                    ];

                    $created = ItemBatchQuantity::create($readyData);
                    if (!$created) {
                        throw new \Exception(__('item.failed_to_save_batch_records'));
                    }
                } //foreach itemBatchTransaction
            }
        } //count>0 itemBatchTransaction


        //Find the item id
        $itemId = ItemBatchMaster::where('id', $itemBatchMasterId)->first()->item_id;

        /**
         * Record Item All
         * */
        $updateQuantityWarehouseWise = $this->updateItemGeneralQuantityWarehouseWise($itemId);
        if (!$updateQuantityWarehouseWise) {
            throw new \Exception('Failed to record General Items Stock Warehouse Wise!');
        }


        return true;
    }

    /**
     * Update General Items warehouse-wise
     *
     */
    public function updateItemGeneralQuantityWarehouseWise($itemGeneralMasterId)
    {
        $itemTransactions = ItemTransaction::selectRaw('
        COALESCE(SUM(
            CASE
                WHEN unique_code IN (
                    \'' . ItemTransactionUniqueCode::PURCHASE->value . '\',
                    \'' . ItemTransactionUniqueCode::SALE_RETURN->value . '\',
                    \'' . ItemTransactionUniqueCode::ITEM_OPENING->value . '\',
                    \'' . ItemTransactionUniqueCode::STOCK_RECEIVE->value . '\',
                    \'' . ItemTransactionUniqueCode::STOCK_ADJUSTMENT_INCREASE->value . '\'
                ) THEN
                    CASE
                        WHEN items.base_unit_id = item_transactions.unit_id THEN quantity
                        WHEN items.secondary_unit_id = item_transactions.unit_id THEN quantity / items.conversion_rate
                        ELSE 0
                    END
                WHEN unique_code IN (
                    \'' . ItemTransactionUniqueCode::PURCHASE_RETURN->value . '\',
                    \'' . ItemTransactionUniqueCode::SALE->value . '\',
                    \'' . ItemTransactionUniqueCode::STOCK_TRANSFER->value . '\',
                    \'' . ItemTransactionUniqueCode::STOCK_ADJUSTMENT_DECREASE->value . '\'
                ) THEN
                    CASE
                        WHEN items.base_unit_id = item_transactions.unit_id THEN -quantity
                        WHEN items.secondary_unit_id = item_transactions.unit_id THEN -quantity / items.conversion_rate
                        ELSE 0
                    END
                ELSE 0
            END
        ), 0) AS item_general_warehouse_stock,
        item_id,
        warehouse_id
    ')
            ->join('items', 'item_transactions.item_id', '=', 'items.id')
            ->whereNotIn('unique_code', [
                ItemTransactionUniqueCode::PURCHASE_ORDER->value,
                ItemTransactionUniqueCode::SALE_ORDER->value
            ])
            ->where('item_id', $itemGeneralMasterId)
            ->groupBy('item_id', 'warehouse_id')
            ->get();

        // Delete old records
        ItemGeneralQuantity::where('item_id', $itemGeneralMasterId)->delete();

        if ($itemTransactions->count() > 0) {
            // Group by warehouse
            $itemGeneralTransactions = $itemTransactions->groupBy('warehouse_id')->toArray();

            foreach ($itemGeneralTransactions as $warehouseId => $generalransactions) {
                foreach ($generalransactions as $generalransaction) {
                    // Prepare record
                    $readyData = [
                        'item_id' => $generalransaction['item_id'],
                        'warehouse_id' => $warehouseId,
                        'quantity' => $generalransaction['item_general_warehouse_stock'],
                    ];

                    // Insert into table
                    $created = ItemGeneralQuantity::create($readyData);
                    if (!$created) {
                        throw new \Exception('Failed to record General Items Warehouse Wise!');
                    }

                    // Update item master stock
                    $updateStock = $this->itemService->updateItemStock($itemGeneralMasterId);
                    if (!$updateStock) {
                        throw new \Exception('Failed to update Item Master Stock!!');
                    }
                }
            }
        }

        return true;
    }


    public function getHistoryOfItems(Item|Purchase|PurchaseReturn|SaleOrder|Sale|SaleReturn|StockTransfer|Quotation|StockAdjustment $model)
    {
        /**
         * Get ItemSerialTransaction
         * Models: ItemSerialTransaction, itemSerialMaster, ItemSerialQuantity
         * */
        $itemTransactions = $model->refresh('itemTransaction')->itemTransaction;

        $itemSerialMasterIdsHistoryArray = [];
        $itemBatchMasterIdsHistoryArray = [];
        $itemGeneralMasterIdsHistoryArray = [];
        if ($itemTransactions->isNotEmpty()) {
            foreach ($itemTransactions as $itemTransaction) {
                /**
                 * ITEM SERIAL NUMBER TRANSACTION
                 * */
                if ($itemTransaction->tracking_type == 'serial') {
                    if ($itemTransaction->itemSerialTransaction->count() > 0) {
                        foreach ($itemTransaction->itemSerialTransaction as $itemSerialTransaction) {
                            $itemSerialMasterIdsHistoryArray[] = $itemSerialTransaction->item_serial_master_id;
                        }
                    }
                }

                /**
                 * BATCH NUMBER TRANSACTION
                 * */
                else if ($itemTransaction->tracking_type == 'batch') {
                    if ($itemTransaction->itemBatchTransactions->count() > 0) {
                        foreach ($itemTransaction->itemBatchTransactions as $itemBatchTransaction) {
                            $itemBatchMasterIdsHistoryArray[] = $itemBatchTransaction->item_batch_master_id;
                        }
                    }
                }

                /**
                 * GENERAL ITEMS TRANSACTION
                 * */
                else {
                    //'general' tracking type
                    $itemGeneralMasterIdsHistoryArray[] = $itemTransaction->item_id;
                }
            }
        } //isNotEmpty

        return [
            'itemSerialMasterIdsHistoryArray'   => $itemSerialMasterIdsHistoryArray,
            'itemBatchMasterIdsHistoryArray'    => $itemBatchMasterIdsHistoryArray,
            'itemGeneralMasterIdsHistoryArray'  => $itemGeneralMasterIdsHistoryArray,
        ];
    }

    /**
     * UPDATE HISTORY DATA FOR ITEM BATCH AND SERIAL NUMBER AND GENERAL
     * */
    public function updatePreviousHistoryOfItems(Item|Purchase|PurchaseReturn|SaleOrder|Sale|SaleReturn|StockTransfer|Quotation|StockAdjustment $model, array $getPreviousHistoryOfItems)
    {
        /**
         * IMPORTANT NOTE:
         *
         * Explicitly unset the relationship data
         * Because i re calling same method in same operation
         * else it will load previous itemTransaction
         *
         * unsetRelation()
         * */
        $model->unsetRelation('itemTransaction');

        //Load Fresh ItemTransaction Now
        $getNewHistoryOfItems = $this->getHistoryOfItems($model);

        /**
         * Item Serial Master ID's array
         * */
        if (isset($getPreviousHistoryOfItems['itemSerialMasterIdsHistoryArray'])) {
            if (count($getPreviousHistoryOfItems['itemSerialMasterIdsHistoryArray']) > 0) {
                /**
                 * Filter the old and new array data to avoid multiple execution
                 * */
                $remainingIds = array_diff($getPreviousHistoryOfItems['itemSerialMasterIdsHistoryArray'], $getNewHistoryOfItems['itemSerialMasterIdsHistoryArray']);

                if (count($remainingIds) > 0) {
                    foreach ($remainingIds as $itemSerialMasterId) {
                        if (!$this->updateItemSerialCurrentStatusWarehouseWise($itemSerialMasterId)) {
                            throw new \Exception('Failed to update previouse serial number history data in item serial quantity table!');
                        }
                    }
                }
            }
        }

        /**
         * Item Serial Master ID's array
         * */
        if (isset($getPreviousHistoryOfItems['itemBatchMasterIdsHistoryArray'])) {
            if (count($getPreviousHistoryOfItems['itemBatchMasterIdsHistoryArray']) > 0) {

                /**
                 * Filter the old and new array data to avoid multiple execution
                 * */
                $remainingIds = array_diff($getPreviousHistoryOfItems['itemBatchMasterIdsHistoryArray'], $getNewHistoryOfItems['itemBatchMasterIdsHistoryArray']);

                if (count($remainingIds) > 0) {
                    foreach ($remainingIds as $itemBatchMasterId) {
                        if (!$this->updateItemBatchQuantityWarehouseWise($itemBatchMasterId)) {
                            throw new \Exception('Failed to update previouse batch number history data in item batch quantity table!');
                        }
                    }
                }
            }
        }

        /**
         * Item General Master ID's array
         * */
        if (isset($getPreviousHistoryOfItems['itemGeneralMasterIdsHistoryArray'])) {
            if (count($getPreviousHistoryOfItems['itemGeneralMasterIdsHistoryArray']) > 0) {

                /**
                 * Filter the old and new array data to avoid multiple execution
                 * */
                $remainingIds = array_diff($getPreviousHistoryOfItems['itemGeneralMasterIdsHistoryArray'], $getNewHistoryOfItems['itemGeneralMasterIdsHistoryArray']);
                if (count($remainingIds) > 0) {
                    foreach ($remainingIds as $itemGeneralMasterId) {
                        if (!$this->updateItemGeneralQuantityWarehouseWise($itemGeneralMasterId)) {
                            throw new \Exception('Failed to update previouse General Item history data in item General quantity table!');
                        }
                    }
                }
            }
        }

        return true;
    }

    public function daysDifferenceByDate($givenDate = '')
    {
        if (empty($givenDate)) {
            return '';
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($givenDate)->startOfDay();

        return $today->diffInDays($endDate, false);
    }

    public function worthItemsDetails($warehouseId, $itemId = null)
    {
        // Fetch available items with their total quantities
        $availableItems = ItemGeneralQuantity::where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->when($itemId, function ($query) use ($itemId) {
                $query->where('item_id', $itemId);
            })
            ->groupBy('item_id')
            ->selectRaw('item_id, SUM(quantity) as total_quantity')
            ->pluck('total_quantity', 'item_id');

        if ($availableItems->isEmpty()) {
            return [
                'totalPurchaseCost' => 0,
                'totalSalePrice' => 0,
                'totalAvailableQuantity' => 0,
            ];
        }

        // Aggregate purchase data and join with items to get sale_price
        $purchasesData = ItemTransaction::where('warehouse_id', $warehouseId)
            ->where(function ($query) {
                $query->where('transaction_type', getMorphedModelName(Purchase::class))
                    ->orWhere('transaction_type', getMorphedModelName('Item Opening'))
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('transaction_type', getMorphedModelName('Stock Transfer'))
                            ->where('unique_code', 'STOCK_RECEIVE');
                    });
            })
            ->whereIn('item_id', $itemId ? [$itemId] : $availableItems->keys()->all())
            ->join('items', 'items.id', '=', 'item_transactions.item_id')
            ->select([
                'item_transactions.item_id',
                'items.sale_price',
                DB::raw('SUM(item_transactions.total) as total_sum'),
                DB::raw('SUM(item_transactions.charge_amount) as charge_amount_sum'),
                DB::raw('SUM(item_transactions.charge_tax_amount) as charge_tax_amount_sum'),
                DB::raw('SUM(item_transactions.quantity) as total_quantity_sum'),
            ])
            ->groupBy('item_transactions.item_id', 'items.sale_price')
            ->get()
            ->keyBy('item_id');

        // Calculate totals
        $totalPurchaseCost = 0;
        $totalSalePrice = 0;
        $totalAvailableQuantity = 0;

        foreach ($availableItems as $itemId => $availableQuantity) {
            if (!$purchaseRecord = $purchasesData->get($itemId)) {
                continue;
            }

            $totalSum = $purchaseRecord->total_sum + $purchaseRecord->charge_amount_sum + $purchaseRecord->charge_tax_amount_sum;
            $totalSumQty = $purchaseRecord->total_quantity_sum;

            $averagePurchasePrice = $totalSum > 0 ? $totalSum / $totalSumQty : 0;
            $totalPurchaseCost += $averagePurchasePrice * $availableQuantity;
            $totalSalePrice += $purchaseRecord->sale_price * $availableQuantity;
            $totalAvailableQuantity += $availableQuantity;
        }

        return [
            'totalPurchaseCost' => $totalPurchaseCost,
            'totalSalePrice' => $totalSalePrice,
            'totalAvailableQuantity' => $totalAvailableQuantity,
        ];
    }


    public function updatePurchasedItemsPurchasePrice($purchaseId)
    {
        // Load item id from purchase item transaction in array
        $currentPurchase = Purchase::with('itemTransaction.item')->find($purchaseId);
        if (!$currentPurchase) {
            throw new \Exception('Purchase not found');
        }

        // Item id should be unique
        $itemIds = $currentPurchase->itemTransaction->pluck('item_id')->unique()->toArray();

        $this->updateItemMasterAveragePurchasePrice($itemIds);
    }

    public function updateItemMasterAveragePurchasePrice(array $itemIds)
    {

        $company = app('company');

        // Check if auto-update features are enabled
        if (!$company['auto_update_purchase_price'] || !$company['auto_update_average_purchase_price']) {
            return;
        }

        // Get all relevant transactions that affect purchase price
        $itemTransactions = ItemTransaction::with('item')
            ->where(function ($query) {
                $query->where('transaction_type', getMorphedModelName(Purchase::class))
                    ->orWhere('transaction_type', getMorphedModelName('Item Opening'));
                // ->orWhere(function($q) {
                //     $q->where('transaction_type', getMorphedModelName('Stock Transfer'))
                //         ->where('unique_code', 'STOCK_RECEIVE');
                // });
            })
            ->whereIn('item_id', $itemIds)
            ->get();


        if ($itemTransactions->isEmpty()) {
            return;
        }

        // Group transactions by item
        $groupedItemTransactions = $itemTransactions->groupBy('item_id');


        foreach ($groupedItemTransactions as $itemId => $transactions) {
            $itemModel = $transactions->first()->item;
            $totalWeightedPrice = 0;
            $totalQuantity = 0;

            // Calculate weighted average price
            foreach ($transactions as $transaction) {
                // Convert price to base unit price if needed
                $purchasePrice = $transaction->total + $transaction->charge_amount + $transaction->charge_tax_amount;
                $quantity = $transaction->quantity;

                // Handle unit conversion
                if ($itemModel->base_unit_id != $transaction->unit_id && $itemModel->secondary_unit_id == $transaction->unit_id && $itemModel->conversion_rate != 1) {
                    $purchasePrice *= $itemModel->conversion_rate;
                    $quantity = $quantity / $itemModel->conversion_rate;
                }
                $totalWeightedPrice += $purchasePrice;
                $totalQuantity += $quantity;
            }


            // Calculate and update average purchase price
            if ($totalQuantity > 0) {
                $avgPurchasePrice = $totalWeightedPrice / $totalQuantity;
                $itemModel->purchase_price = $avgPurchasePrice > 0 ? $avgPurchasePrice : $itemModel->purchase_price;
                $itemModel->save();
            }
        }

        return true;
    }

    /**
     * Calculate average purchase and sale price for each item.
     * @param array $itemIds
     * @return array
     */
    public function calculateEachItemSaleAndPurchasePrice(array $itemIds, $warehouseId = null, bool $useGlobalPurchasePrice = true, array $saleTransactionDateRange = [])
    {
        $result = [];

        $itemIds = array_unique($itemIds);

        $purchaseWarehouseIds = (!$useGlobalPurchasePrice && $warehouseId) ? [$warehouseId] : null;
        $saleWarehouseIds = $warehouseId ? [$warehouseId] : null;

        $items = Item::whereIn('id', $itemIds)->get()->keyBy('id');

        // ✅ Purchase Transactions
        $purchaseTransactions = ItemTransaction::whereIn('item_id', $itemIds)
            ->whereIn('unique_code', [
                ItemTransactionUniqueCode::PURCHASE->value,
                ItemTransactionUniqueCode::ITEM_OPENING->value,
                ItemTransactionUniqueCode::STOCK_RECEIVE->value,
            ])
            ->when($purchaseWarehouseIds, function ($query) use ($purchaseWarehouseIds) {
                return $query->whereIn('warehouse_id', $purchaseWarehouseIds);
            })
            ->get()
            ->groupBy('item_id');

        // ✅ Sale Transactions
        $saleTransactions = ItemTransaction::whereIn('item_id', $itemIds)
            ->where('unique_code', ItemTransactionUniqueCode::SALE->value)
            ->when($saleWarehouseIds, function ($query) use ($saleWarehouseIds) {
                return $query->whereIn('warehouse_id', $saleWarehouseIds);
            })
            ->when(!empty($saleTransactionDateRange), function ($query) use ($saleTransactionDateRange) {
                return $query->whereBetween('transaction_date', [$saleTransactionDateRange['from_date'], $saleTransactionDateRange['to_date']]);
            })
            ->get()
            ->groupBy('item_id');

        foreach ($itemIds as $itemId) {
            $item = $items->get($itemId);
            if (!$item) {
                $result[$itemId] = [
                    'avg_purchase_price' => 0,
                    'avg_sale_price'     => 0,
                ];
                continue;
            }

            // 👉 Purchase Calculation
            $purchaseTotal = 0;
            $purchaseQty = 0;
            foreach ($purchaseTransactions->get($itemId, collect()) as $transaction) {
                $qty = $transaction->quantity;
                $total = $transaction->total + $transaction->charge_amount + $transaction->charge_tax_amount;

                if (
                    $item->base_unit_id != $transaction->unit_id &&
                    $item->secondary_unit_id == $transaction->unit_id &&
                    $item->conversion_rate != 1
                ) {
                    $total *= $item->conversion_rate;
                    $qty = $qty / $item->conversion_rate;
                }

                $purchaseTotal += $total;
                $purchaseQty += $qty;
            }


            // 👉 Sale Calculation
            $saleTotal = 0;
            $saleQty = 0;
            foreach ($saleTransactions->get($itemId, collect()) as $transaction) {
                $qty = $transaction->quantity;
                /**
                 * actual total
                 * total = Price - discount + tax =
                 * In total disocunt is already diducted and tax is added
                 */
                //$netSale = $transaction->total - $transaction->tax_amount;

                if (
                    $item->base_unit_id != $transaction->unit_id &&
                    $item->secondary_unit_id == $transaction->unit_id &&
                    $item->conversion_rate != 1
                ) {
                    $qty = $qty / $item->conversion_rate;
                }

                $saleTotal += $transaction->total; //$netSale;
                $saleQty += $qty;
            }


            //Each Item Data, where unique item id is the key
            $result[$itemId] = [
                'purchase' => [
                    // 'quantity' => $purchaseQty,

                    /*final item total including tax and discount*/
                    'total' => $purchaseTotal,
                    'average_purchase_price' => $purchaseQty > 0 ? $purchaseTotal / $purchaseQty : 0,
                    /*END:*/

                ],

                'sale'  => [
                    // 'quantity' => $saleQty,

                    /*START: final item total including tax and discount*/
                    'total' => $saleTotal,
                    'average_sale_price' => $saleQty > 0 ? $saleTotal / $saleQty : 0,
                    /*END:*/

                ],
            ];
        }

        return $result;
    }
}
