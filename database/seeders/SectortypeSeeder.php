<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectortypeSeeder extends Seeder
{
    public function run()
    {
        $array = ([
            ['sector_id'=>1,'sector_name' => 'Crop Cultivation'],
            ['sector_id'=>2,'sector_name' => 'Fishery and Aquaculture'],
            ['sector_id'=>3,'sector_name' => 'Poiltry Framing'],
            ['sector_id'=>4,'sector_name' => 'Piggery'],
            ['sector_id'=>5,'sector_name' => 'Raising of cattle'],
            ['sector_id'=>6,'sector_name' => 'Raising of Other Animals'],
            ['sector_id'=>7,'sector_name' => 'Mixed Farming'],
            ['sector_id'=>8,'sector_name' => 'Agricultur Machineries'],
            ['sector_id'=>9,'sector_name' => 'Agri-Infrastructures and Support'],
            [ 'sector_id'=>10,'sector_name' => 'Vehicles, Machineries, and Equipment'],
            [ 'sector_id'=>11,'sector_name' => 'Garments, Textiles and Jewelries'],
            ['sector_id'=>12,'sector_name' => 'Groceries and Other Related Commodites'],
            ['sector_id'=>13,'sector_name' => 'Arts and Crafts'],
            ['sector_id'=>14,'sector_name' => 'Medicines, Drugs and Cosmetics'],
            ['sector_id'=>15,'sector_name' => 'Electronics, Home and Office Furnishing'],
            ['sector_id'=>16,'sector_name' => 'Stationaries'],
            ['sector_id'=>17,'sector_name' => 'Hardware and Construction Materials'],
            ['sector_id'=>18,'sector_name' => 'Sports and Toy Products'],
            ['sector_id'=>19,'sector_name' => 'Renewable Energy'],
            ['sector_id'=>20,'sector_name' => 'Non-Renewable Energy'],

            ['sector_id'=>21,'sector_name' => 'Printing and Production of Recorded Media'],
            ['sector_id'=>22,'sector_name' => 'Manufacturing of Chemical and Petroleum Products'],
            ['sector_id'=>23,'sector_name' => 'Travel and Ticketing Agent'],
            ['sector_id'=>24,'sector_name' => 'Restaurant and Bar'],
            ['sector_id'=>25,'sector_name' => 'Homestay and Guest House'],
            ['sector_id'=>26,'sector_name' => 'Hotels & Tourism'],
            ['sector_id'=>27,'sector_name' => 'Groceries and Other Related Commodites'],
            ['sector_id'=>28,'sector_name' => 'Garments, Textiles and Jewelries'],
            ['sector_id'=>29,'sector_name' => 'Vehicles, Machineries, and Equipment'],
            ['sector_id'=>30,'sector_name' => 'Electronics, Home & Office Furnishing'],
            ['sector_id'=>31,'sector_name' => 'Arts and Crafts'],
            ['sector_id'=>32,'sector_name' => 'Medicines, Drugs and Cosmetics'],
            ['sector_id'=>33,'sector_name' => 'Stationaries'],
            ['sector_id'=>34,'sector_name' => 'Hardware and Construction materials'],
            ['sector_id'=>35,'sector_name' => 'Sports and Toy Products'],
            ['sector_id'=>36,'sector_name' => 'Petroleum Products Distributor'],
            ['sector_id'=>37,'sector_name' => 'Export Business'],
            ['sector_id'=>38,'sector_name' => 'Financial Institutions'],
            ['sector_id'=>39,'sector_name' => 'Insurance Companies'],
            ['sector_id'=>40,'sector_name' => 'Pension Companies'],
            ['sector_id'=>41,'sector_name' => 'Micro Finance Institutions'],
            ['sector_id'=>42,'sector_name' => 'Other FSPs'],
            ['sector_id'=>43,'sector_name' => 'Real Estate'],

            ['sector_id'=>44,'sector_name' => 'Transport Carrier'],
            ['sector_id'=>45,'sector_name' => 'Public Transport'],
            ['sector_id'=>46,'sector_name' => 'Tourist Transport'],
            ['sector_id'=>47,'sector_name' => 'Taxi Associations'],
            ['sector_id'=>48,'sector_name' => 'Heavy Machineries Dealers'],
            ['sector_id'=>49,'sector_name' => 'Education Consultancy (In-Country)'],
            ['sector_id'=>50,'sector_name' => 'Education Consultancy (Out-Country)'],
            ['sector_id'=>51,'sector_name' => 'Loan Against Fixed Deposits'],
            ['sector_id'=>52,'sector_name' => 'Loan Against Recurring Deposits'],
            ['sector_id'=>53,'sector_name' => 'Government Agencies'],
            ['sector_id'=>54,'sector_name' => 'Non-Construction Based Contract Services'],
            ['sector_id'=>55,'sector_name' => 'Construction Based Contract Services'],
            ['sector_id'=>56,'sector_name' => 'Business Operation for the Contractor'],
            ['sector_id'=>57,'sector_name' => 'Silviculture and Logging'],
            ['sector_id'=>58,'sector_name' => 'Gathering of Non-wood Forest Products'],
            ['sector_id'=>59,'sector_name' => 'Forestry Machineries'],
            ['sector_id'=>60,'sector_name' => 'Forestry Infrastructures and Supports'],
            ['sector_id'=>61,'sector_name' => 'Mining of Chemical and Minerals'],
            ['sector_id'=>62,'sector_name' => 'Quarrying'],

            ['sector_id'=>63,'sector_name' => 'Information Communication and Technology Services'],
            ['sector_id'=>64,'sector_name' => 'Airline Services'],
            ['sector_id'=>65,'sector_name' => 'Consultancy Services'],
            ['sector_id'=>66,'sector_name' => 'Entertainments and Recreational Services'],
            ['sector_id'=>67,'sector_name' => 'Institutional And Educational Services'],
            ['sector_id'=>68,'sector_name' => 'Health and Fitness Services'],
            ['sector_id'=>69,'sector_name' => 'Repair and Maintenance Services'],
            ['sector_id'=>70,'sector_name' => 'Others Services'],
            ['sector_id'=>71,'sector_name' => 'Personal Credit Card'],
            ['sector_id'=>72,'sector_name' => 'Corporate Credit Cards'],
            ['sector_id'=>73,'sector_name' => 'In-Country Treatments'],
            ['sector_id'=>74,'sector_name' => 'Ex-Country Treatment']
        ]);

        foreach ($array as $key => $rows) {
            DB::table('sector_type')->insert($rows);
        }

    }
}
