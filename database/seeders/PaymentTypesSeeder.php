<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('payment_types')->insert([
            [
                'unique_code' => 'CASH',
                'name' => 'Cash',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
                'is_deletable' => 0, //Restricted to delete
            ],
            [
                'unique_code' => 'CHEQUE',
                'name' => 'Cheque',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
                'is_deletable' => 0, //Restricted to delete
            ],

        ]);
    }
}
