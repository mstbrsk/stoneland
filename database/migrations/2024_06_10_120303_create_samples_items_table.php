<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('samples_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sample_id');
            $table->uuid('product_id');
            $table->text('notes')->nullable();
            $table->unsignedInteger('qty');
            $table->text('selected_variants')->nullable();
            $table->timestamps();


            $table->index([
                'sample_id',
                 'product_id'

            ]);
            $table->foreign('sample_id')->references('id')->on('samples');
            $table->foreign('product_id')->references('id')->on('products');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samples_items');
    }
};
