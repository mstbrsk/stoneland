<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('stock_code')->nullable();
            $table->string('product_name')->nullable();
            $table->uuid('product_id');
            $table->text('attribute_items')->nullable()->comment('Kırmızı -> XL -> Paçalı');
            $table->unsignedInteger('stock')->default(0)->nullable();

            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();

            $table->timestamps();

            $table->index([
                'stock_code',
                'product_id',
                'product_name',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
