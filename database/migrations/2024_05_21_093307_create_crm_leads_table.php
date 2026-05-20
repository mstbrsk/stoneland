<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('relation_id')->nullable();

            $table->string('proposal_no')->nullable();
            $table->text('contact_name')->nullable();
            $table->text('contacted_person');
            $table->text('notes');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->timestamp('contacted_at')->nullable();

            $table->timestamps();

            $table->index([
                'created_by',
                'updated_by'
            ]);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('crm_leads');
    }
};
