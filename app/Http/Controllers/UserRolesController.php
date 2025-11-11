<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class UserRolesController extends Controller
{
    /**
     * Create a new role.
     *
     * This function returns a view to create a new role.
     *
     * @return \Illuminate\View\View
     */
    public function createRole(): View
    {

        $roleAndPermission = $this->getGroupPermissionsList();

        return view('roles-and-permissions.role.create', compact('roleAndPermission'));
    }

    /**
     * Edit a role.
     *
     * @param int $id The ID of the role to edit.
     * @return \Illuminate\View\View
     */
    public function editRole($id): View
    {

        $roleAndPermission = $this->getGroupPermissionsList();

        $role = Role::find($id);

        $allocatedPermissions = $role->permissions->pluck('id');

        return view('roles-and-permissions.role.edit', compact('roleAndPermission', 'role', 'allocatedPermissions'));
    }

    /**
     * Return Complete Group & Permissions List
     * */
    private function getGroupPermissionsList(): Collection
    {
        return Permission::select(
            'permissions.name',
            'permissions.display_name',
            'permissions.status',
            'permissions.created_at',
            'permissions.id',
            'permissions.permission_group_id',
            'permission_groups.name as group_name'
        )
            ->leftJoin('permission_groups', 'permission_groups.id', '=', 'permissions.permission_group_id')
            ->get();
    }

    public function storeRole(RoleRequest $request)
    {
        DB::beginTransaction();

        try {
            unset($request['id']);

            $role = Role::create([
                'name' => $request->name,
                'status' => $request->status,
            ]);

            /**
             * Save Role Id & Permission Id
             * Table: role_and_permissions
             * */
            $permissions = $request->input('permission');

            $permissionValues = [];

            foreach ($permissions as $key => $value) {
                if ($value === 'on') {
                    $permissionValues[] = $key;
                }
            }

            /**
             * Assign Permission to the role
             * Table: model_has_roles
             * */
            $role->givePermissionTo($permissionValues);

            DB::commit();

            return response()->json([
                'message' => __('app.record_saved_successfully'),
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Role creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => __('app.something_went_wrong'),
            ], 500);
        }
    }


    public function updateRole(RoleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();

            // Save the role details
            $role = Role::findOrNew($validatedData['id']);
            $role->name = $validatedData['name'];
            $role->status = $validatedData['status'];
            $role->save();

            /**
             * Save Role Id & Permission Id
             * Table: role_and_permissions
             * */
            $permissions = $request->input('permission');

            $permissionValues = [];

            foreach ($permissions as $key => $value) {
                if ($value === 'on') {
                    $permissionValues[] = $key;
                }
            }

            /**
             * Assign Permission to the role
             * Table: model_has_roles
             * */
            $role->syncPermissions($permissionValues);

            /**
             * Assign permissions to the user
             * Table: model_has_permissions
             * */
            $users = User::where('role_id', $role->id)->get();
            foreach ($users as $user) {
                $user->syncPermissions($role->permissions->pluck('id'));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => __('app.record_updated_successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::channel('custom')->critical($e->getMessage());

            return response()->json([
                'message' => __('app.something_went_wrong') . __('app.check_custom_log_file'),
            ], 409);
        }
    }

    public function listRoles(): View
    {
        return view('roles-and-permissions.role.list');
    }

    public function datatableList(Request $request)
    {

        $data = Role::query();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format(app('company')['date_format']);
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $editUrl = route('role.edit', ['id' => $id]);
                $deleteUrl = route('role.delete', ['id' => $id]);


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

    public function deleteRole(Request $request): JsonResponse
    {

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = Role::find($recordId);
            if (!$record) {
                // Invalid record ID, handle the error (e.g., show a message, log, etc.)
                return response()->json([
                    'status' => false,
                    'message' => __('app.invalid_record_id', ['record_id' => $recordId]),
                ]);
            }
            // You can perform additional validation checks here if needed before deletion
        }

        // All selected record IDs are valid, proceed with the deletion
        // Delete all records with the selected IDs in one query
        try {
            // Attempt deletion (as in previous responses)
            Role::whereIn('id', $selectedRecordIds)->delete();
            return response()->json([
                'status' => true,
                'message' => __('app.record_deleted_successfully'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status' => false,
                    'message' => __('app.cannot_delete_records'),
                ], 409);
            } else {
                // Handle other types of database exceptions
                return response()->json([
                    'status' => false,
                    'message' => __('app.unexpected_error_occurred'),
                ], 500);
            }
        }
    }
}