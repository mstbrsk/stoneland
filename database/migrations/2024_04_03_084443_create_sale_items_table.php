<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sale_id');

            $table->boolean('receipt')->default(false);
            $table->uuid('product_id');
            $table->text('notes')->nullable();
            $table->unsignedInteger('qty');
            $table->float('unit_price');
            $table->float('vat_rate');
            $table->float('vat_line_total');
            $table->float('line_total');

            $table->text('selected_variants')->nullable();

            $table->timestamps();

            $table->index([
                'sale_id',
                'product_id'
            ]);

            $table->foreign('sale_id')->references('id')->on('sales');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
