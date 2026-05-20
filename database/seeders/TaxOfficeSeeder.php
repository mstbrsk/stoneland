<?php

namespace Database\Seeders;

use App\Models\TaxOffice;
use Illuminate\Database\Seeder;

class TaxOfficeSeeder extends Seeder
{
    public function run()
    {
        if (file_exists(public_path('vergi_daireleri.json'))) {
            TaxOffice::truncate();

            $list = json_decode(file_get_contents(public_path('vergi_daireleri.json')), true);

            foreach ($list as $item) {
                TaxOffice::create([
                    'name' => $item['vergi_dairesi'],
                    'city_id' => $item['plaka'],
                    'county' => $item['ilce'],
                    'code' => $item['muhasebe_birimi_kodu'],
                ]);
            }
        }
    }
}
