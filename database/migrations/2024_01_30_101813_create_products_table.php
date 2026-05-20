<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('stock_code')->unique();
            $table->float('sales_price')->nullable();
            $table->float('cost')->nullable();

            $table->float('tax_rate')->nullable();

            $table->unsignedInteger('unit_id')->nullable();
            $table->string('photo')->nullable();
            $table->text('product_attributes')->nullable();
            $table->boolean('can_purchase')->default(false)->nullable();
            $table->boolean('can_sale')->default(true)->nullable();
            $table->boolean('allow_negative_stock')->default(true)->nullable();
            $table->unsignedBigInteger('warehouse_id');

            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
