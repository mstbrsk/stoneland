<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sales_no');
            $table->uuid('contact_id');

            $table->text('selected_variants')->nullable();
            $table->uuid('currency_id');

            $table->uuid('delivery_address_id')->nullable();
            $table->uuid('invoice_address_id')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->uuid('price_list_id')->nullable();
            $table->boolean('is_renewable')->nullable();
            $table->boolean('has_receipt')->nullable();
            $table->uuid('payment_condition_id')->nullable();

            $table->unsignedInteger('quantity')->nullable();
            $table->float('sub_total')->nullable();
            $table->float('total')->nullable();

            $table->text('notes')->nullable();
            $table->json('library')->nullable();

            $table->boolean('was_proposal')->default(false);
            $table->uuid('proposal_id')->nullable();

            $table->unsignedTinyInteger('status')->default(\App\Enums\Sale\SaleStatus::DRAFT);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->timestamps();

            $table->index([
                'sales_no',
                'contact_id',
            ]);

            $table->foreign('contact_id')->references('id')->on('contacts');
            $table->foreign('delivery_address_id')->references('id')->on('addresses');
            $table->foreign('invoice_address_id')->references('id')->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
