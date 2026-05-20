<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();

            $table->index(['name', 'created_by', 'updated_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
