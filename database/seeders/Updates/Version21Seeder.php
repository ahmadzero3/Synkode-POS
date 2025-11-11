<?php

namespace Database\Seeders\Updates;

use App\Enums\ItemTransactionUniqueCode;
use App\Models\Items\Item;
use App\Models\Currency;
use App\Models\Items\ItemGeneralQuantity;
use App\Models\Items\ItemTransaction;
use App\Models\Party\Party;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionGroup;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Sale\Quotation;
use App\Models\Sale\Sale;
use App\Models\Sale\SaleOrder;
use App\Models\Sale\SaleReturn;
use Spatie\Permission\Models\Permission;
use App\Services\ItemTransactionService;
use App\Services\ItemService;

class Version21Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        echo "Version21Seeder Running...";
        $this->updateRecords();
        // $this->addRecords();

        echo "\Version21Seeder Completed!!\n";
    }

    public function updateRecords()
    {

        //select all items
        $items = Item::all();
        if ($items->isNotEmpty()) {
            foreach ($items as $item) {

                $this->updateItemGeneralQuantityWarehouseWise($item->id);
            }
        }
    }

    public function addNewPermissions()
    {
        //
    }

    public function updateItemGeneralQuantityWarehouseWise($itemGeneralMasterId)
    {

        $CONST_PURCHASE_ORDER   = ItemTransactionUniqueCode::PURCHASE_ORDER->value;
        $CONST_PURCHASE         = ItemTransactionUniqueCode::PURCHASE->value;
        $CONST_PURCHASE_RETURN  = ItemTransactionUniqueCode::PURCHASE_RETURN->value;
        $CONST_ITEM_OPENING     = ItemTransactionUniqueCode::ITEM_OPENING->value;


        $itemService = new ItemService();

        $itemTransactions = ItemTransaction::selectRaw('
                                (
                                    COALESCE(SUM(
                                        CASE
                                            WHEN unique_code = "' . ItemTransactionUniqueCode::PURCHASE->value . '"
                                                THEN
                                                    CASE
                                                        WHEN items.base_unit_id = item_transactions.unit_id
                                                            THEN quantity
                                                        WHEN items.secondary_unit_id = item_transactions.unit_id
                                                            THEN quantity / items.conversion_rate
                                                        ELSE 0
                                                    END
                                            WHEN unique_code = "' . ItemTransactionUniqueCode::PURCHASE_RETURN->value . '"
                                                THEN
                                                    CASE
                                                        WHEN items.base_unit_id = item_transactions.unit_id
                                                            THEN -quantity
                                                        WHEN items.secondary_unit_id = item_transactions.unit_id
                                                            THEN -quantity / items.conversion_rate
                                                        ELSE 0
                                                    END
                                            WHEN unique_code = "' . ItemTransactionUniqueCode::SALE->value . '"
                                                THEN
                                                    CASE
                                                        WHEN items.base_unit_id = item_transactions.unit_id
                                                            THEN -quantity
                                                        WHEN items.secondary_unit_id = item_transactions.unit_id
                                                            THEN -quantity / items.conversion_rate
                                                        ELSE 0
                                                    END
                                            WHEN unique_code = "' . ItemTransactionUniqueCode::SALE_RETURN->value . '"
                                                THEN
                                                    CASE
                                                        WHEN items.base_unit_id = item_transactions.unit_id
                                                            THEN quantity
                                                        WHEN items.secondary_unit_id = item_transactions.unit_id
                                                            THEN quantity / items.conversion_rate
                                                        ELSE 0
                                                    END
                                            WHEN unique_code = "' . ItemTransactionUniqueCode::ITEM_OPENING->value . '"
                                                THEN
                                                    CASE
                                                        WHEN items.base_unit_id = item_transactions.unit_id
                                                            THEN quantity
                                                        WHEN items.secondary_unit_id = item_transactions.unit_id
                                                            THEN quantity / items.conversion_rate
                                                        ELSE 0
                                                    END
                                            WHEN unique_code = "' . ItemTransactionUniqueCode::STOCK_TRANSFER->value . '"
                                                THEN
                                                    CASE
                                                        WHEN items.base_unit_id = item_transactions.unit_id
                                                            THEN -quantity
                                                        WHEN items.secondary_unit_id = item_transactions.unit_id
                                                            THEN -quantity / items.conversion_rate
                                                        ELSE 0
                                                    END
                                            WHEN unique_code = "' . ItemTransactionUniqueCode::STOCK_RECEIVE->value . '"
                                                THEN
                                                    CASE
                                                        WHEN items.base_unit_id = item_transactions.unit_id
                                                            THEN quantity
                                                        WHEN items.secondary_unit_id = item_transactions.unit_id
                                                            THEN quantity / items.conversion_rate
                                                        ELSE 0
                                                    END
                                        END
                                    ), 0)

                                ) as item_general_warehouse_stock,
                                item_id,
                                warehouse_id
                            ')
            ->join('items', 'item_transactions.item_id', '=', 'items.id')
            ->whereNotIn('unique_code', [ItemTransactionUniqueCode::PURCHASE_ORDER->value, ItemTransactionUniqueCode::SALE_ORDER->value])
            ->where('item_id', $itemGeneralMasterId)
            ->groupBy('item_id', 'warehouse_id')
            ->get();

        if ($itemTransactions->count() > 0) {
            //Delete ItemGeneralQuantity
            ItemGeneralQuantity::where('item_id', $itemGeneralMasterId)->delete();

            //Group By warehouse
            $itemGeneralTransactions = $itemTransactions->groupBy('warehouse_id')->toArray();

            $quantityCollection = collect();

            //MULTIPLE ITEM TRANSACTIONS
            foreach ($itemGeneralTransactions as $warehouseId => $generalransactions) {
                foreach ($generalransactions as $generalransaction) {
                    //Record ItemGeneralQuantity
                    $readyData = [
                        'item_id'               => $generalransaction['item_id'],
                        'warehouse_id'          => $warehouseId,
                        'quantity'              => $generalransaction['item_general_warehouse_stock'],
                    ];

                    $created = ItemGeneralQuantity::create($readyData);
                    if (!$created) {
                        throw new \Exception('Failed to record General Items Warehouse Wise!');
                    }

                    /**
                     * Update Item Master Stock
                     * */
                    $updateStock = $itemService->updateItemStock($itemGeneralMasterId);
                    if (!$updateStock) {
                        throw new \Exception('Failed to update Item Master Stock!!');
                    }
                } //foreach generalransactions
            }
        }
        return true;
    }
}
