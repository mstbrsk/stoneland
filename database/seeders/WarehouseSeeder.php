<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::truncate();

        $items = [
            [
                'name' => 'Berka Depo',
                'short_name' => '',
                'address_id' => null,
                'created_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'updated_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'created_at' => '2024-06-04 12:03:36',
                'updated_at' => '2024-06-04 12:03:36'
            ],
            [
                'name' => 'Teklif Depo',
                'short_name' => 'TD',
                'address_id' => null,
                'created_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'updated_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'created_at' => '2024-06-04 12:03:45',
                'updated_at' => '2024-06-04 12:03:45'
            ]
        ];

        foreach ($items as $item) {
            //DB::select("SELECT setval(pg_get_serial_sequence('warehouses', 'id'), max(id)) FROM warehouses;");
            Warehouse::create($item);
        }
    }
}
