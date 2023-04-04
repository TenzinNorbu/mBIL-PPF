<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartytypeSeeder extends Seeder
{

    public function run()
    {
        $array = ([
            ['party_type_id' => '1', 'party_type_code' => 'Employee', 'descriptions' => 'Employee'],
            ['party_type_id' => '2', 'party_type_code' => 'Customers', 'descriptions' => 'Customers'],
            ['party_type_id' => '3', 'party_type_code' => 'Investors', 'descriptions' => 'Investors'],
            ['party_type_id' => '4', 'party_type_code' => 'Suppliers and Vendors', 'descriptions' => 'Suppliers and Vendors'],
            ['party_type_id' => '5', 'party_type_code' => 'Regulators', 'descriptions' => 'Regulators'],
            ['party_type_id' => '6', 'party_type_code' => 'Governments', 'descriptions' => 'Governments'],
        ]);

        foreach ($array as $key => $rows) {
            DB::table('partytypes')->insert($rows);
        }

    }
}
