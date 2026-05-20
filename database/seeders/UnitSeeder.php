<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Unit::truncate();

        Unit::insert(array(
            array(
                'id' => 1,
                'name' => 'Adet',
                'created_at' => '2024-06-04 12:04:31',
                'updated_at' => '2024-06-04 12:04:31',
                'created_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'updated_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247'
            ),
            array(
                'id' => 2,
                'name' => 'KG',
                'created_at' => '2024-06-04 12:04:34',
                'updated_at' => '2024-06-04 12:04:34',
                'created_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'updated_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247'
            )
        ));
    }
}
