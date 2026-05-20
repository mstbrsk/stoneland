<?php

use App\Enums\Sample\SampleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('samples_items');
        Schema::dropIfExists('samples');

        Schema::create('samples', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('has_contact')->default(false);

            $table->uuid('contact_id')->nullable();
            $table->unsignedInteger('warehouse_id')->nullable();

            $table->text('contact_name')->nullable();

            $table->text('invoice_no')->nullable();

            $table->text('data')->nullable();
            $table->text('return_data')->nullable();

            $table->json('library')->nullable();

            $table->unsignedInteger('status')->default(SampleStatus::PENDING);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->uuid('shipped_by')->nullable();
            $table->timestamp('shipped_at')->nullable();

            $table->timestamps();

            $table->index([
                'contact_id',
                'warehouse_id',
                'invoice_no',
            ]);

            $table->foreign('contact_id')->references('id')->on('contacts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
