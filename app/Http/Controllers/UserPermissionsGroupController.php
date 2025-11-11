<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionGroupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Requests\RoleRequest;
use App\Http\Requests\PermissionRequest;
use App\Models\PermissionGroup;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserPermissionsGroupController extends Controller
{
    /**
     * Group View
     * @return Json
    */
    public function createGroup() : View {
        return view('roles-and-permissions.group.create');
    }
    public function editGroup($id) : View {

        $group = PermissionGroup::find($id);

        return view('roles-and-permissions.group.edit', compact('group'));
    }


    public function storeGroup(PermissionGroupRequest $request) : JsonResponse {
        $permission = PermissionGroup::create([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    public function updateGroup(PermissionGroupRequest $request) : JsonResponse {
        $validatedData = $request->validated();

        // Save the group details
        $settings = PermissionGroup::findOrNew($validatedData['id']);
        $settings->name = $validatedData['name'];
        $settings->status = $validatedData['status'];
        $settings->save();

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    public function listGroups() : View {
        return view('roles-and-permissions.group.list');
    }

    public function datatableList(Request $request){

        $data = PermissionGroup::query();
        return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format(app('company')['date_format']);
                    })
                    ->addColumn('action', function($row){
                            $id = $row->id;

                             $editUrl = route('permission.group.edit', ['id' => $id]);
                            $deleteUrl = route('permission.group.delete', ['id' => $id]);


                            $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> '.__('app.edit').'</a>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id='.$id.'><i class="bx bx-trash"></i> '.__('app.delete').'</button>
                                </li>
                            </ul>
                        </div>';
                            return $actionBtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

    public function deleteGroup(Request $request) : JsonResponse{

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = PermissionGroup::find($recordId);
            if (!$record) {
                // Invalid record ID, handle the error (e.g., show a message, log, etc.)
                return response()->json([
                    'status'    => false,
                    'message' => __('app.invalid_record_id',['record_id' => $recordId]),
                ]);

            }
            // You can perform additional validation checks here if needed before deletion
        }

        // All selected record IDs are valid, proceed with the deletion
        // Delete all records with the selected IDs in one query
        try {
            // Attempt deletion (as in previous responses)
            PermissionGroup::whereIn('id', $selectedRecordIds)->delete();
            return response()->json([
                'status'    => true,
                'message' => __('app.record_deleted_successfully'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status'    => false,
                    'message' => __('app.cannot_delete_records'),
                ],409);
            } else {
                // Handle other types of database exceptions
                return response()->json([
                    'status'    => false,
                    'message' => __('app.unexpected_error_occurred'),
                ],500);
            }
        }
    }
}