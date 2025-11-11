<?php

namespace Database\Seeders\Updates;


use Illuminate\Database\Seeder;

use App\Models\PermissionGroup;
use App\Models\Prefix;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Version235Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        echo "Version235Seeder Running...";
        $this->updatePermissions();
        $this->addNewPermissions();

        echo "\Version235Seeder Completed!!\n";
    }

    public function updatePermissions()
    {
        Prefix::query()->update(['stock_adjustment' => 'SA/']);
    }

    public function addNewPermissions()
    {
        //
    }//funciton addNewPermissions
}
