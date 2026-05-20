<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('relation_type');
            $table->string('relation_id')->nullable();

            $table->text('message');

            $table->uuid('created_by');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
