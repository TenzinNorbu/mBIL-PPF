<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DzongkhagSeeder extends Seeder
{
    public function run()
    {
        $array = ([
            ['dzongkhag_id' => '1', 'dzongkhag_name' => 'Bumthang'],
            ['dzongkhag_id' => '2', 'dzongkhag_name' => 'Chhukha'],
            ['dzongkhag_id' => '3', 'dzongkhag_name' => 'Dagana'],
            ['dzongkhag_id' => '4', 'dzongkhag_name' => 'Gasa'],
            ['dzongkhag_id' => '5', 'dzongkhag_name' => 'Haa'],
            ['dzongkhag_id' => '6', 'dzongkhag_name' => 'Lhuentse'],
            ['dzongkhag_id' => '7', 'dzongkhag_name' => 'Mongar'],
            ['dzongkhag_id' => '8', 'dzongkhag_name' => 'Paro'],
            ['dzongkhag_id' => '9', 'dzongkhag_name' => 'Pema Gatshel'],
            ['dzongkhag_id' => '10', 'dzongkhag_name' => 'Punakha'],
            ['dzongkhag_id' => '11', 'dzongkhag_name' => 'Samdrup Jongkhar'],
            ['dzongkhag_id' => '12', 'dzongkhag_name' => 'Samtse'],
            ['dzongkhag_id' => '13', 'dzongkhag_name' => 'Sarpang'],
            ['dzongkhag_id' => '14', 'dzongkhag_name' => 'Thimphu'],
            ['dzongkhag_id' => '15', 'dzongkhag_name' => 'Trashigang'],
            ['dzongkhag_id' => '16', 'dzongkhag_name' => 'Trashi Yangtse'],
            ['dzongkhag_id' => '17', 'dzongkhag_name' => 'Trongsa'],
            ['dzongkhag_id' => '18', 'dzongkhag_name' => 'Tsirang'],
            ['dzongkhag_id' => '19', 'dzongkhag_name' => 'Wangdue Phodrang'],
            ['dzongkhag_id' => '20', 'dzongkhag_name' => 'Zhemgang']

        ]);

        foreach ($array as $key => $rows) {
            DB::table('dzongkhags')->insert($rows);
        }

    }
}
