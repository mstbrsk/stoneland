<?php

use App\Enums\CompanyType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->uuid('user_id')->nullable();
            $table->unsignedTinyInteger('company_type')->default(CompanyType::COMPANY->value);
            $table->text('address')->nullable();
            $table->text('second_address')->nullable();
            $table->string('district')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('country')->nullable();
            $table->string('tax_administration')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('accounting_phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('language')->nullable();
            $table->text('tickets')->nullable();
            $table->string('photo')->nullable();
            $table->uuid('group_id')->nullable();

            $table->boolean('is_supplier')->default(false);

            $table->uuid('payment_condition_id')->nullable();
            $table->uuid('exchange_id')->nullable();
            $table->uuid('price_list_id')->nullable();
            $table->uuid('shipping_type_id')->nullable();
            $table->uuid('pos_campaign_id')->nullable();
            $table->uuid('financial_condition_id')->nullable();
            $table->uuid('currency_id')->nullable();

            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'city_id',
                'price_list_id'
            ]);

            $table->foreign('payment_condition_id')->references('id')->on('payment_conditions');
            $table->foreign('exchange_id')->references('id')->on('exchange_rates');
            $table->foreign('price_list_id')->references('id')->on('price_lists');
            $table->foreign('shipping_type_id')->references('id')->on('shipping_types');
            $table->foreign('pos_campaign_id')->references('id')->on('pos_campaigns');
            $table->foreign('financial_condition_id')->references('id')->on('financial_conditions');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('group_id')->references('id')->on('contact_groups');

        });
    }

    public function down()
    {
        Schema::dropIfExists('contacts');
    }
};
