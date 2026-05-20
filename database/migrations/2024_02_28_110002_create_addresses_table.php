<?php

use App\Enums\Address\AddressType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('is_my_address')->default(false);
            $table->string('name')->nullable()->comment('İlgili firmaya ait olabilir');
            $table->uuid('contact_id')->nullable();
            $table->tinyInteger('type')->comment(AddressType::class);
            $table->text('address');

            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
