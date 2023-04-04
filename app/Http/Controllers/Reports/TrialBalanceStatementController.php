<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TrialBalanceStatementController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:trial-balance-report', ['only' => ['TrialBalanceReport']]);
    }

    public function TrialBalanceReport(Request $request)
    {
        $start_date = $request->from_date;
        $year_in_date = Carbon::createFromFormat('Y-m-d', $start_date)->year;
        $start_date = $year_in_date.'-'.'01-01';

        $end_date = $request->to_date;
        $branch_id = $request->branch_id;
        $run_date = Carbon::now()->format('Y-m-d');
        $document_format = 'pdf';


        $trial_balance_data = "SELECT
                accounttransactiondetails.acc_account_group_id,
                accounttransactiondetails.acc_account_type_id,
                accounttransactiondetails.acc_sub_ledger_id,
                accountgroups.account_group_code AS group_code,
                accountgroups.account_group_name AS group_name,
                accounttypes.acc_name AS acc_type_name,

                SUM(acc_debit_amount) AS  dr_amount,
                SUM(acc_credit_amount)  AS cr_amount

            FROM accounttransactions
                INNER JOIN accounttransactiondetails ON accounttransactiondetails.acc_transaction_type_id = accounttransactions.account_transaction_id
                INNER JOIN accountgroups ON accountgroups.account_group_id = accounttransactiondetails.acc_account_group_id
                INNER JOIN accounttypes ON accounttypes.account_type_id = accounttransactiondetails.acc_account_type_id";

        $trial_balance_condition = " WHERE accounttransactions.account_voucher_date between '$start_date' AND '$end_date' 
        and accounttransactiondetails.acc_effective_end_date is null
        and accounttransactions.account_effective_end_date is null";

        if ($request->registration_type != NULL || $request->registration_type != '') {

            $trial_balance_condition = $trial_balance_condition . " AND accounttransactiondetails.registration_type = '$request->registration_type'";
            $businessType = $request->registration_type;
        }else{
            $businessType = 'All';
        }

        if ($branch_id != NULL && $branch_id != '') {

            $trial_balance_condition = $trial_balance_condition . " AND accounttransactiondetails.acc_td_branch_id = '$branch_id'";
            $getBranchName = Branch::where('id', $branch_id)->get()->first()->branch_name;
        } else {

            $getBranchName = 'ALL';
        }

        $select_query = $trial_balance_data . '' . $trial_balance_condition . ' ' . ' GROUP BY accounttransactiondetails.acc_account_group_id,
            accounttransactiondetails.acc_account_type_id,
            accounttransactiondetails.acc_sub_ledger_id,
            accountgroups.account_group_code,
            accountgroups.account_group_name,
            accounttypes.acc_name ORDER BY accounttransactiondetails.acc_account_group_id';

        $trial_balance_sql = DB::select($select_query);

        if ($this->generateTrialBalanceStatement($trial_balance_sql, $getBranchName, $start_date, $end_date, $run_date, $businessType, $document_format) == 'success') {

            return response()->json(['success', 'message' => 'Trial Balance Report Generated Successfully']);
        } else {
            return response()->json(['error', 'message' => 'Unable to Generate Trial Balance Report']);
        }
    }

    public function generateTrialBalanceStatement($trial_balance_sql, $getBranchName, $start_date, $end_date, $run_date,$businessType, $document_format)
    {
        if($document_format == 'pdf'){
        
            $pdf = App::make('dompdf.wrapper');
            $bladeView = view('reports.trialbalance', compact('trial_balance_sql', 'getBranchName',
                'start_date', 'end_date', 'run_date', 'businessType'));
            $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');

            $fileName = 'trial_balance_statement_' . random_int(666666, 999999) . '_' . Carbon::now()->format('YmdHis') . '.pdf'; 

            if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {

                DB::table('documents')->insert([
                    'doc_type_id' => 140000,
                    'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                    'doc_ref_type' => 'TrialBalanceStatement',
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

            $bladeView = view('reports.trialbalance', compact('trial_balance_sql', 'getBranchName',
            'start_date', 'end_date', 'run_date', 'businessType'));
            $fileName = 'trial_balance_statement_' . random_int(666666, 999999) . '_' . Carbon::now()->format('YmdHis'); 
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
