<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_offices', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->unsignedInteger('city_id');
            $table->string('county')->nullable();
            $table->unsignedInteger('code')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_offices');
    }
};
