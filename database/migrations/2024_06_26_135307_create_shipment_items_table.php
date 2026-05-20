<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('shipment_id');
            $table->uuid('sale_id');
            $table->uuid('product_id');
            $table->uuid('variant_id');
            $table->unsignedInteger('shipped_qty');
            $table->uuid('delivery_address_id');

            $table->timestamps();

            $table->index([
                'shipment_id',
                'sale_id',
                'product_id',
                'variant_id',
                'delivery_address_id',
            ]);

            $table->foreign('shipment_id')->references('id')->on('shipments');
            $table->foreign('sale_id')->references('id')->on('sales');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('variant_id')->references('id')->on('product_variants');
            $table->foreign('delivery_address_id')->references('id')->on('addresses');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
