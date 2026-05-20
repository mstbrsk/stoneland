<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::truncate();

        Currency::insert(array(
            array(
                'id' => '7070ddb5-6340-408d-ab37-fc3d7b756100',
                'name' => 'TL',
                'code' => 'TRY',
                'symbol_left' => '',
                'symbol_right' => '₺',
                'value' => 0.00,
                'created_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'updated_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'created_at' => '2024-06-04 12:02:21',
                'updated_at' => '2024-06-04 12:02:21'
            ),
            array(
                'id' => '48628818-b4f4-4360-abe5-59f86bae3567',
                'name' => 'Amerikan Doları',
                'code' => 'USD',
                'symbol_left' => '$',
                'symbol_right' => '',
                'value' => 32.2114,
                'created_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'updated_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'created_at' => '2024-06-04 12:02:32',
                'updated_at' => '2024-06-04 12:02:34'
            ),
            array(
                'id' => '92e6b335-b8d9-4936-9990-01e07dd85168',
                'name' => 'Euro',
                'code' => 'EUR',
                'symbol_left' => '€',
                'symbol_right' => '',
                'value' => 34.9235,
                'created_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'updated_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'created_at' => '2024-06-04 12:02:42',
                'updated_at' => '2024-06-04 12:02:43'
            )
        ));
    }
}
