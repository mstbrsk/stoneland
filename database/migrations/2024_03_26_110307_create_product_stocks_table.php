<?php

use App\Enums\StockProcessType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_transactions', function (Blueprint $table) {
            $table->id();

            $table->uuid('product_id');
            $table->uuid('variant_id')->nullable();

            $table->unsignedInteger('quantity');
            $table->string('type', 20)->default(StockProcessType::IN->value)->comment('in,sale,etc');
            $table->string('relation_type');
            $table->string('relation_id')->nullable();

            $table->unsignedInteger('warehouse_id');

            $table->uuid('contact_id')->nullable();

            $table->text('notes')->nullable();

            $table->uuid('created_by')->nullable();

            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['product_id', 'contact_id', 'variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_transactions');
    }
};
