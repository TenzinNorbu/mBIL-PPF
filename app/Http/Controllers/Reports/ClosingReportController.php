<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use App\Models\Month;
use Carbon\Carbon;

class ClosingReportController extends Controller
{
    public function ClosingReport(Request $request) {

      $registrationType = $request->registration_type;
      $fromDate = $request->from_date;
      $toDate = $request->to_date;

      $closing_sql_data = "SELECT
                company_account_no AS account_no,
                org_name AS company_name,
                SUM(a.employee_contribution) as employee_contribution,
                SUM(a.employer_contribution) as employer_contribution,
                SUM(a.interest_accrued_employee_contribution) as interest_employee,
                SUM(a.interest_accrued_employer_contribution) as interest_employer,
                (SELECT interest_rate FROM pfmoudetails WHERE pfmoudetails.pfmou_company_id = b.company_id
                AND pfmoudetails.effective_end_date IS NULL) as interest_rate,
                (SELECT FORMAT(pfmoudetails.mou_date,'dd-MM-yyyy') FROM pfmoudetails WHERE pfmoudetails.pfmou_company_id = b.company_id
			          AND pfmoudetails.effective_end_date IS NULL) as mou_date
                FROM pfstatements a
                INNER JOIN companyregistrations b ON b.company_id  = a.company_ref_id";

                $condition = " WHERE a.transaction_date BETWEEN '$fromDate' AND '$toDate'";
               
                if($registrationType != '' && $registrationType != null){

                  $condition =  $condition.' '." AND b.registration_type = '$registrationType'";
                }                

                $closing_data =  collect(DB::SELECT($closing_sql_data . ' '. $condition ." GROUP BY company_account_no,org_name,b.company_id"));

              if ($closing_data) {

                $pdf = App::make('dompdf.wrapper');
                $bladeView = view('reports.closingstatement', compact('closing_data','registrationType','fromDate','toDate'));
                $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
                $fileName = 'closing_report' . '_' . Carbon::now()->format('YmdHis') . '.pdf'; // $genRandomExtension

                  if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {

                      DB::table('documents')->insert([
                          'doc_type_id' => 7567485,
                          'doc_ref_no' => Carbon::now()->format('YmdHis') . random_int(00000, 99999),
                          'doc_ref_type' => 'ClosingReport',
                          'doc_type' => 'pdf',
                          'doc_path' => $fileName,
                          'doc_date' => Carbon::now()->format('Y-m-d'),
                          'registration_type' => $request->registration_type,
                          'doc_user_id' => auth('api')->user()->id
                      ]);

                      return response()->json(['success', 'message' => 'Closing Report Generated Successfully']);
                  } else {

                    return response()->json(['error', 'message' => 'Unable to Generate the Closing Report']);
                  }

              } else {
                  return response()->json(['error','message'=>'Error Generating the Report']);
              }
    }
}
