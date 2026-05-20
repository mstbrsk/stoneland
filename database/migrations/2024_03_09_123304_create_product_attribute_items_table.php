<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_attribute_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_attribute_id');
            $table->string('value');

            $table->timestamps();

            $table->index([
                'product_attribute_id'
            ]);

            $table->foreign('product_attribute_id')->references('id')->on('product_attributes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_items');
    }
};
