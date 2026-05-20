<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouse_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedInteger('from');
            $table->unsignedInteger('to');
            $table->uuid('transfer_id');
            $table->uuid('product_id');
            $table->uuid('variant_id')->nullable();
            $table->unsignedInteger('qty');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_transfers');
    }
};
