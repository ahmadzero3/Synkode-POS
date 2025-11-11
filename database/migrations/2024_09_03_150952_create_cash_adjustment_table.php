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
        Schema::create('cash_adjustments', function (Blueprint $table) {
            $table->id();
            $table->date('adjustment_date');
            $table->string('adjustment_type');

            $table->unsignedBigInteger('payment_type_id');
            $table->foreign('payment_type_id')->references('id')->on('payment_types');


            $table->unsignedBigInteger('register_id')->nullable();
            $table->foreign('register_id')->references('id')->on('registers')->onDelete('set null');

            //Each Qty Price: with or without tax
            $table->decimal('amount', 20, 4)->default(0);
            $table->text('note')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_adjustments');
    }
};
