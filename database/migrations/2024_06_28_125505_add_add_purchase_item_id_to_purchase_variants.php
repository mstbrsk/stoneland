<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_variants', function (Blueprint $table) {
            $table->uuid('purchase_item_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_variants', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_item_id'
            ]);
        });
    }
};
