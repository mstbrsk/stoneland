<?php

namespace Database\Seeders;

use App\Enums\CompanyType;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Contact::truncate();

        Contact::create(
            array(
                'id' => '9c34b84f-3c55-462d-b669-486e6feb2a91',
                'code' => '120.00001',
                'name' => 'Test Carisi',
                'user_id' => null,
                'company_type' => CompanyType::COMPANY,
                'address' => 'Fatura Adresi',
                'second_address' => 'Sevkiyat adresi',
                'district' => '107261',
                'city_id' => 4377,
                'country' => 228,
                'tax_administration' => '492',
                'tax_number' => '31322',
                'phone' => '0212222',
                'accounting_phone' => '22222',
                'mobile' => '222',
                'email' => 'ss@ss.com',
                'website' => '',
                'language' => '',
                'tickets' => null,
                'photo' => '',
                'is_supplier' => true,
                'payment_condition_id' => null,
                'exchange_id' => null,
                'price_list_id' => null,
                'shipping_type_id' => null,
                'pos_campaign_id' => null,
                'financial_condition_id' => null,
                'currency_id' => null,
                'created_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'updated_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'created_at' => '2024-06-04 12:00:44',
                'updated_at' => '2024-06-04 12:00:44'
        ));
    }
}
