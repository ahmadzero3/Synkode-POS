<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prefix', function (Blueprint $table) {
            $table->string('stock_adjustment')->after('stock_transfer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prefix', function (Blueprint $table) {
            $table->dropColumn(['stock_adjustment']);
        });
    }
};
