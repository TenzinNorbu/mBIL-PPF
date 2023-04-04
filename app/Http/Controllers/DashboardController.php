<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Companyregistration;
use App\Models\Pfemployeeregistration;
use Carbon\Carbon;
use DB;
use App\Models\User;

class DashboardController extends Controller
{
    public function PFCompanyCount(){
        try{
            $PFCount=Companyregistration::where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)->get()->count();
            return $PFCount;
        }catch (\Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function GFCompanyCount(){
        try{
            return Companyregistration::where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)->get()->count(); 
        }catch (\Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function PFIndividualCount(){
    try{
        return Pfemployeeregistration::where('registration_type', '=', 'PF')
        ->where('status', '=', 'Active')
        ->where('effective_end_date', '=', NULL)->get()->count();
    }catch (\Exception $e){
        return $this->errorResponse('Page not found');
    }
    }
    public function GFIndividualCount(){
    try{
        return Pfemployeeregistration::where('registration_type', '=', 'GF')
        ->where('status', '=', 'Active')
        ->where('effective_end_date', '=', NULL)->get()->count();
    }catch (\Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function TotalCollectionAmmount(){
        try{
            $collectionAmount = collect(DB::select("SELECT SUM(CAST(pf_collection_amount as int)) as total_collections_amount
        FROM pfcollections WHERE pf_collection_status = 'under_process'
        OR pf_collection_status = 'Approved' OR pf_collection_effective_end_date = NULL;"))->first();

        if ($collectionAmount == NULL) {
             $collectionAmount = 0;
        return $collectionAmount;
        } else {
        return $collectionAmount;
        }
        }catch (\Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function MonthlyPFContributions(){
        try{
        $get_total_cont = 0;
        $currentYear =Carbon::now()->format('Y');
        $monthly_cont_lists = collect(DB::select("SELECT
            for_the_month,for_the_year,
            SUM(CAST(employee_contribution as float) + CAST(employer_contribution as float)) as total_contributions
            FROM pfstatements
            WHERE registration_type = 'PF' AND for_the_year = '$currentYear'
            GROUP BY for_the_month,for_the_year"));
    
        if (($monthly_cont_lists)->count() === 0){
            $monthly_cont_lists = 0;
            return $monthly_cont_lists;
        } else {
            foreach ($monthly_cont_lists as $pf_data) {
                $get_total_cont += $pf_data->total_contributions;
                $for_the_month = $pf_data->for_the_month;
                $for_the_year = $pf_data->for_the_year;
            }
            return response()->json([
                'for_the_month' => $for_the_month,
                'for_the_year' => $for_the_year,
                'total_collections' => $get_total_cont
            ]);
        }
    }catch (\Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function MonthlyGFContributions(){
        try{
            $currentYear =Carbon::now()->format('Y');
            $monthly_cont_lists = collect(DB::select("SELECT
                for_the_month,for_the_year,
                SUM(CAST(employee_contribution as float) + CAST(employer_contribution as float)) as total_contributions
                FROM pfstatements
                WHERE registration_type = 'GF' AND for_the_year = '$currentYear'
                GROUP BY for_the_month,for_the_year,
                total_employee_contribution,total_employer_contribution"))->first();
        
            if ($monthly_cont_lists === NULL) {
                $monthly_cont_lists = 0;
            return $monthly_cont_lists;
            } else {
                return $monthly_cont_lists;
            }
        }catch (\Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function Days_40($user_id){
        try{
            $users_notify =User::whereRaw('datediff(Day,password_created_date,getdate())=2')
                              ->where('id','=', $user_id)->first();
                         
            if(!empty($users_notify)){
                return response()->json([
                        'status' => 'success',
                        'message'=> "Your password will expire on ($users_notify->password_reset_date). System will lock your account if you have not change your password.",
            ]);
           }else{
            return response()->json([
                'error' => 'Page not found',
            ]);
          }
        }catch(Exception $e){
                return $this->errorResponse('Page not found');
        }
    }
}
