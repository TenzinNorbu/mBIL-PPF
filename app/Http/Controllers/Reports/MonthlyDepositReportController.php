<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use App\Models\Month;
use App\Models\Pfemployeeregistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MonthlyDepositReportController extends Controller
{
    public function GenerateMonthlyDepositReport(Request $request)
    {
        $for_the_month = $request->for_the_month;
        $for_the_year = $request->for_the_year;
        $company_id = $request->company_id;
        $registrationType = $request->registration_type;
        $employee_id = '';

        $sql_data = "SELECT
                std.company_ref_id,
                std.employee_ref_id,
                std.for_the_month,
                std.for_the_year,
                sum(std.employee_contribution) as total_employee_contribution,
                sum(std.employer_contribution) as total_employer_contribution,
                (select org_name from companyregistrations where companyregistrations.company_id = std.company_ref_id and companyregistrations.effective_end_date is null) as company_name,
                (select employee_name from pfemployeeregistrations where pfemployeeregistrations.pf_employee_id = std.employee_ref_id and pfemployeeregistrations.effective_end_date is null) as employee_name,
                (select employee_id_no from pfemployeeregistrations where pfemployeeregistrations.pf_employee_id = std.employee_ref_id and pfemployeeregistrations.effective_end_date is null) as employee_ac_no,
                (case when
                (select STRING_AGG(account_voucher_number ,',') from accounttransactions
                    inner join pfcollections on account_reference_no = pfcollections.pf_collection_no and pfcollections.pf_collection_no = std.transaction_ref_no) is null then
                    (select pfcollections.pf_collection_no from pfcollections where pfcollections.pf_collection_no = std.transaction_ref_no)
                else
                 (select STRING_AGG(account_voucher_number ,',') from accounttransactions
                inner join pfcollections on account_reference_no = pfcollections.pf_collection_no and pfcollections.pf_collection_no = std.transaction_ref_no) end) as voucher_no
              FROM pfstatements std";

        $conditions = " WHERE   std.transaction_type = 'Deposit'";

        if ($registrationType != '' && $registrationType != null) {

            $conditions = $conditions . ' ' . " AND std.registration_type = '$registrationType'";
        }else{

            $registrationType = 'All';
        }

        if ($for_the_year != '' && $for_the_year != null) {
            $conditions = $conditions . ' ' . "   AND std.for_the_year = '$for_the_year'";

        } else {

            $for_the_year = 'ALL';
        }
        if ($for_the_month != '' && $for_the_month != null) {

            $conditions = $conditions . ' ' . " AND std.for_the_month = '$for_the_month'";
            $forTheMonth = Month::where('id','=',$for_the_month)->get()->first()->month_name;

        } else {

            $forTheMonth = 'ALL';
        }

        if ($company_id != '' && $company_id != null) {

            $conditions = $conditions . ' ' . " AND std.company_ref_id = '$company_id'";
            $companyName = Companyregistration::where('company_id','=',$company_id)->get()->first()->org_name;
        }else{

            $companyName = 'All';
        }

        if ($employee_id != '' &&  $employee_id != null) {

           $conditions = $conditions . ' ' . " AND std.employee_ref_id = '$employee_id'";
           $emp_name = Pfemployeeregistration::where('pf_employee_id','=',$employee_id)->get()->first()->org_name;
        }else{

            $emp_name =  'All';
        }

        $get_monthly_sql_data = collect(DB::SELECT("$sql_data" . ' ' . "$conditions" . ' ' . " group by
                std.company_ref_id,
                std.employee_ref_id,
                std.for_the_month,
                std.for_the_year,
                std.transaction_date,
                std.transaction_ref_no ORDER BY std.transaction_date;"));

        if(empty($get_monthly_sql_data)) {

            return response()->json(['error', 'message' => 'No data found for the input parameter that you had passed.  Could not generate the report']);
        }

        if ($this->generateMonthlyDepositStatement($request, $get_monthly_sql_data, $registrationType, $forTheMonth, $for_the_year, $companyName) == 'success') {

            return response()->json(['success', 'message' => 'Monthly Deposit Statement Generated Successfully']);
        } else {

            return response()->json(['error', 'message' => 'Unable to generate Monthly Deposit Statement']);
        }
    }

    public function generateMonthlyDepositStatement(Request $request, $get_monthly_sql_data, $registrationType, $forTheMonth, $for_the_year, $companyName)
    {
        $pdf = App::make('dompdf.wrapper');
        $bladeView = view('reports.monthlydeposit', compact('get_monthly_sql_data','registrationType','forTheMonth', 'for_the_year', 'companyName'));
        $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
        $genRandomExtension = random_int(666666, 999999);
        $currentDateTime = Carbon::now()->format('YmdHis');
        $fileName = 'monthly_deposit_statement_' . $genRandomExtension . '_' . $currentDateTime . '.pdf'; // $genRandomExtension

        if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {

            DB::table('documents')->insert([
                'doc_type_id' => 120000,
                'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                'doc_ref_type' => 'MonthlyDepositStatement',
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
