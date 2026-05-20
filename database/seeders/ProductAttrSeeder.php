<?php

namespace Database\Seeders;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductAttrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductAttribute::truncate();

        $attr = ProductAttribute::create(
            array(
                'id' => 'bbaa3b76-a1f0-4b1e-9a07-94a552a4fbb6',
                'name' => 'Renk',
                'values' => [['attribute' => 'Mavi'], ['attribute' => 'Yeşil'], ['attribute' => 'Beyaz']],
                'created_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'updated_by' => '9c34b75a-54b6-4299-8bdb-f37cd04ce247',
                'created_at' => '2024-06-04 12:05:17',
                'updated_at' => '2024-06-04 12:05:17'
            )
        );

        ProductAttributeItem::truncate();

        $items = array(
            array(
                'id' => '3a280a6b-73c6-4124-b1da-0c5ed89b1802',
                'product_attribute_id' => $attr->id,
                'value' => 'Mavi',
                'created_at' => '2024-06-04 12:05:17',
                'updated_at' => '2024-06-04 12:05:17'
            ),
            array(
                'id' => '1eb3eca4-80f3-44cb-af12-a0f50e5d30d9',
                'product_attribute_id' => $attr->id,
                'value' => 'Yeşil',
                'created_at' => '2024-06-04 12:05:17',
                'updated_at' => '2024-06-04 12:05:17'
            ),
            array(
                'id' => '7f06eba3-2b33-4823-b639-15742f7111f0',
                'product_attribute_id' => $attr->id,
                'value' => 'Beyaz',
                'created_at' => '2024-06-04 12:05:17',
                'updated_at' => '2024-06-04 12:05:17'
            )
        );

        foreach ($items as $item) {
            ProductAttributeItem::create($item);
        }
    }
}
