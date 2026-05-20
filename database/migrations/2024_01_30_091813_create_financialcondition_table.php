<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('financial_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('name');
            $table->string('code')->nullable();
            $table->string('code2')->nullable();
            $table->string('code3')->nullable();
            $table->string('category')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_conditions');
    }
};
