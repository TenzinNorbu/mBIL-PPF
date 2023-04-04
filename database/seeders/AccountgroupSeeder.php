<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountgroupSeeder extends Seeder
{
    public function run()
    {
        $array = ([
            [
                'account_group_id' => '9F1D02A0-421A-11EC-92A6-BB4CCAAF9B33',
                'account_group_code' => '0001',
                'account_group_name' => 'PPF/GF Account',
                'branch_wise' => 'Yes',
            ],
            [
                'account_group_id' => 'A7621450-421A-11EC-858F-9BE7FA733BC4',
                'account_group_code' => '0002',
                'account_group_name' => 'Bank Account',
                'branch_wise' => 'Yes',
            ],
            [
                'account_group_id' => 'B0B731C0-421A-11EC-B589-354B57453CBA',
                'account_group_code' => '0003',
                'account_group_name' => 'Cash Account',
                'branch_wise' => 'Yes',
            ],
            [
                'account_group_id' => '5A3C8E80-45DE-11EC-A079-D9F2813EF00D',
                'account_group_code' => '0004',
                'account_group_name' => 'Interest Paid A/c',
                'branch_wise' => 'Yes',
            ],
            [
                'account_group_id' => '785E1CF0-45DE-11EC-A4C9-0D3C9D51C511',
                'account_group_code' => '0005',
                'account_group_name' => 'Interest Payable A/c',
                'branch_wise' => 'Yes',
            ],
            [
                'account_group_id' => '87AEC410-45DE-11EC-8103-D1EB6ECDC7A5',
                'account_group_code' => '0006',
                'account_group_name' => 'Profit and Loss A/c',
                'branch_wise' => 'Yes',
            ],
            [
                'account_group_id' => '987A9B20-45DE-11EC-973C-47DC726D5DD3',
                'account_group_code' => '0007',
                'account_group_name' => 'Refund Payable A/c',
                'branch_wise' => 'Yes',
            ],
        ]);

        foreach ($array as $key => $rows) {
            DB::table('accountgroups')->insert($rows);
        }
    }
}
