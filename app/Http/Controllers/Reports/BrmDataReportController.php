<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use App\Models\Month;
use Carbon\Carbon;

class BrmDataReportController extends Controller
{
    public function BrmDataReport(Request $request) {

    $fromDate = $request->from_date;
    $toDate = $request->to_date;
    $registrationType = $request->registration_type;

    $brm_data = collect(DB::SELECT("SELECT
			(pfcollections.pf_collection_branch_id) as pf_collection_branch_id,
			company_account_no as company_account_no,
			org_name as company_name,
			effective_start_date as effective_start_date,
			(CASE WHEN companyregistrations.effective_start_date < '$fromDate' THEN 
			SUM(pf_collection_amount) else 0 END) as additional,
			(CASE WHEN companyregistrations.effective_start_date >= '$fromDate' THEN 
			SUM(pf_collection_amount) else 0 END) as new

			FROM pfcollections
			INNER JOIN companyregistrations ON companyregistrations.company_id = pfcollections.pf_collection_company_account_no_id
			WHERE pfcollections.registration_type = '$registrationType' AND
			pfcollections.pf_collection_date BETWEEN '$fromDate' AND '$toDate'
			GROUP BY company_account_no,org_name,effective_start_date,pf_collection_branch_id"));

	if ($brm_data) {

		$pdf = App::make('dompdf.wrapper');
		$bladeView = view('reports.brmreport', compact('brm_data','registrationType','fromDate','toDate'));
		$pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
		$fileName = 'brm_report' . '_' . Carbon::now()->format('YmdHis') . '.pdf'; // $genRandomExtension

  		if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {

        			DB::table('documents')->insert([
        			'doc_type_id' => 7384565,
        			'doc_ref_no' => Carbon::now()->format('YmdHis') . random_int(1111, 9999),
        			'doc_ref_type' => 'BrmReport',
        			'doc_type' => 'pdf',
        			'doc_path' => $fileName,
        			'doc_date' => Carbon::now()->format('Y-m-d'),
        			'registration_type' => $registrationType,
              'doc_user_id' => auth('api')->user()->id
        		]);

    		    return response()->json(['success', 'message' => 'BRM Report Generated Successfully']);
        		} else {

        				return response()->json(['error', 'message' => 'Unable to Generate BRM Report']);
        			}
    		} else {

			       return response()->json(['error','message'=>'Unable to generate the BRM Report']);
		     }
    }
}
