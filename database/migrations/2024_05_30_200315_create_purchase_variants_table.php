<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('purchase_id');
            $table->uuid('product_id');
            $table->uuid('variant_id');

            $table->unsignedInteger('qty');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'product_id',
                'variant_id',
                'purchase_id',
            ]);

            $table->foreign('purchase_id')->references('id')->on('purchases');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_variants');
    }
};
