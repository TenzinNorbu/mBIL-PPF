<?php

namespace App\Http\Controllers\GF;

use App\Http\Controllers\Controller;
use App\Models\Accounttransaction;
use App\Models\Accounttransactiondetail;
use App\Models\Companyregistration;
use App\Models\Pfemployeeregistration;
use App\Models\Pfstatement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GfMonthlyClosingController extends Controller
{
    public function MonthlyClosing(Request $request)
    {
        $closing_date = $request->closing_date;
        $getForTheMonth = $request->closing_month;
        $getForTheYear = $request->closing_year;

        $getActiveCompanyList = Companyregistration::with('getGfMouDetails')
            ->where('companyregistrations.registration_type', '=', 'GF')
            ->where('companyregistrations.closing_date', '=', NULL)
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->get('company_id');

        DB::beginTransaction();

        foreach ($getActiveCompanyList as $cmp_data) {

            $get_active_employee_list = Pfemployeeregistration::where('effective_end_date', '=', NULL)
                ->where('status', '=', 'Active')
                ->where('closing_date', '=', NULL)
                ->where('pf_employee_company_id', '=', "$cmp_data->company_id")
                ->where('registration_type', '=', "GF")
                ->get();

            foreach ($get_active_employee_list as $emp_data) {

                $get_transaction_details = Pfstatement::where('employee_ref_id', '=', "$emp_data->pf_employee_id")
                    ->where('company_ref_id', '=', "$cmp_data->company_id")
                    ->where('registration_type', '=', "GF")
                    ->whereRaw("transaction_version_no = (select max(transaction_version_no) from pfstatements where employee_ref_id = $emp_data->pf_employee_id)")
                    ->get()->first();

                $employeeContribution = 0;
                $employerContribution = 0;
                $ref_branch_id = 1;

                if ($get_transaction_details != '' && $get_transaction_details != NULL) {

                    $totalEmployeeContribution = (float)$get_transaction_details->total_employee_contribution;
                    $totalEmployerContribution = (float)$get_transaction_details->total_employer_contribution;

                    $totalInterestOnEmployee = (float)$get_transaction_details->total_interest_on_employee_contribution;
                    $totalInterestOnEmployer = (float)$get_transaction_details->total_interest_on_employer_contribution;

                    $interest_chargeable_amount01 = (float)$get_transaction_details->interest_chargeable_amount_1;
                    $interest_chargeable_amount02 = (float)$get_transaction_details->interest_chargeable_amount_2;

                    $gross_os_balance_employee = (float)$get_transaction_details->gross_os_balance_employee;
                    $gross_os_balance_employer = (float)$get_transaction_details->gross_os_balance_employer;

                    $ref_interest_rate = $cmp_data->getGfMouDetails->interest_rate;
                    $employeeName = $emp_data->employee_name;
                    $companyId = $emp_data->pf_employee_company_id;
                    $employeeId = $emp_data->pf_employee_id;

                    $no_of_days = round(((strtotime($closing_date) - strtotime($get_transaction_details->transaction_date)) / 24 / 3600), 0) + 1;

                    if ($this->savePFStatementClosingTags($companyId, $employeeId, $closing_date, $ref_branch_id, $getForTheMonth,
                            $getForTheYear, $ref_interest_rate, $employeeContribution, $employerContribution, $totalEmployeeContribution,
                            $totalEmployerContribution, $totalInterestOnEmployee, $totalInterestOnEmployer, $interest_chargeable_amount01,
                            $interest_chargeable_amount02, $gross_os_balance_employee, $gross_os_balance_employer, $employeeName, $no_of_days) == 'error') {

                        DB::rollBack();
                        return response()->json('error');
                    }
                }
            }
        }

        DB::commit();
        return response()->json('success');
    }

    public function savePFStatementClosingTags($companyId, $employeeId, $closing_date, $ref_branch_id, $getForTheMonth,
                                               $getForTheYear, $ref_interest_rate, $employeeContribution, $employerContribution,
                                               $totalEmployeeContribution, $totalEmployerContribution, $totalInterestOnEmployee,
                                               $totalInterestOnEmployer, $interest_chargeable_amount01, $interest_chargeable_amount02,
                                               $gross_os_balance_employee, $gross_os_balance_employer, $employeeName, $no_of_days)
    {

        $transaction_ref_no = date('Ymd') . random_int(11111111, 99999999);

        $interestAccruedEmployee = ($interest_chargeable_amount01 * $ref_interest_rate / 100 * $no_of_days) / 365;
        $interestAccruedEmployer = ($interest_chargeable_amount02 * $ref_interest_rate / 100 * $no_of_days) / 365;

        $int_chargeable_amt_1 = $interest_chargeable_amount01;
        $int_chargeable_amt_2 = $interest_chargeable_amount02;

        $outstanding_01 = $gross_os_balance_employee + $interestAccruedEmployee;
        $outstanding_02 = $gross_os_balance_employer + $interestAccruedEmployer;

        $int_outstanding_01 = $totalInterestOnEmployee + $interestAccruedEmployee;
        $int_outstanding_02 = $totalInterestOnEmployer + $interestAccruedEmployer;

        $statement_data = Pfstatement::where('employee_ref_id', '=', $employeeId)
            ->where('registration_type', '=', "GF")
            ->orderBy('transaction_version_no', 'DESC')
            ->get()->first();

        $transaction_version_no = (int)$statement_data->transaction_version_no;

        $pf_deposit = new Pfstatement();
        $pf_deposit->transaction_no = $transaction_ref_no;
        $pf_deposit->company_ref_id = $companyId;
        $pf_deposit->employee_ref_id = $employeeId;
        $pf_deposit->transaction_type = 'Closing';
        $pf_deposit->transaction_ref_no = NULL;
        $pf_deposit->transaction_date = $closing_date;
        $pf_deposit->for_the_month = $getForTheMonth;
        $pf_deposit->for_the_year = $getForTheYear;
        $pf_deposit->employee_contribution = 0;
        $pf_deposit->employer_contribution = 0;
        $pf_deposit->total_employee_contribution = $totalEmployeeContribution;
        $pf_deposit->total_employer_contribution = $totalEmployerContribution;
        $pf_deposit->interest_accrued_employee_contribution = $interestAccruedEmployee;
        $pf_deposit->interest_accrued_employer_contribution = $interestAccruedEmployer;

        $pf_deposit->total_interest_on_employee_contribution = (float)$int_outstanding_01;
        $pf_deposit->total_interest_on_employer_contribution = (float)$int_outstanding_02;

        $pf_deposit->interest_chargeable_amount_1 = (float)$int_chargeable_amt_1;
        $pf_deposit->interest_chargeable_amount_2 = (float)$int_chargeable_amt_2;

        $pf_deposit->gross_os_balance_employee = (float)$outstanding_01;
        $pf_deposit->gross_os_balance_employer = (float)$outstanding_02;

        $pf_deposit->ref_interest_rate = $ref_interest_rate;
        $pf_deposit->transaction_version_no = $transaction_version_no + 1;

        $pf_deposit->created_date = Carbon::now()->format('Y-m-d');
        $pf_deposit->created_by = auth('api')->user()->name;
        $pf_deposit->registration_type = 'GF';

        if (!$pf_deposit->save()) {

            return 'error';

        } else {

            if ($this->closingInterestAccountTransactions($transaction_ref_no, $companyId, $employeeId, $closing_date, $ref_branch_id,
                    $getForTheYear, $interestAccruedEmployee, $interestAccruedEmployer,
                    $employeeName) == 'error') {

                return 'error';

            } else {

                if ($this->savePFStatementOpeningTags($companyId, $employeeId, $transaction_ref_no, $closing_date, $getForTheMonth,
                        $getForTheYear, $totalEmployeeContribution, $totalEmployerContribution, $int_outstanding_01, $int_outstanding_02,
                        $int_chargeable_amt_1, $int_chargeable_amt_2, $outstanding_01, $outstanding_02, $ref_interest_rate,
                        $transaction_version_no) == 'error') {

                    return 'error';
                }

            }

        }

        return 'success';

    }

    //  Opening Tag begins
    public function savePFStatementOpeningTags($companyId, $employeeId, $transaction_ref_no, $closing_date, $getForTheMonth, $getForTheYear,
                                               $totalEmployeeContribution, $totalEmployerContribution, $int_outstanding_01, $int_outstanding_02,
                                               $int_chargeable_amt_1, $int_chargeable_amt_2,
                                               $outstanding_01, $outstanding_02, $ref_interest_rate, $transaction_version_no)
    {

        $opening_date = Carbon::createFromFormat('Y-m-d', $closing_date)->addDays(1);

        $pf_deposit = new Pfstatement();
        $pf_deposit->transaction_no = $transaction_ref_no;
        $pf_deposit->company_ref_id = $companyId;
        $pf_deposit->employee_ref_id = $employeeId;
        $pf_deposit->transaction_type = 'Opening';
        $pf_deposit->transaction_ref_no = NULL;
        $pf_deposit->transaction_date = $opening_date;
        $pf_deposit->for_the_month = (int)$getForTheMonth + 1;
        $pf_deposit->for_the_year = $getForTheYear;
        $pf_deposit->employee_contribution = 0;
        $pf_deposit->employer_contribution = 0;
        $pf_deposit->total_employee_contribution = $totalEmployeeContribution;
        $pf_deposit->total_employer_contribution = $totalEmployerContribution;
        $pf_deposit->interest_accrued_employee_contribution = 0;
        $pf_deposit->interest_accrued_employer_contribution = 0;

        $pf_deposit->total_interest_on_employee_contribution = (float)$int_outstanding_01;
        $pf_deposit->total_interest_on_employer_contribution = (float)$int_outstanding_02;

        $pf_deposit->interest_chargeable_amount_1 = (float)$int_chargeable_amt_1;
        $pf_deposit->interest_chargeable_amount_2 = (float)$int_chargeable_amt_2;

        $pf_deposit->gross_os_balance_employee = (float)$outstanding_01;
        $pf_deposit->gross_os_balance_employer = (float)$outstanding_02;

        $pf_deposit->ref_interest_rate = $ref_interest_rate;
        $pf_deposit->transaction_version_no = $transaction_version_no + 2;

        $pf_deposit->created_date = Carbon::now()->format('Y-m-d');
        $pf_deposit->created_by = auth('api')->user()->name;
        $pf_deposit->registration_type = 'GF';

        if (!$pf_deposit->save()) {

            return 'error';
        }

        return 'success';
    }

    public function closingInterestAccountTransactions($transaction_ref_no, $companyId, $employeeId, $closing_date, $ref_branch_id,
                                                       $getForTheYear, $interestAccruedEmployee, $interestAccruedEmployer, $employeeName)
    {
        $accountTransaction = new Accounttransaction();
        $accountTransactionId = date('Ymd') . random_int(11111111, 99999999);
        $accountTransaction->account_transaction_id = $accountTransactionId;
        $accountTransaction->account_voucher_type = 'Closing';
        $accountTransaction->account_voucher_date = Carbon::now()->format('Y-m-d');

        $accountTransaction->account_transaction_mode = NULL;
        $accountTransaction->account_voucher_amount = (float)$interestAccruedEmployee + (float)$interestAccruedEmployer;
        $accountTransaction->account_voucher_narration = 'GF Closing interest accrued payable against employee account no: ' . $employeeId;

        $getAccountTransactionSerialNo = DB::table('masteridholders')
            ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
            ->where('id_type', '=', 'GF_Sys_Transaction')
            ->where('branch_id', $ref_branch_id)
            ->where('f_year', $getForTheYear)
            ->get(['serial_no', 'branch_code'])
            ->first();

        $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
        $branch_code = $getAccountTransactionSerialNo->branch_code;

        $instrument_no = NULL;
        $cheque_date = NULL;
        $col_bank_name = NULL;
        $voucher_number = 'CL/GF/' . date('Y') . '/' . $branch_code . '/SYS/' . $accountTransactionSerialNo;

        $accountTransaction->account_voucher_number = $voucher_number;
        $accountTransaction->account_collection_instrument_no = $instrument_no;
        $accountTransaction->account_cheque_date = $cheque_date;
        $accountTransaction->account_collection_bank = $col_bank_name;

        $accountTransaction->account_payment_id = NULL;
        $accountTransaction->account_collection_id = NULL;

        $accountTransaction->account_reference_no = $transaction_ref_no;
        $accountTransaction->account_effective_start_date = $closing_date;
        $accountTransaction->account_effective_end_date = NULL;
        $accountTransaction->account_created_by = auth('api')->user()->name;
        $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');
        $accountTransaction->registration_type = 'GF';

        if ($accountTransaction->save()) {

            DB::table('masteridholders')
                ->where('branch_id', '=', $ref_branch_id)
                ->where('f_year', '=', $getForTheYear)
                ->where('id_type', '=', 'GF_Sys_Transaction')
                ->update(['serial_no' => $accountTransactionSerialNo]);

            if ($this->closingInterestAccountTransactionDetail($accountTransactionId, $transaction_ref_no, $companyId, $employeeId, $closing_date,
                    $interestAccruedEmployee, $interestAccruedEmployer, $employeeName) == 'error') {

                return 'error';
            }

        } else {

            return 'error';
        }
    }

    public function closingInterestAccountTransactionDetail($accountTransactionId, $transaction_ref_no, $companyId, $employeeId,
                                                            $closing_date, $interestAccruedEmployee,
                                                            $interestAccruedEmployer, $employeeName)
    {

        $closing_category = ['PPFInterestPaidAccount', 'PPFInterestPayableAccount'];

        foreach ($closing_category as $closing_data) {

            $saveAccountTransactionDetail = new Accounttransactiondetail();
            $genAccountTransactiondetailsId = date('Ymd') . random_int(11111111, 99999999);
            $saveAccountTransactionDetail->acc_transaction_detail_id = $genAccountTransactiondetailsId;
            $saveAccountTransactionDetail->acc_transaction_type_id = $accountTransactionId;
            $saveAccountTransactionDetail->registration_type = 'GF';

            if ($closing_data == 'PPFInterestPaidAccount') {

                // GF Interest Paid Account
                $saveAccountTransactionDetail->acc_account_group_id = '5A3C8E80-45DE-11EC-A079-D9F2813EF00D';
                $saveAccountTransactionDetail->acc_account_type_id = '02438250-C3BC-11EC-A142-EF0DDDCC0F8E';
                $saveAccountTransactionDetail->acc_debit_amount = $interestAccruedEmployee + $interestAccruedEmployer;
                $saveAccountTransactionDetail->acc_credit_amount = 0;
                $saveAccountTransactionDetail->acc_narration = 'GF Interest Paid  : ' . $employeeName;

            } else {

                // GF interest payable account
                $saveAccountTransactionDetail->acc_account_group_id = '785E1CF0-45DE-11EC-A4C9-0D3C9D51C511';
                $saveAccountTransactionDetail->acc_account_type_id = '50115C20-C3BC-11EC-8808-D9088D57FFC1';
                $saveAccountTransactionDetail->acc_debit_amount = 0;
                $saveAccountTransactionDetail->acc_credit_amount = $interestAccruedEmployee + $interestAccruedEmployer;
                $saveAccountTransactionDetail->acc_narration = 'GF Interest Payable  :  ' . $employeeName;
            }

            $saveAccountTransactionDetail->acc_reference_no = $transaction_ref_no;
            $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;
            $saveAccountTransactionDetail->acc_company_id = $companyId;
            $saveAccountTransactionDetail->acc_employee_id = $employeeId;
            $saveAccountTransactionDetail->acc_effective_start_date = $closing_date;
            $saveAccountTransactionDetail->acc_effective_end_date = NULL;
            $saveAccountTransactionDetail->acc_td_branch_id = 1;

            if (!$saveAccountTransactionDetail->save()) {

                return 'error';
            }
        }

        return 'success';
    }

}
