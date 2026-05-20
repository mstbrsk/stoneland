<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->uuid('purchase_return_id')->nullable();
            $table->string('sale_invoice_no')->nullable();

            $table->index([
                'purchase_return_id'
            ]);

            $table->foreign('purchase_return_id')->references('id')->on('purchase_returns');
        });

        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->string('sale_invoice_no')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_return_id',
                'sale_invoice_no',
            ]);
        });

        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropColumn([
                'sale_invoice_no'
            ]);
        });
    }
};
