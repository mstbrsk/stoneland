<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\District;
use Illuminate\Database\Seeder;
use Storage;

class LocationSeeder extends Seeder
{

    public function run()
    {
        ini_set('memory_limit', '200M');

        if (file_exists(public_path('countries+states+cities.json'))) {
            District::truncate();
            City::truncate();
            Country::truncate();

            $list = json_decode(file_get_contents(public_path('countries+states+cities.json')), true);

            foreach ($list as $item) {
                $country = Country::create([
                    'name' => $item['name'],
                    'code' => $item['iso2'],
                ]);

                $states = $item['states'];

                foreach ($states as $state) {
                    $city = City::create([
                        'country_id' => $country->id,
                        'name' => $state['name'],
                        'latitude' => $state['latitude'],
                        'longitude' => $state['longitude'],
                    ]);

                    if ($country->id === 228) {
                        $districts = $state['cities'];

                        foreach ($districts as $district) {
                            District::create([
                                'city_id' => $city->id,
                                'id' => $district['id'],
                                'name' => $district['name'],
                                'latitude' => $district['latitude'],
                                'longitude' => $district['longitude'],
                            ]);
                        }
                    }
                }
            }
        }
    }
}
