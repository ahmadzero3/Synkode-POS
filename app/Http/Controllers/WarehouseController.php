<?php

namespace App\Http\Controllers;

use App\Enums\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Requests\WarehouseRequest;
use App\Models\Items\ItemGeneralQuantity;
use App\Models\Items\ItemTransaction;
use App\Models\Purchase\Purchase;
use App\Models\User;
use App\Models\UserWarehouse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\QueryException;
use App\Models\Warehouse;
use App\Traits\FormatNumber;
use Illuminate\Support\Facades\DB;
use App\Services\ItemTransactionService;


class WarehouseController extends Controller
{
    use FormatNumber;

    public $itemTransactionService;

    public function __construct(ItemTransactionService $itemTransactionService)
    {
        $this->itemTransactionService = $itemTransactionService;
    }

    /**
     * Create a new warehouse.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {

        return view('warehouse.create');
    }

    /**
     * Edit a warehouse.
     *
     * @param int $id The ID of the warehouse to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id): View
    {

        $warehouse = Warehouse::find($id);

        return view('warehouse.edit', compact('warehouse'));
    }
    /**
     * Return JsonResponse
     * */
    public function store(WarehouseRequest $request): JsonResponse
    {

        // Get the validated data from the WarehouseRequest
        $validatedData = $request->validated();
        $warehouse = Warehouse::create($validatedData);

        return response()->json([
            'status'  => true,
            'message' => __('app.record_saved_successfully'),
            'data'  => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
            ]
        ]);
    }

    /**
     * Return JsonResponse
     * */
    public function update(WarehouseRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Save the tax details
        Warehouse::where('id', $validatedData['id'])->update($validatedData);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    public function list(): View
    {
        session(['record' => [
            'type' => 'info',
            'status' => "Information",
            'message' => "The warehouse serves the primary purpose of maintaining stock levels. If an item is not available in any other warehouse, its stock will be displayed as zero when generating invoices, bills, or conducting any other transactions. This ensures accurate inventory management and prevents errors during the billing process.",
        ]]);
        return view('warehouse.list');
    }

    public function datatableList(Request $request)
    {

        //If warehouseId is not provided, fetch warehouses accessible to the user
        $warehouseIds = User::find(auth()->id())->getAccessibleWarehouses()->pluck('id');

        //$data = Warehouse::query();
        $data = Warehouse::whereIn('id', $warehouseIds); // Apply filtering

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format(app('company')['date_format']);
            })
            ->addColumn('username', function ($row) {
                return $row->user->username ?? '';
            })
            ->addColumn('total_items', function ($row) {
                $totalItems = ItemGeneralQuantity::where('warehouse_id', $row->id)->where('quantity', '>', 0)->distinct('item_id')->count('item_id');
                return $totalItems;
            })
            ->addColumn('available_stock', function ($row) {
                $quantity = ItemGeneralQuantity::where('warehouse_id', $row->id)->sum('quantity');
                return $this->formatQuantity($quantity);
            })
            ->addColumn('worth_cost', function ($row) {
                $worthItemsDetails = $this->itemTransactionService->worthItemsDetails($row->id);
                // Store details in the row object for later use in worth_sale_price
                $row->worthItemsDetails = $worthItemsDetails;
                return $this->formatWithPrecision($worthItemsDetails['totalPurchaseCost']);
            })
            ->addColumn('worth_sale_price', function ($row) {
                return $this->formatWithPrecision($row->worthItemsDetails['totalSalePrice']);
            })
            ->addColumn('worth_profit', function ($row) {
                return $this->formatWithPrecision($row->worthItemsDetails['totalSalePrice'] - $row->worthItemsDetails['totalPurchaseCost']);
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $editUrl = route('warehouse.edit', ['id' => $id]);
                $deleteUrl = route('warehouse.delete', ['id' => $id]);


                $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">';
                $actionBtn .= '<li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> ' . __('app.edit') . '</a>
                                </li>';
                $actionBtn .= ($row->is_deletable == 0) ? '' : '<li>
                                    <button type="button" class="dropdown-item text-danger deleteRequest " data-delete-id=' . $id . '><i class="bx bx-trash"></i> ' . __('app.delete') . '</button>
                                </li>';
                $actionBtn .= '</ul>
                        </div>';
                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function delete(Request $request): JsonResponse
    {

        $selectedRecordIds = $request->input('record_ids');

        /**
         * All selected record IDs are valid, proceed with the deletion
         * Delete all records with the selected IDs in one query
         * */
        try {
            Warehouse::whereIn('id', $selectedRecordIds)->where('is_deletable', 1)->delete();
        } catch (QueryException $e) {
            return response()->json(['message' => __('app.cannot_delete_records')], 422);
        }

        return response()->json([
            'status'    => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }

    /**
     * Ajax Response
     * Search for Select2 Bar list
     * */
    function getAjaxWarehouseSearchBarList()
    {
        $search = request('search');

        $user = auth()->user();

        $items = Warehouse::where(function ($query) use ($search) {
            $query->whereRaw('UPPER(name) LIKE ?', ['%' . strtoupper($search) . '%']);
        })
            ->when(!$user->is_allowed_all_warehouses, function ($query) use ($user) {
                $warehouseIds = UserWarehouse::where('user_id', $user->id)->pluck('warehouse_id');
                $query->whereIn('id', $warehouseIds)->get();
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
}
