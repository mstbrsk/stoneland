<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('proposal_no');

            $table->boolean('has_contact')->default(false);

            $table->uuid('contact_id')->nullable();
            $table->uuid('delivery_address_id')->nullable();
            $table->uuid('invoice_address_id')->nullable();

            $table->uuid('crm_lead_id')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('invoice_address')->nullable();

            $table->uuid('currency_id');

            $table->text('selected_items')->nullable();

            $table->timestamp('deadline_at')->nullable();
            $table->uuid('price_list_id')->nullable();
            $table->boolean('is_renewable')->nullable();
            $table->uuid('payment_condition_id')->nullable();

            $table->unsignedInteger('quantity')->nullable();
            $table->float('sub_total')->nullable();
            $table->float('total')->nullable();

            $table->text('notes')->nullable();
            $table->json('library')->nullable();

            $table->unsignedTinyInteger('status')->default(\App\Enums\Proposal\ProposalStatus::DRAFT);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->timestamps();

            $table->index([
                'proposal_no',
                'contact_id',
            ]);

            /*  $table->foreign('contact_id')->references('id')->on('contacts');
              $table->foreign('delivery_address_id')->references('id')->on('addresses');
              $table->foreign('invoice_address_id')->references('id')->on('addresses');*/
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
