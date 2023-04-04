<?php

namespace App\Http\Controllers\GF;

use App\Http\Controllers\Controller;
use App\Models\Accounttransaction;
use App\Models\Accounttransactiondetail;
use App\Models\Pfcollection;
use App\Models\Pfmoudetail;
use App\Models\Pfstatement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GfIndividualdepositController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:view-pending-deposits|view-approved-deposits|create-deposits', ['only' => ['listPendingDeposits', 'listApproveDeposits']]);
        $this->middleware('permission:create-deposits', ['only' => ['saveIndividualDeposit']]);
    }

    public function listApproveDeposits()
    {
        return Pfcollection::join('companyregistrations', 'pfcollections.pf_collection_company_account_no_id', '=', 'companyregistrations.company_id')
            ->join('months', 'pfcollections.pf_collection_for_the_month', '=', 'months.id')
            ->with('pfCollectionBranch')
            ->where('pf_collection_status', '=', 'Approved')
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->where('pfcollections.registration_type', '=', 'GF')
            ->get();
    }

    public function saveIndividualDeposit(Request $request)
    {
        DB::beginTransaction();
        $companyRefNo = (integer)$request->pf_company_id;
        $collection_id = $request->pf_collection_id;
        $collection_no = $request->pf_collection_no;
        $collection_branch_id = (int)$request->collection_branch_id;
        $transactionType = 'Deposit';
        $transactionDate = $request->pf_collection_date;
        $forTheMonth = (integer)$request->pf_collection_for_the_month;
        $forTheYear = (integer)$request->pf_collection_for_the_year;
        $created_date = Carbon::now()->format('Y-m-d');
        $created_by = auth('api')->user()->name;
        $employeRefId = $request->pfEmployeeId;
        $totalReceiptAmount = (float)$request->totalReceiptAmount;
        $total_refund_diff_excess = $totalReceiptAmount - (float)$request->excess_payment_amount;
        $total_interest_accrued_employee = 0;
        $total_interest_accrued_employer = 0;

        $pfmou_data = Pfmoudetail::where('pfmou_company_id', '=', $companyRefNo)
            ->where('effective_end_date', '=', NULL)
            ->where('registration_type', '=', 'GF')
            ->get()->first();

        $interest_rate = (float)$pfmou_data->interest_rate;
        $deposit_ref_no = date('YmdH') . random_int(66666, 99999);

        foreach ($employeRefId as $data) {

            $empRefId = $data['pf_employee_id'];
            $employee_contribution = (float)$data['employee_contribution_amount'];
            $employer_contribution = (float)$data['employer_contribution_amount'];

            DB::table('pfemployeeregistrations')
                ->where('pf_employee_id', '=', $empRefId)
                ->where('registration_type', '=', 'GF')
                ->where('effective_end_date', '=', NULL)
                ->update([
                    'employee_contribution_amount' => $employee_contribution,
                    'employer_contribution_amount' => $employer_contribution
                ]);

            $emp_data = Pfstatement::where('employee_ref_id', '=', $empRefId)
                ->where('registration_type', '=', 'GF')
                ->orderBy('transaction_version_no', 'DESC')
                ->get()->first();

            if ($emp_data == null) {

                $interest_accrued_employee_contribution = 0;
                $interest_accrued_employer_contribution = 0;

                $total_interest_on_employee_contribution = 0;
                $total_interest_on_employer_contribution = 0;

                $interest_chargeable_amount_1 = $employee_contribution;
                $interest_chargeable_amount_2 = $employer_contribution;

                $total_employee_contribution = $employee_contribution;
                $total_employer_contribution = $employer_contribution;

                $gross_os_balance_employee = $employee_contribution;
                $gross_os_balance_employer = $employer_contribution;

                $previous_disbursed_amount = 0;

                $transaction_version_no = 1;

                $days = 0;

            } else {

                $days = round(((strtotime($transactionDate) - strtotime($emp_data->transaction_date)) / 24 / 3600), 0);

                if ($days < 0) {
                    return 'error';
                }

                $interest_accrued_employee_contribution = round(($emp_data->interest_chargeable_amount_1 * $interest_rate / 100 * $days / 365), 2);
                $interest_accrued_employer_contribution = round(($emp_data->interest_chargeable_amount_2 * $interest_rate / 100 * $days / 365), 2);

                $total_interest_on_employee_contribution = (float)($emp_data->total_interest_on_employee_contribution + $interest_accrued_employee_contribution);
                $total_interest_on_employer_contribution = (float)($emp_data->total_interest_on_employer_contribution + $interest_accrued_employer_contribution);

                $interest_chargeable_amount_1 = (float)($emp_data->interest_chargeable_amount_1 + $employee_contribution);
                $interest_chargeable_amount_2 = (float)($emp_data->interest_chargeable_amount_2 + $employer_contribution);

                $total_employee_contribution = (float)($emp_data->total_employee_contribution + $employee_contribution);
                $total_employer_contribution = (float)($emp_data->total_employer_contribution + $employer_contribution);

                $gross_os_balance_employee = (float)($emp_data->gross_os_balance_employee + $employee_contribution + $interest_accrued_employee_contribution);
                $gross_os_balance_employer = (float)($emp_data->gross_os_balance_employer + $employer_contribution + $interest_accrued_employer_contribution);

                $previous_disbursed_amount = $emp_data->prev_total_disbursed_amount;

                $transaction_version_no = $emp_data->transaction_version_no + 1;
            }

            $total_interest_accrued_employee = (float)($total_interest_accrued_employee + $interest_accrued_employee_contribution);
            $total_interest_accrued_employer = (float)($total_interest_accrued_employer + $interest_accrued_employer_contribution);

            // Update on Employee Table

            $pfstatementObj = new Pfstatement();
            $pfstatementObj->transaction_no = $deposit_ref_no;
            $pfstatementObj->company_ref_id = $companyRefNo;
            $pfstatementObj->employee_ref_id = $empRefId;
            $pfstatementObj->transaction_type = $transactionType;
            $pfstatementObj->transaction_ref_no = $collection_no;
            $pfstatementObj->transaction_date = $transactionDate;
            $pfstatementObj->for_the_month = $forTheMonth;
            $pfstatementObj->for_the_year = $forTheYear;
            $pfstatementObj->employee_contribution = $employee_contribution;
            $pfstatementObj->employer_contribution = $employer_contribution;

            /** Sum of PF Employee Conrubution */
            $pfstatementObj->total_employee_contribution = $total_employee_contribution;

            /** total_employer_contribution */
            $pfstatementObj->total_employer_contribution = $total_employer_contribution;

            $pfstatementObj->interest_accrued_employee_contribution = $interest_accrued_employee_contribution;
            $pfstatementObj->interest_accrued_employer_contribution = $interest_accrued_employer_contribution;

            /** Sum of PF Interest Employee Contribution */
            $pfstatementObj->total_interest_on_employee_contribution = $total_interest_on_employee_contribution;

            /** Sum of PF Interest Employer Contribution*/
            $pfstatementObj->total_interest_on_employer_contribution = $total_interest_on_employer_contribution;

            $pfstatementObj->interest_chargeable_amount_1 = $interest_chargeable_amount_1;
            $pfstatementObj->interest_chargeable_amount_2 = $interest_chargeable_amount_2;

            $pfstatementObj->gross_os_balance_employee = $gross_os_balance_employee;
            $pfstatementObj->gross_os_balance_employer = $gross_os_balance_employer;
            $pfstatementObj->transaction_version_no = (int)$transaction_version_no;
            $pfstatementObj->prev_total_disbursed_amount = (float)$previous_disbursed_amount;

            $pfstatementObj->ref_interest_rate = $interest_rate;
            $pfstatementObj->created_date = $created_date;
            $pfstatementObj->created_by = $created_by;
            $pfstatementObj->registration_type = 'GF';

            if (!$pfstatementObj->save()) {

                DB::rollBack();
                return 'error';
            }
        }

        $collection_account_transaction_id = $this->SaveCollectionAccountTransaction($deposit_ref_no, $total_refund_diff_excess, $transactionDate, $collection_no, $collection_branch_id);
        $interest_account_transaction_id = $this->SaveInterestTransaction($deposit_ref_no, $total_interest_accrued_employee,
            $total_interest_accrued_employer, $transactionDate, $collection_no, $collection_branch_id);

        if ($collection_account_transaction_id == 'error' || $interest_account_transaction_id == 'error') {

            DB::rollBack();
            return 'error';
        }

        $current_transaction_data = DB::table("pfstatements")
            ->where("transaction_no", "=", $deposit_ref_no)
            ->where("registration_type", "=", 'GF')
            ->get();

        foreach ($current_transaction_data as $data) {

            $empRefId = $data->employee_ref_id;
            $employee_contribution = (float)$data->employee_contribution;
            $employer_contribution = (float)$data->employer_contribution;

            $interest_accrued_employee_contribution = (float)$data->interest_accrued_employee_contribution;
            $interest_accrued_employer_contribution = (float)$data->interest_accrued_employer_contribution;

            if ($this->saveCollectionAccountTransactionDetail($collection_account_transaction_id, $companyRefNo,
                    $empRefId, $deposit_ref_no, $employee_contribution, $collection_branch_id,
                    $employer_contribution, $collection_no) == 'error') {

                DB::rollBack();
                return 'error';
            }

            if ($this->saveInterestAccountTransactionDetail($interest_account_transaction_id, $companyRefNo, $empRefId,
                    $deposit_ref_no, $interest_accrued_employee_contribution, $collection_branch_id,
                    $interest_accrued_employer_contribution, $collection_no) == 'error') {

                DB::rollBack();
                return 'error';
            }
        }

        $collection_update = DB::table('pfcollections')
            ->where('pf_collection_id', '=', $collection_id)
            ->where('registration_type', '=', 'GF')
            ->where('pf_collection_effective_end_date', '=', NULL)
            ->update(['pf_collection_status' => 'Approved']);

        if ($collection_update) {

            DB::commit();
            return response()->json('success');
        } else {

            DB::rollBack();
            return 'error';
        }
    }

    public function SaveCollectionAccountTransaction($transaction_no, $total_refund_diff_excess, $transactionDate, $collection_no, $collection_branch_id)
    {

        $year = Carbon::now()->year;
        $currentYear = date('Y');
        $accountTransaction = new Accounttransaction();
        $accountTrasactionId = date('YmdH') . random_int(666666, 999999);
        $accountTransaction->account_transaction_id = $accountTrasactionId;
        $accountTransaction->account_voucher_type = 'SYS';
        $accountTransaction->account_voucher_date = $transactionDate;
        $accountTransaction->account_transaction_mode = 'Deposit';

        $accountTransaction->account_voucher_amount = $total_refund_diff_excess;
        $accountTransaction->account_voucher_narration = 'GF individual deposit by GF Department against collection no ' . $collection_no;

        $getAccountTransactionSerialNo = DB::table('masteridholders')
            ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
            ->where('id_type', '=', 'GF_Account_Transaction')
            ->where('registration_type', '=', 'GF')
            ->where('branch_id', '=', $collection_branch_id)
            ->where('f_year', '=', $year)
            ->get(['serial_no', 'branch_code'])
            ->first();

        $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
        $branch_code = $getAccountTransactionSerialNo->branch_code;
        $voucher_number = 'SYS/' . $currentYear . '/GF/' . $branch_code . '/' . $accountTransactionSerialNo;

        $accountTransaction->account_collection_instrument_no = NULL;
        $accountTransaction->account_cheque_date = NULL;
        $accountTransaction->account_collection_bank = NULL;
        $accountTransaction->account_voucher_number = $voucher_number;

        $accountTransaction->account_payment_id = NULL;
        $accountTransaction->account_collection_id = NULL;
        $accountTransaction->account_reference_no = $transaction_no;
        $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
        $accountTransaction->account_effective_end_date = NULL;
        $accountTransaction->account_created_by = auth('api')->user()->name;
        $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');
        $accountTransaction->registration_type = 'GF';

        if ($accountTransaction->save()) {

            DB::table('masteridholders')
                ->where('branch_id', '=', $collection_branch_id)
                ->where('f_year', '=', $year)
                ->where('id_type', '=', 'GF_Account_Transaction')
                ->where('registration_type', '=', 'GF')
                ->update(['serial_no' => $accountTransactionSerialNo]);

            return $accountTrasactionId;

        } else {

            return 'error';

        }
    }

    public function SaveInterestTransaction($transaction_no, $total_interest_accrued_employee, $total_interest_accrued_employer,
                                            $transactionDate, $collection_no, $collection_branch_id)
    {

        $year = Carbon::now()->year;
        $currentYear = date('Y');

        $accountTransaction = new Accounttransaction();
        $accountTrasactionId = date('YmdH') . random_int(666666, 999999);
        $accountTransaction->account_transaction_id = $accountTrasactionId;
        $accountTransaction->account_voucher_type = 'SYS';
        $accountTransaction->account_voucher_date = $transactionDate;
        $accountTransaction->account_transaction_mode = 'Deposit';

        $accountTransaction->account_voucher_amount = $total_interest_accrued_employee + $total_interest_accrued_employer;
        $accountTransaction->account_voucher_narration = 'GF individual Interest deposit by GF Section against collection no ' . $collection_no;

        $getAccountTransactionSerialNo = DB::table('masteridholders')
            ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
            ->where('id_type', '=', 'GF_Account_Transaction')
            ->where('registration_type', '=', 'GF')
            ->where('branch_id', '=', $collection_branch_id)
            ->where('f_year', '=', $year)
            ->get(['serial_no', 'branch_code'])
            ->first();

        $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
        $branch_code = $getAccountTransactionSerialNo->branch_code;
        $voucher_number = 'SYS/' . $currentYear . '/GF/' . $branch_code . '/' . $accountTransactionSerialNo;

        $accountTransaction->account_collection_instrument_no = NULL;
        $accountTransaction->account_cheque_date = NULL;
        $accountTransaction->account_collection_bank = NULL;
        $accountTransaction->account_voucher_number = $voucher_number;

        $accountTransaction->account_payment_id = NULL;
        $accountTransaction->account_collection_id = NULL;
        $accountTransaction->account_reference_no = $transaction_no;

        $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
        $accountTransaction->account_effective_end_date = NULL;
        $accountTransaction->account_created_by = auth('api')->user()->name;
        $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');
        $accountTransaction->registration_type = 'GF';

        if ($accountTransaction->save()) {

            DB::table('masteridholders')
                ->where('branch_id', '=', $collection_branch_id)
                ->where('f_year', '=', $year)
                ->where('id_type', '=', 'GF_Account_Transaction')
                ->where('registration_type', '=', 'GF')
                ->update(['serial_no' => $accountTransactionSerialNo]);

            return $accountTrasactionId;

        } else {

            return 'error';
        }
    }

    public function saveCollectionAccountTransactionDetail($accountTrasactionId, $companyRefNo, $empRefId, $transaction_no,
                                                           $employee_contribution, $collection_branch_id,
                                                           $employer_contribution, $collection_no)
    {
        $transactionCategory = ['Received', 'Collection'];

        foreach ($transactionCategory as $transaction) {

            $saveAccountTransactionDetail = new Accounttransactiondetail();
            $genAccountTransactionId = date('YmdH') . random_int(666666, 999999);
            $saveAccountTransactionDetail->acc_transaction_detail_id = $genAccountTransactionId;
            $saveAccountTransactionDetail->acc_transaction_type_id = $accountTrasactionId;
            $saveAccountTransactionDetail->registration_type = 'GF';

            if ($transaction == 'Received') {

                $saveAccountTransactionDetail->acc_account_group_id = '9F1D02A0-421A-11EC-92A6-BB4CCAAF9B33';
                $saveAccountTransactionDetail->acc_account_type_id = '379A35D0-C3BB-11EC-B51B-79FB79A98A69';
                $saveAccountTransactionDetail->acc_debit_amount = $employee_contribution + $employer_contribution;
                $saveAccountTransactionDetail->acc_credit_amount = 0;
                $saveAccountTransactionDetail->acc_narration = 'Individual GF Contribution deposited against collection no ' . $collection_no;

            } else {

                $saveAccountTransactionDetail->acc_account_group_id = '9F1D02A0-421A-11EC-92A6-BB4CCAAF9B33';
                $saveAccountTransactionDetail->acc_account_type_id = 'A8CE35A0-C3BB-11EC-BBC4-5976BCDF5AAA';
                $saveAccountTransactionDetail->acc_debit_amount = 0;
                $saveAccountTransactionDetail->acc_credit_amount = $employee_contribution + $employer_contribution;
                $saveAccountTransactionDetail->acc_narration = 'Individual GF Contribution deposited against collection no ' . $collection_no;

            }

            $saveAccountTransactionDetail->acc_reference_no = $transaction_no;
            $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;
            $saveAccountTransactionDetail->acc_company_id = $companyRefNo;
            $saveAccountTransactionDetail->acc_employee_id = $empRefId;
            $saveAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
            $saveAccountTransactionDetail->acc_effective_end_date = NULL;
            $saveAccountTransactionDetail->acc_td_branch_id = $collection_branch_id;

            if (!$saveAccountTransactionDetail->save()) {
                return 'error';
            }
        }
        return 'success';
    }

    public function saveInterestAccountTransactionDetail($accountTrasactionId, $companyRefNo, $empRefId, $transaction_no,
                                                         $interest_accrued_employee_contribution, $collection_branch_id,
                                                         $interest_accrued_employer_contribution, $collection_no)
    {

        $transactionCategory = ['InterestPaid', 'InterestPayable'];

        foreach ($transactionCategory as $transaction) {

            $saveAccountTransactionDetail = new Accounttransactiondetail();
            $genAccountTransactionId = date('YmdH') . random_int(666666, 999999);
            $saveAccountTransactionDetail->acc_transaction_detail_id = $genAccountTransactionId;
            $saveAccountTransactionDetail->acc_transaction_type_id = $accountTrasactionId;

            if ($transaction == 'InterestPaid') {

                // GF Interest Paid Account
                $saveAccountTransactionDetail->acc_account_group_id = '5A3C8E80-45DE-11EC-A079-D9F2813EF00D';
                $saveAccountTransactionDetail->acc_account_type_id = '02438250-C3BC-11EC-A142-EF0DDDCC0F8E';
                $saveAccountTransactionDetail->acc_debit_amount = $interest_accrued_employee_contribution + $interest_accrued_employer_contribution;
                $saveAccountTransactionDetail->acc_credit_amount = 0;
                $saveAccountTransactionDetail->acc_narration = 'Interest accrued paid against Individual contribution for collection no ' . $collection_no;

            } else {

                // GF Interest Payable Account
                $saveAccountTransactionDetail->acc_account_group_id = '785E1CF0-45DE-11EC-A4C9-0D3C9D51C511';
                $saveAccountTransactionDetail->acc_account_type_id = '50115C20-C3BC-11EC-8808-D9088D57FFC1';
                $saveAccountTransactionDetail->acc_debit_amount = 0;
                $saveAccountTransactionDetail->acc_credit_amount = $interest_accrued_employee_contribution + $interest_accrued_employer_contribution;;
                $saveAccountTransactionDetail->acc_narration = 'Interest accrued paid against Individual contribution for collection no ' . $collection_no;
            }

            $saveAccountTransactionDetail->acc_reference_no = $transaction_no;
            $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;
            $saveAccountTransactionDetail->acc_company_id = $companyRefNo;
            $saveAccountTransactionDetail->acc_employee_id = $empRefId;
            $saveAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
            $saveAccountTransactionDetail->acc_effective_end_date = NULL;
            $saveAccountTransactionDetail->acc_td_branch_id = $collection_branch_id;
            $saveAccountTransactionDetail->registration_type = 'GF';

            if (!$saveAccountTransactionDetail->save()) {

                return 'error';
            }
        }
        return 'success';
    }
}
