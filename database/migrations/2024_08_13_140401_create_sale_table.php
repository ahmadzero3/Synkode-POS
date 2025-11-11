<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('sale_date');
            $table->string('prefix_code')->nullable();
            // Change the column type for count_id from string to unsignedBigInteger
            $table->unsignedBigInteger('count_id')->nullable(); // Changed from string to unsignedBigInteger
            $table->unsignedBigInteger('last_global_count_id')->default(0);
            $table->string('sale_code')->nullable();
            $table->string('reference_no')->nullable();

            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->foreign('sale_order_id')->references('id')->on('sale_orders');

            $table->unsignedBigInteger('party_id');
            $table->foreign('party_id')->references('id')->on('parties');

            /**
             * State of supply
             * Only if GST enabled
             * */
            $table->unsignedBigInteger('state_id')->nullable();
            $table->foreign('state_id')->references('id')->on('states');

            $table->text('note')->nullable();

            $table->decimal('round_off', 20, 4)->default(0);
            $table->decimal('grand_total', 20, 4)->default(0);
            $table->decimal('paid_amount', 20, 4)->default(0);

            $table->string('payment_type')->nullable();
            $table->decimal('payment_amount', 20, 4)->default(0);
            $table->decimal('balance', 20, 4)->default(0);
            $table->decimal('change_return', 20, 4)->default(0);

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
            $table->string('invoice_status')->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
