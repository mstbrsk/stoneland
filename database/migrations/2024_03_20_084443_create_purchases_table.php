<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('supplier_id');

            $table->text('selected_items')->nullable();
            $table->text('selected_variants')->nullable();

            $table->uuid('currency_id');
            $table->timestamp('purchased_at');
            $table->timestamp('deadline_at');
            $table->string('purchase_no', 6);
            $table->string('source_doc', 255)->nullable();
            $table->unsignedInteger('warehouse_id');
            $table->string('invoice_no', 255)->nullable();

            $table->unsignedInteger('quantity')->nullable();
            $table->float('sub_total')->nullable();
            $table->float('total')->nullable();

            $table->text('notes')->nullable();
            //$table->text('images')->nullable();
            $table->json('library')->nullable();

            $table->unsignedTinyInteger('status')->default(\App\Enums\Purchase\PurchaseStatus::WAITING_FOR_APPROVAL);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->timestamps();

            $table->index([
                'warehouse_id',
                'supplier_id',
                'invoice_no',
                'purchase_no',
            ]);

            $table->foreign('supplier_id')->references('id')->on('contacts');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
