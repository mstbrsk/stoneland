<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $turkey_id = DB::table('countries')->where('code', 'TR')->first()->id;

        $cities = [

            ['country_id' => $turkey_id, 'name' => 'ADANA' ],
            ['country_id' => $turkey_id,'name' => 'ADIYAMAN' ],
            ['country_id' => $turkey_id, 'name' => 'AFYONKARAHİSAR' ],
            ['country_id' => $turkey_id, 'name' => 'AĞRI' ],
            ['country_id' => $turkey_id, 'name' => 'AKSARAY'],
            ['country_id' => $turkey_id, 'name' => 'AMASYA'],
            ['country_id' => $turkey_id,'name' => 'ANKARA'],
            ['country_id' => $turkey_id, 'name' => 'ANTALYA' ],
            ['country_id' => $turkey_id, 'name' => 'ARDAHAN' ],
            ['country_id' => $turkey_id, 'name' => 'ARTVİN'],
            ['country_id' => $turkey_id, 'name' => 'AYDIN' ],
            ['country_id' => $turkey_id, 'name' => 'BALIKESİR' ],
            ['country_id' => $turkey_id, 'name' => 'BARTIN'],
            ['country_id' => $turkey_id, 'name' => 'BATMAN'],
            ['country_id' => $turkey_id, 'name' => 'BAYBURT'],
            ['country_id' => $turkey_id, 'name' => 'BİLECİK'],
            ['country_id' => $turkey_id, 'name' => 'BİNGÖL'],
            ['country_id' => $turkey_id, 'name' => 'BİTLİS'],
            ['country_id' => $turkey_id, 'name' => 'BOLU'],
            ['country_id' => $turkey_id, 'name' => 'BURDUR'],
            ['country_id' => $turkey_id, 'name' => 'BURSA'],
            ['country_id' => $turkey_id, 'name' => 'ÇANAKKALE'],
            ['country_id' => $turkey_id, 'name' => 'ÇANKIRI'],
            ['country_id' => $turkey_id, 'name' => 'ÇORUM'],
            ['country_id' => $turkey_id, 'name' => 'DENİZLİ'],
            ['country_id' => $turkey_id, 'name' => 'DİYARBAKIR'],
            ['country_id' => $turkey_id, 'name' => 'DÜZCE'],
            ['country_id' => $turkey_id, 'name' => 'EDİRNE'],
            ['country_id' => $turkey_id,'name' => 'ELAZIĞ'],
            ['country_id' => $turkey_id, 'name' => 'ERZİNCAN'],
            ['country_id' => $turkey_id, 'name' => 'ERZURUM'],
            ['country_id' => $turkey_id,'name' => 'ESKİŞEHİR'],
            ['country_id' => $turkey_id, 'name' => 'GAZİANTEP'],
            ['country_id' => $turkey_id, 'name' => 'GİRESUN'],
            ['country_id' => $turkey_id, 'name' => 'GÜMÜŞHANE'],
            ['country_id' => $turkey_id, 'name' => 'HAKKARİ'],
            ['country_id' => $turkey_id, 'name' => 'HATAY'],
            ['country_id' => $turkey_id, 'name' => 'IĞDIR'],
            ['country_id' => $turkey_id, 'name' => 'ISPARTA'],
            ['country_id' => $turkey_id, 'name' => 'İSTANBUL'],
            ['country_id' => $turkey_id, 'name' => 'İZMİR'],
            ['country_id' => $turkey_id, 'name' => 'KAHRAMANMARAŞ'],
            ['country_id' => $turkey_id, 'name' => 'KARABÜK'],
            ['country_id' => $turkey_id, 'name' => 'KARAMAN'],
            ['country_id' => $turkey_id, 'name' => 'KARS'],
            ['country_id' => $turkey_id, 'name' => 'KASTAMONU'],
            ['country_id' => $turkey_id, 'name' => 'KAYSERİ'],
            ['country_id' => $turkey_id, 'name' => 'KIRIKKALE'],
            ['country_id' => $turkey_id, 'name' => 'KIRKLARELİ'],
            ['country_id' => $turkey_id, 'name' => 'KIRŞEHİR'],
            ['country_id' => $turkey_id, 'name' => 'KİLİS'],
            ['country_id' => $turkey_id,'name' => 'KOCAELİ'],
            ['country_id' => $turkey_id, 'name' => 'KONYA'],
            ['country_id' => $turkey_id, 'name' => 'KÜTAHYA'],
            ['country_id' => $turkey_id, 'name' => 'MALATYA'],
            ['country_id' => $turkey_id, 'name' => 'MANİSA'],
            ['country_id' => $turkey_id, 'name' => 'MARDİN'],
            ['country_id' => $turkey_id, 'name' => 'MERSİN'],
            ['country_id' => $turkey_id, 'name' => 'MUĞLA'],
            ['country_id' => $turkey_id, 'name' => 'MUŞ'],
            ['country_id' => $turkey_id, 'name' => 'NEVŞEHİR'],
            ['country_id' => $turkey_id, 'name' => 'NİĞDE'],
            ['country_id' => $turkey_id, 'name' => 'ORDU'],
            ['country_id' => $turkey_id, 'name' => 'OSMANİYE'],
            ['country_id' => $turkey_id, 'name' => 'RİZE'],
            ['country_id' => $turkey_id, 'name' => 'SAKARYA'],
            ['country_id' => $turkey_id, 'name' => 'SAMSUN'],
            ['country_id' => $turkey_id, 'name' => 'SİİRT'],
            ['country_id' => $turkey_id, 'name' => 'SİNOP'],
            ['country_id' => $turkey_id, 'name' => 'SİVAS'],
            ['country_id' => $turkey_id, 'name' => 'ŞANLIURFA'],
            ['country_id' => $turkey_id, 'name' => 'ŞIRNAK'],
            ['country_id' => $turkey_id, 'name' => 'TEKİRDAĞ'],
            ['country_id' => $turkey_id, 'name' => 'TOKAT'],
            ['country_id' => $turkey_id,'name' => 'TRABZON'],
            ['country_id' => $turkey_id, 'name' => 'TUNCELİ'],
            ['country_id' => $turkey_id, 'name' => 'UŞAK'],
            ['country_id' => $turkey_id, 'name' => 'VAN'],
            ['country_id' => $turkey_id, 'name' => 'YALOVA'],
            ['country_id' => $turkey_id, 'name' => 'YOZGAT'],
            ['country_id' => $turkey_id,'name' => 'ZONGULDAK'],



        ];

        DB::table('cities')->insert($cities);
    }
}
