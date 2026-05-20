<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('price_lists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');

            $table->uuid('contact_group_id');

            $table->unsignedTinyInteger('type');
            $table->float('value');

            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();

            $table->timestamps();

            $table->index([
                'name',
                'contact_group_id',
                'updated_by',
            ]);

            $table->foreign('contact_group_id')->references('id')->on('contact_groups');
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_lists');
    }
};
