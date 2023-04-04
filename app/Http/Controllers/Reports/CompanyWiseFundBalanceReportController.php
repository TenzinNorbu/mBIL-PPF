<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyWiseFundBalanceReportController extends Controller
{
    public function CompanyWiseFundBalanceReport(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $regType = $request->registration_type;
        $companyId = $request->company_id;

        $company_wise_fund_balance_data = "SELECT
            a.company_ref_id,
            SUM(a.employee_contribution + a.employer_contribution) as total_contributions,
            SUM(a.interest_accrued_employee_contribution + a.interest_accrued_employer_contribution) as total_interest,
            
            (SELECT SUM(refund_total_disbursed_amount ) from refunds
                     WHERE refund_processing_date <= '$toDate' and refunds.refund_company_id = a.company_ref_id 
                     and refunds.refund_processed_remarks != 'Excess Payment Refund') as total_disbursed_amount,
            
            (SELECT org_name from companyregistrations
                WHERE companyregistrations.company_id = a.company_ref_id) as company_name,
            
            (select interest_rate from pfmoudetails where effective_end_date is null and pfmou_company_id = a.company_ref_id) as int_rate

            FROM pfstatements a";

        $condition = " WHERE transaction_date <= '$toDate'";

        if ($regType != NULL && $regType != '') {
            $condition = $condition . " AND a.registration_type = '$regType'";
        }else{
            $regType = 'All';
        }

        if ($companyId != NULL && $companyId != '') {
            $condition = $condition . " AND a.company_ref_id = '$companyId'";
            $company_data = Companyregistration::where('company_id', '=', $companyId)->get()->first();
            $companyName = $company_data->org_name;
        }else{
            $companyName = 'All';
        }

        $select_query = $company_wise_fund_balance_data . '' . $condition . '' . 'group by a.company_ref_id';
        $company_wise_data = DB::select($select_query);

        if ($this->generateCompanyWiseFundBalanceReport($company_wise_data, $fromDate, $toDate, $regType, $companyName) == 'success') {

            return response()->json(['success', 'message' => 'Company Wise Fund Balance Statement Generated Successfully']);
        } else {

            return response()->json(['error', 'message' => 'Could not generate the Company Wise Fund Balance Report']);
        }
    }

    public function generateCompanyWiseFundBalanceReport($company_wise_data, $fromDate, $toDate, $regType, $companyName)
    {

        $pdf = App::make('dompdf.wrapper');
        $bladeView = view('reports.companyfundbalance', compact('company_wise_data', 'fromDate', 'toDate', 'regType', 'companyName'));
        $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
        $fileName = 'companywise_fundbalance_report' . random_int(666666, 999999) . '_' . Carbon::now()->format('YmdHis') . '.pdf'; // $genRandomExtension

        if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {

            DB::table('documents')->insert([
                'doc_type_id' => 320000,
                'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                'doc_ref_type' => 'CompanyWiseFundBalance',
                'doc_type' => 'pdf',
                'doc_path' => $fileName,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'registration_type' => $regType,
                'doc_user_id' => auth('api')->user()->id
            ]);

            return 'success';
        } else {

            return 'error';
        }
    }
}
