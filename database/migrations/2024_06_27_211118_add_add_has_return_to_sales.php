<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->uuid('sale_return_id')->nullable();

            $table->index([
                'sale_return_id'
            ]);

            $table->foreign('sale_return_id')->references('id')->on('sale_returns');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'sale_return_id',
            ]);
        });
    }
};
