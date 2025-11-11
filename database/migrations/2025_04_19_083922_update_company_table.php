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
        Schema::table('company', function (Blueprint $table) {
            $table->boolean('show_brand_on_invoice')->default(1)->after('show_signature_on_invoice');
            $table->boolean('show_tax_number_on_invoice')->default(1)->after('show_brand_on_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company', function (Blueprint $table) {
            $table->dropColumn(['show_brand_on_invoice', 'show_tax_number_on_invoice']);
        });
    }
};
