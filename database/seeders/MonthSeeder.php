<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthSeeder extends Seeder
{

    public function run()
    {
        $array = ([
            ['month_name' => 'January'],
            ['month_name' => 'February'],
            ['month_name' => 'March'],
            ['month_name' => 'April'],
            ['month_name' => 'May'],
            ['month_name' => 'June'],
            ['month_name' => 'July'],
            ['month_name' => 'August'],
            ['month_name' => 'September'],
            ['month_name' => 'October'],
            ['month_name' => 'November'],
            ['month_name' => 'December'],
        ]);

        foreach ($array as $key => $rows) {
            DB::table('months')->insert($rows);
        }
    }
}
