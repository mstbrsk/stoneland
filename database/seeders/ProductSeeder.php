<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::truncate();

        Product::insert([
            [
                "id" => "9c423cac-cc1b-4fdf-8034-0903d0f68e08",
                "name" => "Varyantlı Ürün 1",
                "stock_code" => "vu1",
                "sales_price" => null,
                "cost" => null,
                "tax_rate" => null,
                "unit_id" => 1,
                "photo" => null,
                "product_attributes" => '[{"attribute_id":"bbaa3b76-a1f0-4b1e-9a07-94a552a4fbb6","values":["3a280a6b-73c6-4124-b1da-0c5ed89b1802","1eb3eca4-80f3-44cb-af12-a0f50e5d30d9","7f06eba3-2b33-4823-b639-15742f7111f0"]}]',
                "can_purchase" => null,
                "can_sale" => null,
                "allow_negative_stock" => null,
                "warehouse_id" => 1,
                "created_by" => "9c34b75a-54b6-4299-8bdb-f37cd04ce247",
                "updated_by" => "9c34b75a-54b6-4299-8bdb-f37cd04ce247",
                "created_at" => "2024-06-11 05:16:37",
                "updated_at" => "2024-06-11 05:16:37"
            ]
        ]);

        ProductVariant::truncate();

        ProductVariant::insert([
            [
                "id" => "9c423cac-cdc2-4042-b727-763883bd2a40",
                "stock_code" => null,
                "product_name" => "Varyantlı Ürün 1",
                "product_id" => "9c423cac-cc1b-4fdf-8034-0903d0f68e08",
                "attribute_items" => '["3a280a6b-73c6-4124-b1da-0c5ed89b1802"]',
                "stock" => 0,
                "created_by" => "9c34b75a-54b6-4299-8bdb-f37cd04ce247",
                "updated_by" => "9c34b75a-54b6-4299-8bdb-f37cd04ce247",
                "created_at" => "2024-06-11 05:16:37",
                "updated_at" => "2024-06-11 05:16:37"
            ],
            [
                "id" => "9c423cac-ceab-4496-8dcb-2408e309f4d7",
                "stock_code" => null,
                "product_name" => "Varyantlı Ürün 1",
                "product_id" => "9c423cac-cc1b-4fdf-8034-0903d0f68e08",
                "attribute_items" => '["1eb3eca4-80f3-44cb-af12-a0f50e5d30d9"]',
                "stock" => 0,
                "created_by" => "9c34b75a-54b6-4299-8bdb-f37cd04ce247",
                "updated_by" => "9c34b75a-54b6-4299-8bdb-f37cd04ce247",
                "created_at" => "2024-06-11 05:16:37",
                "updated_at" => "2024-06-11 05:16:37"
            ],
            [
                "id" => "9c423cac-cf66-4c15-a957-444a1de03bea",
                "stock_code" => null,
                "product_name" => "Varyantlı Ürün 1",
                "product_id" => "9c423cac-cc1b-4fdf-8034-0903d0f68e08",
                "attribute_items" => '["7f06eba3-2b33-4823-b639-15742f7111f0"]',
                "stock" => 0,
                "created_by" => "9c34b75a-54b6-4299-8bdb-f37cd04ce247",
                "updated_by" => "9c34b75a-54b6-4299-8bdb-f37cd04ce247",
                "created_at" => "2024-06-11 05:16:37",
                "updated_at" => "2024-06-11 05:16:37"
            ]
        ]);

    }
}
