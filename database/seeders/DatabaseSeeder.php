<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CountrySeeder::class,
            //LanguageSeeder::class,
            LocationSeeder::class,
            TaxOfficeSeeder::class,
            CurrencySeeder::class,

            ContactSeeder::class,
            WarehouseSeeder::class,
            UnitSeeder::class,
            ProductAttrSeeder::class,
            ProductSeeder::class,
        ]);

        /* User::factory(50)->create();*/
    }
}
