<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyWiseStatementReportController extends Controller
{
    public function CompanyWiseStatementReport(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $company_id = $request->company_id;
        $regType = $request->registration_type;

        $company_name = Companyregistration::where('company_id','=',$company_id)->get()->first()->org_name;

        $opening_contribution = collect(DB::select("SELECT company_ref_id,
            sum(employee_contribution) as total_employee_contribution,
            sum(employer_contribution) as total_employer_contribution,
            sum(interest_accrued_employee_contribution) as total_interest_employee,
            sum(interest_accrued_employer_contribution) as total_interest_employer
            from pfstatements
      			WHERE registration_type = '$regType' AND transaction_date < '$fromDate' AND company_ref_id = '$company_id'
      			GROUP BY company_ref_id"))->first();

        $opening_refund = collect(DB::SELECT("select 

            (SELECT COALESCE(SUM(refund_employee_contribution),0) from refunds
            where refunds.refund_company_id = s.company_ref_id) as total_employee_contribution,

            (SELECT COALESCE(SUM(refund_interest_on_employee_contr),0) from refunds
            where refunds.refund_company_id = s.company_ref_id) as total_employer_contribution,

            (SELECT COALESCE(SUM(refund_employer_contribution),0) from refunds
            where refunds.refund_company_id = s.company_ref_id) as refund_interest_employee,

            (SELECT COALESCE(SUM(refund_interest_on_employer_contr),0) from refunds
            where refunds.refund_company_id = s.company_ref_id) as refund_interest_employer,

            (SELECT COALESCE(SUM(refund_as_on_interest_employee),0) from refunds
            where refunds.refund_company_id = s.company_ref_id) as as_on_refund_employee_interest,

            (SELECT COALESCE(SUM(refund_as_on_interest_employer),0) from refunds
            where refunds.refund_company_id = s.company_ref_id) as as_on_refund_employer_interest,

            (SELECT COALESCE(SUM(refund_total_disbursed_amount),0) from refunds
            WHERE refund_processing_date <= '$fromDate' and refunds.refund_company_id = s.company_ref_id 
            and refunds.refund_processed_remarks != 'Excess Payment Refund') as refund_disbursed_amount

            from pfstatements s
            where s.transaction_date <= '$fromDate' and s.registration_type = '$regType' 
            and s.transaction_type = 'Refund' and s.company_ref_id = '$company_id'"))->first();

        $current_contribution = collect(DB::select("SELECT
            company_ref_id,
            sum(employee_contribution) as total_employee_contribution,
            sum(employer_contribution) as total_employer_contribution,
            sum(interest_accrued_employee_contribution) as total_interest_employee,
            sum(interest_accrued_employer_contribution) as total_interest_employer

            FROM pfstatements
            WHERE pfstatements.registration_type = '$regType'
            AND pfstatements.transaction_date BETWEEN '$fromDate' AND '$toDate' AND company_ref_id = '$company_id'
            GROUP BY company_ref_id"))->first();

        $current_refund = collect(DB::select("SELECT
            COALESCE(SUM(refund_employee_contribution),0) as total_employee_contribution,
            COALESCE(SUM(refund_employer_contribution),0) as total_employer_contribution,
            COALESCE(SUM(refund_interest_on_employee_contr),0) as refund_interest_employee,
            COALESCE(SUM(refund_interest_on_employer_contr),0) as refund_interest_employer,
            COALESCE(SUM(refund_as_on_interest_employee),0) as as_on_refund_employee_interest,
            COALESCE(SUM(refund_as_on_interest_employer),0) as as_on_refund_employer_interest,
            COALESCE(SUM(refund_total_disbursed_amount),0) as refund_disbursed_amount
            from pfstatements
            INNER JOIN refunds ON refunds.refund_ref_no = pfstatements.transaction_ref_no
            WHERE transaction_date BETWEEN '$fromDate' AND '$toDate'
            AND pfstatements.transaction_type = 'Refund'
            AND refunds.registration_type = '$regType'
            AND company_ref_id = '$company_id'"))->first();

        $current_year_cont_details = collect(DB::select("SELECT
            company_ref_id,
            for_the_month,
            for_the_year,
            transaction_ref_no,
            transaction_date,
            transaction_type,
            sum(employee_contribution) as total_employee_contribution,
            sum(employer_contribution) as total_employer_contribution,
            sum(interest_accrued_employee_contribution) as total_interest_employee,
            sum(interest_accrued_employer_contribution) as total_interest_employer,
            (CASE WHEN transaction_type = 'Refund' THEN
                (SELECT sum(refund_total_disbursed_amount) FROM refunds WHERE pfstatements.transaction_ref_no = refunds.refund_ref_no GROUP BY refunds.refund_ref_no)
            ELSE 0 END) AS disbursed_amount

            FROM pfstatements
            WHERE pfstatements.registration_type = '$regType'
            AND pfstatements.transaction_date BETWEEN '$fromDate' AND '$toDate' AND company_ref_id = '$company_id'

            GROUP BY pfstatements.id,company_ref_id, transaction_date, transaction_type,
                    transaction_ref_no,for_the_month,for_the_year,transaction_ref_no ORDER BY  pfstatements.id"));

        if ($this->generateCompanyWiseStatement($request, $opening_contribution, $opening_refund,$current_contribution,$current_refund,
                $current_year_cont_details, $fromDate, $toDate, $regType, $company_name) == 'success') {

            return response()->json(['success', 'message' => 'Company Wise Statement Generated Successfully']);
        } else {

            return response()->json(['error', 'message' => 'Could not generate the company wise statement!']);
        }
    }

    public function generateCompanyWiseStatement(Request $request, $opening_contribution, $opening_refund,$current_contribution,$current_refund,
                                                         $current_year_cont_details, $fromDate, $toDate, $regType, $company_name)
    {
        $pdf = App::make('dompdf.wrapper');
        $bladeView = view('reports.companywise', compact('opening_contribution', 'opening_refund', 'current_contribution',
            'current_refund', 'current_year_cont_details', 'fromDate','toDate','regType','company_name'));
        $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
        $genRandomExtension = random_int(666666, 999999);
        $currentDateTime = Carbon::now()->format('YmdHis');
        $fileName = 'company_wise_statement' . $genRandomExtension . '_' . $currentDateTime . '.pdf'; // $genRandomExtension

        if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {
            DB::table('documents')->insert([
                'doc_type_id' => 110000,
                'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                'doc_ref_type' => 'CmpWiseStatement',
                'doc_type' => 'pdf',
                'doc_path' => $fileName,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'registration_type' => $request->registration_type,
                'doc_user_id' => auth('api')->user()->id
            ]);
            return 'success';
        } else {

            return 'error';
        }
    }
}
