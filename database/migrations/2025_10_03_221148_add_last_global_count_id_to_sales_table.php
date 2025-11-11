<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'last_global_count_id')) {
                $table->unsignedBigInteger('last_global_count_id')
                      ->default(0)
                      ->after('count_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'last_global_count_id')) {
                $table->dropColumn('last_global_count_id');
            }
        });
    }
};
