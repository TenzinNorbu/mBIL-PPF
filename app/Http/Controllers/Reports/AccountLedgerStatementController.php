<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Accountgroup;
use App\Models\Accounttype;
use App\Models\Branch;
use App\Models\Companyregistration;
use App\Models\Pfemployeeregistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AccountLedgerStatementController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:account-ledger-report', ['only' => ['GenerateAccSubLedgerStatement']]);
    }

    public function GenerateAccSubLedgerStatement(Request $request)
    {
        $start_date = $request->from_date;
        $end_date = $request->to_date;
        $businessType = $request->registration_type;
        $account_group_id = $request->account_group_id;
        $account_type_id = $request->account_type_id;
        $branch_id = $request->branch_id;
        $company_id = $request->company_id;
        $employee_id = $request->employee_id;
        $document_format = 'html';

        $opening_account_ledger_data = "SELECT
                SUM(accounttransactiondetails.acc_credit_amount) AS opening_credit_amount,
                SUM(accounttransactiondetails.acc_debit_amount) AS opening_debit_amount
                FROM accounttransactions
                INNER JOIN accounttransactiondetails ON accounttransactiondetails.acc_transaction_type_id = accounttransactions.account_transaction_id";

        // Opening Balance Condition
        $opening_condition = " WHERE account_effective_end_date IS NULL
                        AND acc_effective_end_date is NULL
                        AND account_voucher_date < '$start_date'";

        if ($account_group_id == NULL || $account_group_id == '') {
            $accountGroupName = 'All';

        }else{
            $opening_condition = $opening_condition . " AND acc_account_group_id = '$account_group_id'";
            $accountGroupName = Accountgroup::where('account_group_id', $account_group_id)->get()->first()->account_group_name;
            
        }

        if ($account_type_id == NULL || $account_type_id == '') {
            
            $accountTypeName = 'All';

        }else{
            $opening_condition = $opening_condition . " AND accounttransactiondetails.acc_account_type_id = '$account_type_id'";
            $accountTypeName = Accounttype::where('account_type_id',$account_type_id)->get()->first()->acc_name;

        }

        if ($request->registration_type == NULL || $request->registration_type == '') {
            $businessType = 'All';

        }else{
            $opening_condition = $opening_condition . " AND accounttransactions.registration_type = '$request->registration_type'";
           
        }

        if ($branch_id == NULL || $branch_id == '') {

            $branchName ='All';
          
        }else{
             $opening_condition = $opening_condition . " AND accounttransactiondetails.acc_td_branch_id = '$branch_id'";
             $branchName = Branch::where('id', $branch_id)->get()->first()->branch_name;
        }

        if ($company_id == NULL || $company_id == '' || $company_id == 'undefined') {
            $companyName = 'All';           

        }else{

            $opening_condition = $opening_condition . " AND accounttransactiondetails.acc_company_id = '$company_id'";
            $companyName = Companyregistration::where('company_id ','=',$company_id)->get()->first()->org_name;

        }

        if ($employee_id == NULL || $employee_id == '') {
            
            $employeeName = 'All';

        }else{

            $opening_condition = $opening_condition . " AND accounttransactiondetails.acc_employee_id = '$employee_id'";
            $emp_data = Pfemployeeregistration::where('pf_employee_id', $employee_id)->get()->first();
            $employeeName = $emp_data->employee_name;
           
        }


        $account_ledger_report_data = "SELECT
                accounttransactions.account_voucher_date,
        				accounttransactions.account_voucher_number,
        				accounttransactions.account_voucher_narration,
        				accounttransactions.account_collection_instrument_no,
        				accounttransactions.account_cheque_date,
                accounttransactiondetails.acc_credit_amount AS acc_credit_amount ,
                accounttransactiondetails.acc_debit_amount AS acc_debit_amount
                FROM accounttransactions
                INNER JOIN accounttransactiondetails ON accounttransactiondetails.acc_transaction_type_id = accounttransactions.account_transaction_id";

        $condition = " WHERE account_effective_end_date IS NULL
                        AND acc_effective_end_date is NULL
                        AND account_voucher_date  between '$start_date' and '$end_date'";

        if ($account_group_id == NULL || $account_group_id == '') {
            
        }else{
            $condition = $condition . " AND acc_account_group_id = '$account_group_id'";
        }
        if ($account_type_id == NULL || $account_type_id == '') {
            
        }else{
            $condition = $condition . " AND accounttransactiondetails.acc_account_type_id = '$account_type_id'";
        }

        if ($request->registration_type == NULL || $request->registration_type == '') {
           
        }else{
             $condition = $condition . " AND accounttransactions.registration_type = '$request->registration_type'";
        }

        if ($branch_id != NULL && $branch_id != '') {
            $condition = $condition . " AND accounttransactiondetails.acc_td_branch_id = '$branch_id'";
        }
        if ($company_id != NULL && $company_id != '') {
            $condition = $condition . " AND accounttransactiondetails.acc_company_id = '$company_id'";
        }

        if ($employee_id != NULL && $employee_id != '') {
            $condition = $condition . " AND accounttransactiondetails.acc_employee_id = '$employee_id'";
        }

        $select_opening_query = $opening_account_ledger_data . ' ' . $opening_condition;
        $opening_query = DB::select($select_opening_query);

        $select_query = $account_ledger_report_data . ' ' . $condition;
        $account_ledger_sql = DB::select($select_query);

        //return $account_ledger_sql;

        if ($this->generateAccountLedgerStatement($account_ledger_sql, $start_date, $end_date, $accountGroupName,
                $accountTypeName, $companyName, $branchName, $employeeName,$opening_query, $businessType, $document_format) == 'success') {

            return response()->json(['success', 'message' => 'Account Ledger Report Generated Successfully']);
        } else {

            return response()->json(['error', 'message' => 'Unable to Generate Account Ledger Report']);
        }

    }

    public function generateAccountLedgerStatement($account_ledger_sql, $start_date, $end_date, $accountGroupName,
                                                   $accountTypeName, $companyName, $branchName, $employeeName,$opening_query, $businessType,$document_format)
    {
        $genRandomExtension = random_int(666666, 999999);
        $currentDateTime = Carbon::now()->format('YmdHis');

        if($document_format == 'pdf'){

            $pdf = App::make('dompdf.wrapper');
            $bladeView = view('reports.accountledger', compact('account_ledger_sql',
                'start_date', 'end_date', 'accountGroupName',
                'accountTypeName', 'companyName', 'branchName', 'employeeName','opening_query','businessType'));
            $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
           
            $fileName = 'account_ledger_statement_' . $genRandomExtension . '_' . $currentDateTime . '.pdf'; 
    
            if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {
    
                DB::table('documents')->insert([
                    'doc_type_id' => 130000,
                    'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                    'doc_ref_type' => 'AccountLedgerStatement',
                    'doc_type' => 'pdf',
                    'doc_path' => $fileName,
                    'doc_date' => Carbon::now()->format('Y-m-d'),
                    'registration_type' => $businessType,
                    'doc_user_id' => auth('api')->user()->id
                ]);
    
                return 'success';
    
            } else {
    
                return 'error';
            }

        }else{

            $bladeView = view('reports.accountledger', compact('account_ledger_sql',
            'start_date', 'end_date', 'accountGroupName',
            'accountTypeName', 'companyName', 'branchName', 'employeeName','opening_query','businessType'));
            $fileName = 'account_ledger_statement_' . $genRandomExtension . '_' . $currentDateTime;

            if(Storage::disk('reports')->put($fileName.'.html', $bladeView)){
                    
                    DB::table('documents')->insert([
                        'doc_type_id' => 130000,
                        'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                        'doc_ref_type' => 'AccountLedgerStatement',
                        'doc_type' => 'html',
                        'doc_path' => $fileName.'.html',
                        'doc_date' => Carbon::now()->format('Y-m-d'),
                        'registration_type' => $businessType,
                        'doc_user_id' => auth('api')->user()->id
                    ]);

                    return 'success';
                }else{
                    return 'error';
                }

        }
       

    }
}
