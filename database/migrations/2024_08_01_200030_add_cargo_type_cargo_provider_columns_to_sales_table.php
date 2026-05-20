<?php

use App\Enums\Proposal\CargoType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedTinyInteger('cargo_type')->nullable()->comment(CargoType::class);
            $table->string('cargo_provider')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'cargo_type',
                'cargo_provider'
            ]);
        });
    }
};
