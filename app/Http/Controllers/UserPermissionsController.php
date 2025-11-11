<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class UserPermissionsController extends Controller
{
    /**
     * Create a new permission.
     *
     * This function returns a view to create a new permission.
     *
     * @return \Illuminate\View\View
     */
    public function createPermission(): View
    {
        return view('roles-and-permissions.permission.create');
    }

    /**
     * Edit a permission.
     *
     * @param int $id The ID of the permission to edit.
     * @return \Illuminate\View\View
     */
    public function editPermission($id): View
    {

        $permission = Permission::find($id);

        return view('roles-and-permissions.permission.edit', compact('permission'));
    }

    public function storePermission(PermissionRequest $request): JsonResponse
    {
        $permission = Permission::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'permission_group_id' => $request->permission_group_id,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    public function updatePermission(PermissionRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Save the permission details
        $settings = Permission::findOrNew($validatedData['id']);
        $settings->permission_group_id = $validatedData['permission_group_id'];
        $settings->name = $validatedData['name'];
        $settings->status = $validatedData['status'];
        $settings->display_name = $validatedData['display_name'];
        $settings->save();

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    public function listPermissions()
    {
        return view('roles-and-permissions.permission.list');
    }

    public function datatableList(Request $request)
    {

        $data = Permission::select(
            'permissions.name',
            'permissions.display_name',
            'permissions.status',
            'permissions.created_at',
            'permissions.id',
            'permission_groups.name as group_name' // Alias for the group name
        )
            ->leftJoin('permission_groups', 'permissions.permission_group_id', '=', 'permission_groups.id')
            ->get(); // To actually retrieve the data


        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format(app('company')['date_format']);
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $editUrl = route('permission.edit', ['id' => $id]);
                $deleteUrl = route('permission.delete', ['id' => $id]);


                $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> ' . __('app.edit') . '</a>
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

    public function deletePermission(Request $request): JsonResponse
    {

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = Permission::find($recordId);
            if (!$record) {
                // Invalid record ID, handle the error (e.g., show a message, log, etc.)
                return response()->json([
                    'status'    => false,
                    'message' => __('app.invalid_record_id', ['record_id' => $recordId]),
                ]);
            }
            // You can perform additional validation checks here if needed before deletion
        }

        // All selected record IDs are valid, proceed with the deletion
        // Delete all records with the selected IDs in one query
        try {
            // Attempt deletion (as in previous responses)
            Permission::whereIn('id', $selectedRecordIds)->delete();
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
            } else {
                // Handle other types of database exceptions
                return response()->json([
                    'status'    => false,
                    'message' => __('app.unexpected_error_occurred'),
                ], 500);
            }
        }
    }
}