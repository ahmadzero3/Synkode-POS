<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use App\Models\Role;
use App\Models\UserWarehouse;

class UserController extends Controller
{
    /**
     * Create a new user.
     *
     * This function returns a view to create a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Edit a user.
     *
     * @param int $id The ID of the user to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id): View
    {
        $user = User::with('userWarehouses')->find($id);

        return view('users.edit', compact('user'));
    }

    /**
     * Return JsonResponse
     * */
    public function store(UserRequest $request)
    {
        DB::beginTransaction();

        try {
            $filename = null;

            $validatedData = $request->validated();

            // Ensure no ID is passed for new user creation
            unset($validatedData['id']);

            $validatedData['password'] = Hash::make($validatedData['password']);

            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                $filename = $this->uploadImage($request->file('avatar'));
            }

            $validatedData['avatar'] = $filename;

            $validatedData['is_allowed_all_warehouses'] = $request->has('is_allowed_all_warehouses') ? 1 : 0;

            $user = User::create($validatedData);

            $role = Role::find($validatedData['role_id']);
            $user->assignRole($role);

            $permissions = $role->permissions;
            $user->givePermissionTo($permissions);

            $this->updateUserWarehouses($user->id);

            DB::commit();

            return response()->json([
                'message' => __('app.record_saved_successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('User creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => __('app.something_went_wrong'),
            ], 500);
        }
    }

    private function uploadImage($image): string
    {
        // Generate a unique filename for the image
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();

        // Save the image to the storage disk
        Storage::putFileAs('public/images/avatar', $image, $filename);

        return $filename;
    }

    public function update(UserRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            if (!empty($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }
            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                $validatedData['avatar'] = $this->uploadImage($request->file('avatar'));
            }

            $validatedData['is_allowed_all_warehouses'] = $request->has('is_allowed_all_warehouses') ? 1 : 0;

            // Save the service details
            User::where('id', $validatedData['id'])->update($validatedData);

            //This will add entry in model_has_roles entry
            $user = User::find($validatedData['id']);
            $roleId = $validatedData['role_id']; // Extract the role ID
            $role = Role::findOrFail($roleId); // Fetch the Role object
            $user->roles()->detach(); //Remove All Roles of current User object
            $user->assignRole($role); // Assign the role to the user

            $permissions = $role->permissions;

            $user->syncPermissions($permissions);

            /*Update Users Allowed warehouse*/
            $this->updateUserWarehouses($user->id);

            DB::commit();
            return response()->json([
                'message' => __('app.record_updated_successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('User update failed: ' . $e->getMessage());

            return response()->json([
                'message' => __('app.something_went_wrong'),
            ], 500);
        }
    }

    public function updateUserWarehouses($userId)
    {
        /**
         * Delete User Data Warehouse
         * */
        UserWarehouse::where('user_id', $userId)->delete();

        /**
         * Update User Warehouse Data
         * only if all warehouse not allowed
         * */
        if (!request()->has('is_allowed_all_warehouses')) {
            $warehouseIds = request()->input('warehouse_ids');

            if (is_array($warehouseIds) && count($warehouseIds) > 0) {
                foreach ($warehouseIds as $warehouseId) {
                    UserWarehouse::create([
                        'user_id' => $userId,
                        'warehouse_id' => $warehouseId,
                    ]);
                }
            } else {
                throw new \Exception("Permit atleast one warehouse to user!");
            }
        }

        return true;
    }
    public function list(): View
    {
        return view('users.list');
    }

    public function datatableList(Request $request)
    {

        $data = User::select('users.*', 'roles.name as role_name')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', '!=', auth()->id());


        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format(app('company')['date_format']);
            })
            ->addColumn('role_name', function ($row) {
                return $row->role->name ?? null;
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $editUrl = route('user.edit', ['id' => $id]);
                $deleteUrl = route('user.delete', ['id' => $id]);


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

    public function delete(Request $request): JsonResponse
    {

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = User::find($recordId);
            if (!$record) {
                // Invalid record ID, handle the error (e.g., show a message, log, etc.)
                return response()->json([
                    'status' => false,
                    'message' => __('app.invalid_record_id', ['record_id' => $recordId]),
                ]);
            }
            // You can perform additional validation checks here if needed before deletion
        }

        /**
         * All selected record IDs are valid, proceed with the deletion
         * Delete all records with the selected IDs in one query
         * */
        User::whereIn('id', $selectedRecordIds)->delete();

        return response()->json([
            'status' => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }
    /**
     * Get current user profile information.
     *
     * This function returns a view
     *
     * @return \Illuminate\View\View
     */
    public function getProfile(): View
    {
        $user = User::find(auth()->user()->id);

        return view('profile.edit', compact('user'));
    }

    public function ajaxCashiers(Request $request)
    {
        $search = $request->input('search', '');

        $query = User::whereHas('roles', function ($q) {
            $q->whereRaw('LOWER(name) = ?', ['cashier']);
        })->with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $users = $query->limit(10)->get();

        $results = $users->map(function ($u) {
            $roleName = $u->roles->first() ? $u->roles->first()->name : '';
            return [
                'id' => $u->id,
                'text' => trim($u->username . ' (' . strtolower($roleName) . ')'),
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function ajaxAllUsers(Request $request)
    {
        $search = $request->input('search', '');

        $users = User::where('role_id', '!=', 1) // Exclude superadmin (assuming role_id 1 is superadmin)
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->select('id', 'first_name', 'last_name', 'email', 'username')
            ->orderBy('first_name')
            ->limit(50)
            ->get();

        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'text' => $user->first_name . ' ' . $user->last_name . ' (' . $user->email . ')',
                'email' => $user->email,
            ];
        });

        return response()->json([
            'results' => $results
        ]);
    }
}
