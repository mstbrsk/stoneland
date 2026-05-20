<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('proposal_id');

            $table->uuid('product_id');
            $table->text('notes')->nullable();

            $table->unsignedInteger('qty');

            $table->float('unit_price');
            $table->float('vat_rate');
            $table->float('vat_line_total');
            $table->float('line_total');

            $table->timestamps();

            $table->index([
                'proposal_id',
                'product_id'
            ]);

            $table->foreign('proposal_id')->references('id')->on('proposals');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_products');
    }
};
