<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterIdHolderSeeder extends Seeder
{
    public function run()
    {
        DB::table('masteridholders')->insert([
           /*
            * PF Company Registrations
            */
            ['id_type' => 'Company_Registration', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '2', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '3', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '4', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '5', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '6', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '7', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '8', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '9', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '10', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '11', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '12', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '13', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '14', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '15', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '16', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '17', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '18', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '19', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '20', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '21', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Company_Registration', 'branch_id' => '22', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],

           /*
            * PF Account Transactions
            */

            ['id_type' => 'Account_Transaction', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '2', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '3', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '4', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '5', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '6', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '7', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '8', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '9', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '10', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '11', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '12', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '13', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '14', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '15', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '16', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '17', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '18', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '19', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '20', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '21', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'Account_Transaction', 'branch_id' => '22', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],

            /*
             * PF Collection
             */
            ['id_type' => 'RV_Transaction', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '2', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '3', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '4', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '5', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '6', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '7', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '8', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '9', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '10', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '11', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '12', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '13', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '14', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '15', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '16', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '17', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '18', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '19', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '20', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '21', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'RV_Transaction', 'branch_id' => '22', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],

            /*
             * PF Payment Voucher
             */

            ['id_type' => 'PV_Transaction', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '2', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '3', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '4', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '5', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '6', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '7', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '8', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '9', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '10', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '11', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '12', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '13', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '14', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '15', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '16', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '17', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '18', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '19', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '20', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '21', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],
            ['id_type' => 'PV_Transaction', 'branch_id' => '22', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],

            /*
             * PF Closing
             */
            ['id_type' => 'Sys_Transaction', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>'PF', 'serial_no' => 0],



            /*
             * GF Company Registrations
             */

            ['id_type' => 'GF_Company_Registration', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '2', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '3', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '4', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '5', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '6', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '7', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '8', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '9', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '10', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '11', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '12', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '13', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '14', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '15', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '16', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '17', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '18', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '19', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '20', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '21', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Company_Registration', 'branch_id' => '22', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],

            /*
             * GF Account Transactions
             */

            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '2', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '3', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '4', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '5', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '6', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '7', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '8', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '9', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '10', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '11', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '12', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '13', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '14', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '15', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '16', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '17', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '18', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '19', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '20', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '21', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_Account_Transaction', 'branch_id' => '22', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],

           /*
            * GF Collections
            */
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '2', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '3', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '4', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '5', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '6', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '7', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '8', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '9', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '10', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '11', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '12', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '13', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '14', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '15', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '16', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '17', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '18', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '19', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '20', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '21', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_RV_Transaction', 'branch_id' => '22', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],

            /*
             * GF Payment Voucher
             */

            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '2', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '3', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '4', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '5', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '6', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '7', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '8', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '9', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '10', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '11', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '12', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '13', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '14', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '15', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '16', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '17', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '18', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '19', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '20', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '21', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],
            ['id_type' => 'GF_PV_Transaction', 'branch_id' => '22', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],

            /*
             * GF Closing
             */
            ['id_type' => 'GF_Sys_Transaction', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>'GF', 'serial_no' => 0],

            /*
             * Account Posting
             */
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '1', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '2', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '3', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '4', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '5', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '6', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '7', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '8', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '9', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '10', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '11', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '12', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '13', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '14', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '15', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '16', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '17', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '18', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '19', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '20', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '21', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
            ['id_type' => 'Account_Posting_Transaction', 'branch_id' => '22', 'f_year' => '2022', 'registration_type'=>NULL, 'serial_no' => 0],
        ]);

    }
}
