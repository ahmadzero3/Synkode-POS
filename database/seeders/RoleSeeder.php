<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use updateOrCreate to avoid duplicate key issues
        $superAdminRole = Role::updateOrCreate(
            ['name' => 'SuperAdmin'],
            ['status' => 1]
        );

        $adminRole = Role::updateOrCreate(
            ['name' => 'Admin'],
            ['status' => 1]
        );

        $cashierRole = Role::updateOrCreate(
            ['name' => 'Cashier'],
            ['status' => 1]
        );

        // Assign roles to users
        $superAdminUser = User::where('email', 'superadmin@example.com')->first();
        if ($superAdminUser) {
            $superAdminUser->role_id = $superAdminRole->id;
            $superAdminUser->save();
            $superAdminUser->assignRole($superAdminRole);
        }

        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $adminUser->role_id = $adminRole->id;
            $adminUser->save();
            $adminUser->assignRole($adminRole);
        }

        $cashierUser = User::where('email', 'cashier@example.com')->first();
        if ($cashierUser) {
            $cashierUser->role_id = $cashierRole->id;
            $cashierUser->save();
            $cashierUser->assignRole($cashierRole);
        }

        // Get all permissions for SuperAdmin
        $allPermissions = Permission::all();
        if ($superAdminUser) {
            $superAdminUser->givePermissionTo($allPermissions);
        }

        // Assign permissions to Admin role (all permissions EXCEPT user, role, and register permissions)
        $adminPermissions = Permission::where(function ($query) {
            $query->where('name', 'NOT LIKE', 'user.%')
                ->where('name', 'NOT LIKE', 'role.%')
                ->where('name', 'NOT LIKE', 'register.%');
        })->get();

        $adminRole->givePermissionTo($adminPermissions);
        if ($adminUser) {
            $adminUser->givePermissionTo($adminPermissions);
        }

        // Assign permissions to Cashier role (only sale.bill permissions)
        $cashierPermissions = Permission::where('name', 'LIKE', 'sale.invoice.%')->get();
        $cashierRole->givePermissionTo($cashierPermissions);
        if ($cashierUser) {
            $cashierUser->givePermissionTo($cashierPermissions);
        }

        // Reset sequences after seeding
        $maxUserId = User::max('id');
        if ($maxUserId) {
            DB::statement("SELECT setval('users_id_seq', $maxUserId)");
        }

        $maxRoleId = Role::max('id');
        if ($maxRoleId) {
            DB::statement("SELECT setval('roles_id_seq', $maxRoleId)");
        }
    }
}