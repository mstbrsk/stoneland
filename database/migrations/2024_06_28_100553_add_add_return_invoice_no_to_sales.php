<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('return_invoice_no')->nullable();
        });

        Schema::table('sale_returns', function (Blueprint $table) {
            $table->string('return_invoice_no')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'return_invoice_no'
            ]);
        });

        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropColumn([
                'return_invoice_no'
            ]);
        });
    }
};
