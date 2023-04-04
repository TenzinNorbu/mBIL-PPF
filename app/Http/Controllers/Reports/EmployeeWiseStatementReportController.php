<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use App\Models\Pfemployeeregistration;
use App\Models\Pfstatement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

class EmployeeWiseStatementReportController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:individual-statement-report', ['only' => ['EmployeeWiseStatementReport']]);
    }

    public function EmployeeWiseStatementReport(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $pf_employee_id = $request->employee_id;
        $pf_employee_cid_no = $request->employee_cid_no;
        $no_of_days = 0;
        $registrationType = $request->registration_type;
        $organization_id = $request->company_id;
        $company_data = Companyregistration::where('company_id', '=', $organization_id)->first();
        $organization_name = $company_data->org_name;
        if($request->employee_id != '' || $request->employee_id != NULL){

                $organization_name = $company_data->org_name;
                $to_Date = \Carbon\Carbon::parse($request->to_date)->format('d-m-Y');
                $from_Date = \Carbon\Carbon::parse($request->from_date)->format('d-m-Y');

                $opening_contribution = collect(DB::select("SELECT company_ref_id,
                        sum(COALESCE(employee_contribution,0)) as total_employee_contribution,
                        sum(COALESCE(employer_contribution,0)) as total_employer_contribution,
                        sum(interest_accrued_employee_contribution) as total_interest_employee,
                        sum(interest_accrued_employer_contribution) as total_interest_employer
                        from pfstatements
                        where registration_type = '$registrationType' AND transaction_date < '$fromDate'
                        AND employee_ref_id = '$pf_employee_id'
                        GROUP BY company_ref_id"))->first();
                        

                $opening_refund = collect(DB::SELECT("SELECT
                        COALESCE(SUM(refund_employee_contribution),0) as total_employee_contribution,
                        COALESCE(SUM(refund_employer_contribution),0) as total_employer_contribution,
                        COALESCE(SUM(refund_interest_on_employee_contr),0) as refund_interest_employee,
                        COALESCE(SUM(refund_interest_on_employer_contr),0) as refund_interest_employer,
                        COALESCE(SUM(refund_as_on_interest_employee),0) as as_on_refund_employee_interest,
                        COALESCE(SUM(refund_as_on_interest_employer),0) as as_on_refund_employer_interest,
                        COALESCE(SUM(refund_total_disbursed_amount),0) as refund_disbursed_amount
                        from pfstatements
                        INNER JOIN refunds ON refunds.refund_ref_no = pfstatements.transaction_ref_no
                        WHERE pfstatements.transaction_date < '$fromDate'
                        AND refunds.registration_type = '$registrationType'
                        AND pfstatements.transaction_type = 'Refund'
                        AND employee_ref_id = '$pf_employee_id'"))->first();

                $current_contribution = collect(DB::select("SELECT
                    company_ref_id,
                    sum(employee_contribution) as total_employee_contribution,
                    sum(employer_contribution) as total_employer_contribution,
                    sum(interest_accrued_employee_contribution) as total_interest_employee,
                    sum(interest_accrued_employer_contribution) as total_interest_employer
                    FROM pfstatements
                    WHERE pfstatements.registration_type = '$registrationType'
                    AND pfstatements.transaction_date BETWEEN '$fromDate' AND '$toDate' AND employee_ref_id = '$pf_employee_id'
                    GROUP BY company_ref_id"))->first();

                $current_opening_refund = collect(DB::select("SELECT
                    COALESCE(SUM(refund_employee_contribution),0) as total_employee_contribution,
                    COALESCE(SUM(refund_employer_contribution),0) as total_employer_contribution,
                    COALESCE(SUM(refund_interest_on_employee_contr),0) as refund_interest_employee,
                    COALESCE(SUM(refund_interest_on_employer_contr),0) as refund_interest_employer,
                    COALESCE(SUM(refund_as_on_interest_employee),0) as as_on_refund_employee_interest,
                    COALESCE(SUM(refund_as_on_interest_employer),0) as as_on_refund_employer_interest,
                    COALESCE(SUM(refund_total_disbursed_amount),0) as refund_disbursed_amount
                    from pfstatements
                    INNER JOIN refunds ON refunds.refund_ref_no = pfstatements.transaction_ref_no
                    WHERE transaction_date BETWEEN '$fromDate' AND '$toDate' AND employee_ref_id = '$pf_employee_id'
                    AND pfstatements.transaction_type = 'Refund'
                    AND refunds.registration_type = '$registrationType'"))->first();

                $current_year_cont_details = collect(DB::select("SELECT
                    pfstatements.id,
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
                    WHERE pfstatements.registration_type = '$registrationType'
                    AND pfstatements.transaction_date BETWEEN '$fromDate' AND '$toDate' AND employee_ref_id = '$pf_employee_id'

                    GROUP BY pfstatements.id,company_ref_id, transaction_date, transaction_type,
                            transaction_ref_no,for_the_month,for_the_year,transaction_ref_no
                            ORDER BY pfstatements.id"));

                $employee_wise_data = Pfemployeeregistration::with(['empOrganization' => function ($query) {
                    return $query->with('getPfMouDetails')
                        ->where('effective_end_date', '=', NULL)->get()->first();
                }])
                    ->where('pf_employee_id', '=', $pf_employee_id)
                    ->with('lastTransactionData')
                    ->where('registration_type', '=', $registrationType)
                    ->where('effective_end_date', '=', NULL)
                    ->get()->first();

                if (!empty($employee_wise_data) && !empty($opening_refund) || !empty($employee_opening_balance)) {
                    $ref_interest_rate = $employee_wise_data->empOrganization->getPfMouDetails->interest_rate;

                    if ($employee_wise_data->lastTransactionData != null) {
                        $last_tran_date = $employee_wise_data->lastTransactionData->transaction_date;
                        $no_of_days = round(((strtotime($toDate) - strtotime($last_tran_date)) / 24 / 3600), 0);

                    } else {

                        return response()->json(['error', 'message' => 'There is no transactions made against the employee ' . '[ ' . $employee_wise_data->employee_name . ' ]']);
                    }

                    if ($no_of_days > 0) {

                        $as_on_data = $this->calculateAsOnBalance($request, $employee_wise_data->pf_employee_id, $employee_wise_data->pf_employee_company_id, $no_of_days, $ref_interest_rate);

                    } else {
                        $as_on_data = NULL;
                    }

                    $pdf = App::make('dompdf.wrapper');
                    $bladeView = view('reports.employeestatement', compact('opening_contribution', 'opening_refund', 'current_contribution',
                        'current_opening_refund', 'employee_wise_data', 'from_Date', 'to_Date', 'current_year_cont_details',
                        'as_on_data', 'registrationType', 'organization_name'));
                    $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
                    $genRandomExtension = random_int(666666, 999999);
                    $currentDateTime = Carbon::now()->format('YmdHis');
                    $fileName = 'employee_wise_statement_' . $genRandomExtension . '_' . $currentDateTime . '.pdf';
                    if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {

                        DB::table('documents')->insert([
                            'doc_type_id' => 800000,
                            'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                            'doc_ref_type' => 'EmpWiseStatement',
                            'doc_type' => 'pdf',
                            'doc_path' => $fileName,
                            'doc_date' => Carbon::now()->format('Y-m-d'),
                            'registration_type' => $request->registration_type,
                            'doc_user_id' => auth('api')->user()->id
                        ]);
                        return response()->json(['success', 'message' => 'Employee Wise Statement Generated Successfully']);

                    } else {
                        return response()->json(['error', 'message' => 'Unable to Generate Employee Wise Statement']);
                    }
                } else {
                    return response()->json(['error', 'message' => 'Unable to Generate Employee Wise Statement']);
                }

        } else {

              $employeeWiseData =  Pfemployeeregistration::where('pf_employee_company_id', '=', $organization_id)
              ->where('status','=','Active')
              ->get();

              foreach($employeeWiseData  as $employeeData){

                  $this->getEmployeeWiseStatement($request, $employeeData->pf_employee_id,$organization_name);
              }
            }
        }

    public function getEmployeeWiseStatement(Request $request, $pf_employee_id,$organization_name){
        $registrationType =  $request->registration_type;
        $to_Date = \Carbon\Carbon::parse($request->to_date)->format('d-m-Y');
        $from_Date = \Carbon\Carbon::parse($request->from_date)->format('d-m-Y');
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $opening_contribution = collect(DB::select("SELECT company_ref_id,
                sum(COALESCE(employee_contribution,0)) as total_employee_contribution,
                sum(COALESCE(employer_contribution,0)) as total_employer_contribution,
                sum(interest_accrued_employee_contribution) as total_interest_employee,
                sum(interest_accrued_employer_contribution) as total_interest_employer
                from pfstatements
                where registration_type = '$registrationType' AND transaction_date < '$fromDate'
                AND employee_ref_id = '$pf_employee_id'
                GROUP BY company_ref_id"))->first();

        $opening_refund = collect(DB::SELECT("SELECT
                COALESCE(SUM(refund_employee_contribution),0) as total_employee_contribution,
                COALESCE(SUM(refund_employer_contribution),0) as total_employer_contribution,
                COALESCE(SUM(refund_interest_on_employee_contr),0) as refund_interest_employee,
                COALESCE(SUM(refund_interest_on_employer_contr),0) as refund_interest_employer,
                COALESCE(SUM(refund_as_on_interest_employee),0) as as_on_refund_employee_interest,
                COALESCE(SUM(refund_as_on_interest_employer),0) as as_on_refund_employer_interest,
                COALESCE(SUM(refund_total_disbursed_amount),0) as refund_disbursed_amount
                from pfstatements
                INNER JOIN refunds ON refunds.refund_ref_no = pfstatements.transaction_ref_no
                WHERE transaction_date < '$fromDate'
                AND refunds.registration_type = '$registrationType'
                AND employee_ref_id = '$pf_employee_id'"))->first();

        $current_contribution = collect(DB::select("SELECT
            company_ref_id,
            sum(employee_contribution) as total_employee_contribution,
            sum(employer_contribution) as total_employer_contribution,
            sum(interest_accrued_employee_contribution) as total_interest_employee,
            sum(interest_accrued_employer_contribution) as total_interest_employer
            FROM pfstatements
            WHERE pfstatements.registration_type = '$registrationType'
            AND pfstatements.transaction_date BETWEEN '$fromDate' AND '$toDate' AND employee_ref_id = '$pf_employee_id'
            GROUP BY company_ref_id"))->first();

        $current_opening_refund = collect(DB::select("SELECT
            COALESCE(SUM(refund_employee_contribution),0) as total_employee_contribution,
            COALESCE(SUM(refund_employer_contribution),0) as total_employer_contribution,
            COALESCE(SUM(refund_interest_on_employee_contr),0) as refund_interest_employee,
            COALESCE(SUM(refund_interest_on_employer_contr),0) as refund_interest_employer,
            COALESCE(SUM(refund_as_on_interest_employee),0) as as_on_refund_employee_interest,
            COALESCE(SUM(refund_as_on_interest_employer),0) as as_on_refund_employer_interest,
            COALESCE(SUM(refund_total_disbursed_amount),0) as refund_disbursed_amount
            from pfstatements
            INNER JOIN refunds ON refunds.refund_ref_no = pfstatements.transaction_ref_no
            WHERE transaction_date BETWEEN '$fromDate' AND '$toDate' AND employee_ref_id = '$pf_employee_id'
            AND refunds.registration_type = '$registrationType'"))->first();

        $current_year_cont_details = collect(DB::select("SELECT
            pfstatements.id,
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
            WHERE pfstatements.registration_type = '$registrationType'
            AND pfstatements.transaction_date BETWEEN '$fromDate' AND '$toDate' AND employee_ref_id = '$pf_employee_id'

            GROUP BY pfstatements.id,company_ref_id, transaction_date, transaction_type,
                    transaction_ref_no,for_the_month,for_the_year,transaction_ref_no
                    ORDER BY pfstatements.id"));

        $employee_wise_data = Pfemployeeregistration::with(['empOrganization' => function ($query) {
            return $query->with('getPfMouDetails')
                ->where('effective_end_date', '=', NULL)->get()->first();
        }])
            ->where('pf_employee_id', '=', $pf_employee_id)
            ->with('lastTransactionData')
            ->where('registration_type', '=', $registrationType)
            ->where('effective_end_date', '=', NULL)
            ->get()->first();

        if (!empty($employee_wise_data) && !empty($opening_refund) || !empty($employee_opening_balance)) {
            $ref_interest_rate = $employee_wise_data->empOrganization->getPfMouDetails->interest_rate;

            if ($employee_wise_data->lastTransactionData != null) {
                $last_tran_date = $employee_wise_data->lastTransactionData->transaction_date;
                $no_of_days = round(((strtotime($toDate) - strtotime($last_tran_date)) / 24 / 3600), 0);

            } else {

                return response()->json(['error', 'message' => 'There is no transactions made against the employee ' . '[ ' . $employee_wise_data->employee_name . ' ]']);
            }

            if ($no_of_days > 0) {

                $as_on_data = $this->calculateAsOnBalance($request, $employee_wise_data->pf_employee_id, $employee_wise_data->pf_employee_company_id, $no_of_days, $ref_interest_rate);

            } else {
                $as_on_data = NULL;
            }

            $pdf = App::make('dompdf.wrapper');
            $bladeView = view('reports.getemployeewisestatement', compact('opening_contribution', 'opening_refund', 'current_contribution',
                'current_opening_refund', 'employee_wise_data', 'from_Date', 'to_Date', 'current_year_cont_details',
                'as_on_data', 'registrationType', 'organization_name'));
            $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
            $genRandomExtension = random_int(666666, 999999);
            $currentDateTime = Carbon::now()->format('YmdHis');
            $fileName = 'employee_wise_statement_' . $genRandomExtension . '_' . $currentDateTime . '.pdf';

            $pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()));

            DB::table('documents')->insert([
                'doc_type_id' => 800000,
                'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                'doc_ref_type' => 'EmpWiseStatement',
                'doc_type' => 'pdf',
                'doc_path' => $fileName,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'registration_type' => $request->registration_type,
            ]);

            return response()->json(['success', 'message' => 'Employee Wise Statement Generated Successfully']);
        } else {

            return response()->json(['error', 'message' => 'Unable to Generate Employee Wise Statement']);
        }
    }

    public function calculateAsOnBalance(Request $request, $employee_id, $company_id, $no_of_days, $ref_interest_rate)
    {
        $get_transaction_details = Pfstatement::where('employee_ref_id', '=', "$employee_id")
            ->where('registration_type', '=', $request->registration_type)
            ->where('company_ref_id', '=', "$company_id")
            ->whereRaw("transaction_version_no = (select max(transaction_version_no) from pfstatements where employee_ref_id = $employee_id)")
            ->get()->first();

        $totalEmployeeContribution = $get_transaction_details->total_employee_contribution;
        $totalEmployerContribution = $get_transaction_details->total_employer_contribution;

        $totalInterestOnEmployee = $get_transaction_details->total_interest_on_employee_contribution;
        $totalInterestOnEmployer = $get_transaction_details->total_interest_on_employer_contribution;

        $interest_chargeable_amount01 = $get_transaction_details->interest_chargeable_amount_1;
        $interest_chargeable_amount02 = $get_transaction_details->interest_chargeable_amount_2;

        $gross_os_balance_employee = $get_transaction_details->gross_os_balance_employee;
        $gross_os_balance_employer = $get_transaction_details->gross_os_balance_employer;

        $interestAccruedEmployee = ($interest_chargeable_amount01 * $ref_interest_rate / 100 * $no_of_days) / 365;
        $interestAccruedEmployer = ($interest_chargeable_amount02 * $ref_interest_rate / 100 * $no_of_days) / 365;

        $employeeContribution = 0;
        $employerContribution = 0;

        $as_on_int_chargeable_amt_1 = $interest_chargeable_amount01 + $employeeContribution;
        $as_on_int_chargeable_amt_2 = $interest_chargeable_amount02 + $employerContribution;
        $as_on_outstanding_01 = $gross_os_balance_employee + $interestAccruedEmployee + $employeeContribution;
        $as_on_outstanding_02 = $gross_os_balance_employer + $interestAccruedEmployer + $employerContribution;
        $as_on_int_outstanding_01 = $totalInterestOnEmployee + $interestAccruedEmployee;
        $as_on_int_outstanding_02 = $totalInterestOnEmployer + $interestAccruedEmployer;

        $new_data = [
            'pf_employee_id' => $employee_id,
            'pf_employee_company_id' => $company_id,
            'employee_contribution' => $employeeContribution,
            'employer_contribution' => $employerContribution,

            'total_employee_contribution' => $totalEmployeeContribution,
            'total_employer_contribution' => $totalEmployerContribution,

            'interest_accrued_employee' => $interestAccruedEmployee,
            'interest_accrued_employer' => $interestAccruedEmployer,

            'interest_chargable_amount_employee' => $as_on_int_chargeable_amt_1,
            'interest_chargable_amount_employer' => $as_on_int_chargeable_amt_2,

            'total_int_outstanding_employee' => $as_on_int_outstanding_01,
            'total_int_outstanding_employer' => $as_on_int_outstanding_02,

            'total_outstanding_bal_employee' => $as_on_outstanding_01,
            'total_outstanding_bal_employer' => $as_on_outstanding_02,
        ];

        return $new_data;
    }
}
