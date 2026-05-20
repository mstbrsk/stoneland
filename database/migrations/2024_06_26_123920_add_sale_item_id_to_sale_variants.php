<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sale_variants', function (Blueprint $table) {
            $table->uuid('sale_item_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sale_variants', function (Blueprint $table) {
            $table->dropColumn('sale_item_id');
        });
    }
};
