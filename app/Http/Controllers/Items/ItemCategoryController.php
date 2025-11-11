<?php

namespace App\Http\Controllers\Items;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;

use App\Models\Items\ItemCategory;
use App\Http\Requests\ItemCategoryRequest;

class ItemCategoryController extends Controller
{

    /**
     * Create a new category.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {

        return view('items.category.create');
    }

    /**
     * List the category
     *
     * @return \Illuminate\View\View
     */
    public function list(): View
    {
        return view('items.category.list');
    }

    /**
     * Edit a items.
     *
     * @param int $id The ID of the item to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id): View
    {

        $category = ItemCategory::find($id);

        return view('items.category.edit', compact('category'));
    }

    /**
     * Return JsonResponse
     * */
    public function store(ItemCategoryRequest $request): JsonResponse
    {

        $filename = null;

        // Get the validated data from the categoryRequest
        $validatedData = $request->validated();

        // Create a new category record using Eloquent and save it
        $newCategory = ItemCategory::create($validatedData);

        return response()->json([
            'status'    => true,
            'message' => __('app.record_saved_successfully'),
            'data'  => [
                'id' => $newCategory->id,
                'name' => $newCategory->name,
            ]
        ]);
    }

    /**
     * Return JsonResponse
     * */
    public function update(ItemCategoryRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Save the category details
        ItemCategory::where('id', $validatedData['id'])->update($validatedData);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    public function datatableList(Request $request)
    {

        $data = ItemCategory::with('user');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format(app('company')['date_format']);
            })
            ->addColumn('username', function ($row) {
                return $row->user->username ?? '';
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $editUrl = route('item.category.edit', ['id' => $id]);
                $deleteUrl = route('item.category.delete', ['id' => $id]);


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

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = ItemCategory::find($recordId);
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
            ItemCategory::whereIn('id', $selectedRecordIds)->where('is_deletable', 1)->delete();
            return response()->json([
                'status'    => true,
                'message' => __('app.record_deleted_successfully'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status'    => false,
                    'message' => __('app.cannot_delete_records'),
                ], 409);
            }
        }
    }

    public function getAjaxSearchBarList()
    {
        $search = request('search', '');
        $page = (int) request('page', 1);
        $perPage = 10;

        $query = ItemCategory::query()
            ->when($search, function ($q) use ($search) {
                $q->whereRaw('UPPER(name) LIKE ?', ['%' . strtoupper($search) . '%']);
            });

        $total = $query->count();

        $items = $query
            ->select('id', 'name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $results = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->name,
            ];
        });

        return response()->json([
            'results' => $results,
            'hasMore' => ($page * $perPage) < $total, // Important!
        ]);
    }
}
