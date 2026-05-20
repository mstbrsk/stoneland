<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTables extends Migration
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 3)->default('');
            $table->string('name', 100)->default('');
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('country_id');
            $table->string('name');
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('city_id');
            $table->string('name');
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
        });
    }


    public function down()
    {
        Schema::dropIfExists('countries');
        Schema::dropIfExists('cities');
    }
}
