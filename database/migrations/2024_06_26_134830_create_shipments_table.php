<?php

use App\Enums\Shipment\ShipmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('contact_id');
            $table->uuid('sale_id');
            $table->unsignedTinyInteger('status')->nullable()->comment(ShipmentStatus::class);

            $table->timestamps();

            $table->index([
               'sale_id',
               'contact_id',
            ]);

            $table->foreign('sale_id')->references('id')->on('sales');
            $table->foreign('contact_id')->references('id')->on('contacts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
