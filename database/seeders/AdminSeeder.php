<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::create([
            'id' => 1,
            'username' => 'superadmin',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('synkode@123456789'),
            'status' => 1,
        ]);

        // Create Admin user (will not have access to user and role permissions)
        User::create([
            'id' => 2,
            'username' => 'admin',
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
            'status' => 1,
        ]);

        // Create Cashier user (will have access only to sale.bill)
        User::create([
            'id' => 3,
            'username' => 'cashier',
            'first_name' => 'Cashier',
            'last_name' => 'User',
            'email' => 'cashier@example.com',
            'password' => Hash::make('12345678'),
            'status' => 1,
        ]);
    }
}
