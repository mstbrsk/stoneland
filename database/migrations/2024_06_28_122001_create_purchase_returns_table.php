<?php

use App\Enums\Purchase\PurchaseReturnStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('purchase_id');
            $table->text('returns')->nullable()->comment('json -> item_id , variant_id , qty');

            $table->unsignedInteger('status')->default(PurchaseReturnStatus::PENDING);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
