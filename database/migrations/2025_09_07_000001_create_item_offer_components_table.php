<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('item_offer_components', function (Blueprint $table) {
            $table->id();

            // The combo/offer item
            $table->unsignedBigInteger('offer_item_id');
            // The component item that makes up the offer
            $table->unsignedBigInteger('component_item_id');
            // Quantity of this component inside one unit of the offer
            $table->decimal('quantity', 20, 4)->default(1);

            $table->timestamps();

            $table->foreign('offer_item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('component_item_id')->references('id')->on('items')->onDelete('restrict');

            $table->unique(['offer_item_id', 'component_item_id'], 'uniq_offer_component');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_offer_components');
    }
};
