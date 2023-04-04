<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use App\Models\Month;
use Carbon\Carbon;

class MonthlyLaborReportController extends Controller
{
    public function MonthlyLaborReport(Request $request) {

      $registrationType = $request->registration_type;
      $forTheMonth = $request->for_the_month;
      $forTheYear = $request->for_the_year;
      $last_date = date("t", strtotime("$forTheYear-$forTheMonth"));

      $picker_date = $forTheYear . '-' . $forTheMonth . '-' . $last_date;
      $document_format = 'html';

      $monthly_data = "SELECT
            company_account_no,
            org_name AS company_name,
            b.address AS company_address,
            phone_no AS contact_no,
            (SELECT COUNT(pf_employee_id) FROM pfemployeeregistrations WHERE pf_employee_company_id = b.company_id) AS no_of_employees,
            (SELECT TOP(1) pf_collection_date FROM pfcollections WHERE pf_collection_company_account_no_id = b.company_id
            ORDER BY id DESC) AS last_payment_date,
            (SELECT TOP(1) pf_collection_amount FROM pfcollections WHERE pf_collection_company_account_no_id = b.company_id
            ORDER BY id DESC) AS last_payment_amount,
            DATEDIFF(DAY, (SELECT TOP(1) pf_collection_date FROM pfcollections WHERE pf_collection_company_account_no_id = b.company_id
            ORDER BY id DESC),CURRENT_TIMESTAMP) AS od_days
            FROM companyregistrations b ";

      $condition = " WHERE b.registration_type = '$registrationType' AND b.closing_date IS NULL 
      and b.effective_start_date <= '$picker_date'";

      $select_query = $monthly_data . '' . $condition;
      $monthly_data_sql = DB::select($select_query);

      if ($monthly_data) {

          $month = Month::where('id','=',$forTheMonth)->get()->first()->month_name;
          $bladeView = view('reports.monthlylabourstatement', compact('monthly_data_sql','registrationType','month', 'forTheYear','forTheMonth'));
          $fileName = 'monthly_labor_report_' . '_' . Carbon::now()->format('YmdHis'); // $genRandomExtension

          if(Storage::disk('reports')->put($fileName.'.html', $bladeView)){
                  
                  DB::table('documents')->insert([
                  'doc_type_id' => 4506900,
                  'doc_ref_no' => Carbon::now()->format('YmdHis') . random_int(1111, 9999),
                  'doc_ref_type' => 'MonthlyLabourStatement',
                  'doc_type' => 'html',
                  'doc_path' => $fileName.'.html',
                  'doc_date' => Carbon::now()->format('Y-m-d'),
                  'registration_type' => $request->registration_type,
                  'doc_user_id' => auth('api')->user()->id
              ]);

                  return response()->json(['success','message'=>'Monthly Labour Report Generated Successfully']);
              }else{
                  return 'error';
              }

      } else {

        return response()->json(['error','message'=>'Unable to generate the Monthly Labour Report']);
      }
    }
}
