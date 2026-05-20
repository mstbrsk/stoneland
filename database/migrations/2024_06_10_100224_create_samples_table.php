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
        Schema::create('samples', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('has_contact')->default(false);

            $table->uuid('contact_id')->nullable();
            $table->unsignedInteger('warehouse_id')->nullable();


            $table->text('contact_name')->nullable();

            $table->text('invoice_no')->nullable();

            $table->text('selected_variants')->nullable();
            $table->json('library')->nullable();


            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->timestamps();

            $table->index([

                'contact_id',
            ]);
            $table->foreign('contact_id')->references('id')->on('contacts');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
