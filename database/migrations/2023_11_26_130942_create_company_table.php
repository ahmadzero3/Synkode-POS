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
        Schema::create('company', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile');
            $table->string('email');
            $table->text('address')->nullable();
            $table->text('bank_details')->nullable();
            $table->string('colored_logo')->nullable();
            $table->string('light_logo')->nullable();
            $table->string('signature')->nullable();
            $table->string('language_code')->nullable();
            $table->string('language_name')->nullable();
            $table->string('active_sms_api')->nullable();
            $table->integer('number_precision')->default(2);
            $table->integer('quantity_precision')->default(2);
            $table->integer('show_sku')->default(1);
            $table->integer('show_mrp')->default(1);
            $table->integer('enable_serial_tracking')->default(1);
            $table->integer('enable_batch_tracking')->default(2);
            $table->integer('enable_mfg_date')->default(1);
            $table->integer('enable_exp_date')->default(1);
            $table->integer('enable_model')->default(0);
            $table->integer('enable_color')->default(0);
            $table->integer('enable_size')->default(0);
            $table->boolean('enable_minimum_stock_qty')->default(false);
            $table->integer('minimum_stock_qty')->nullable();
            $table->boolean('enable_print_tax')->default(false);
            $table->boolean('enable_print_discount')->default(false);

            $table->integer('show_tax_summary')->default(1);
            $table->string('tax_type')->default('tax');
            $table->integer('show_signature_on_invoice')->default(1);
            $table->integer('show_terms_and_conditions_on_invoice')->default(1);
            $table->text('terms_and_conditions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
