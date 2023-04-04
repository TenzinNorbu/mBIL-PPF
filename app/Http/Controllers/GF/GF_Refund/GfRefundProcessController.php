<?php

namespace App\Http\Controllers\GF\GF_Refund;

use App\Http\Controllers\Controller;
use App\Models\Accounttransaction;
use App\Models\Accounttransactiondetail;
use App\Models\Branch;
use App\Models\Companyregistration;
use App\Models\Contactperson;
use App\Models\Document;
use App\Models\Pfcollection;
use App\Models\Pfemployeeregistration;
use App\Models\Pfmoudetail;
use App\Models\Pfstatement;
use App\Models\Proprietordetail;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mail;
use NumberToWords\NumberToWords;

class GfRefundProcessController extends Controller
{
   function __construct()
   {
       $this->middleware('permission:refund-process', ['only' => ['refundProcess']]);
   }

    public function refundProcess(Request $request)
    {
        DB::beginTransaction();
        $refund = new Refund();

        $refundRefNo = 'REF-GF' . date('Ymd') . Carbon::now()->format('His') . random_int(2222222222, 9999999999);

        $refund->refund_ref_no = $refundRefNo;
        $refund->refund_company_id = $request->refund_company_id;
        $refund->refund_employee_id = $request->refund_employee_id;
        $refund->refund_employee_cid = $request->refund_employee_cid;
        $refund->refund_processing_date = $request->refund_processing_date;

        $pending_collection_data = Pfcollection::where('pf_collection_company_account_no_id', '=', $request->refund_company_id)
            ->where('pf_collection_effective_end_date', '=', NULL)
            ->where('registration_type', '=', 'GF')
            ->where('pf_collection_status', '=', 'under_process')->get()->first();
            

        if ($pending_collection_data != null && $pending_collection_data != '') {

            return response()->json(['error', 'message' => 'Pending collection. Please check and complete the transaction first!']);
        }

        /** New fields for refund  */
        $refund_remarks = str_replace(PHP_EOL, "\n", $request->refund_remarks);
        $refund->refund_processed_remarks = $refund_remarks;

        $refund->refund_approved_remarks = NULL;
        $refund->refund_payment_date = $request->refund_payment_date;
        $refund->refund_payment_processed_by = auth('api')->user()->name;
        $refund->refund_payment_remarks = NULL;

        $getProcessingDate = $request->refund_processing_date;
        $forTheYear = Carbon::createFromFormat('Y-m-d', $getProcessingDate)->format('Y');
        $forTheMonth = Carbon::createFromFormat('Y-m-d', $getProcessingDate)->format('m');
        $refund->refund_processed_by = auth('api')->user()->name;

        $request->file('refund_file_name');

        // Screen 2
        $refund->refund_approval_date = NULL;
        $refund->refund_approved_by = NULL;
        $refund->refund_status = 'Processed';

        $emp_account_no = $request->pf_emp_acc_no;

        $refund_net_refundable = $request->refund_net_refundable;
        $total_refund_amount = $request->refund_amount;
        $total_prev_disbursed_amount = $request->refund_total_disbursed_amount;
        $totalRefundAmount = (float)$total_refund_amount + (float)$total_prev_disbursed_amount;

        $refund_process_employee = 0;
        $refund_process_employer = (float)$total_refund_amount;

        if ((float)$total_refund_amount <= 0 || (float)$refund_net_refundable <= 0) {
            return response()->json(['error', 'message' => 'The refundable amount is zero, Please try again!!', 'amount' => $total_refund_amount]);
        }

        $last_tran_statement_data = Pfstatement::where('employee_ref_id', '=', $request->refund_employee_id)
            ->where('registration_type', '=', 'GF')
            ->orderBy('transaction_version_no', 'DESC')
            ->get()->first();

        $days = round(((strtotime($request->refund_processing_date) - strtotime($last_tran_statement_data->transaction_date)) / 24 / 3600), 0);

        if ($days < 0) {

            return response()->json(['error', 'message' => 'Processing Date cannot be latter than Last Transaction Date, Please try again']);
        }

        $total_prev_disbursed_employee = 0;
        $total_prev_disbursed_employer = (float)$total_prev_disbursed_amount;

        $as_on_date_interest_1 = $request->refund_as_on_interest_employee;
        $as_on_date_interest_2 = $request->refund_as_on_interest_employer;

        $net_amount_1 = $last_tran_statement_data->interest_chargeable_amount_1;
        $net_amount_2 = $last_tran_statement_data->interest_chargeable_amount_2;
        $gross_amount_1 = $last_tran_statement_data->gross_os_balance_employee;
        $gross_amount_2 = $last_tran_statement_data->gross_os_balance_employer;

        $total_gross_amount = (float)$gross_amount_1 + (float)$gross_amount_2;

        $total_accumulated_employee_contribution = $last_tran_statement_data->total_employee_contribution;
        $total_accumulated_employer_contribution = $last_tran_statement_data->total_employer_contribution;

        $overall_refund_amount_employee = $total_prev_disbursed_employee + $refund_process_employee;
        $overall_refund_amount_employer = $total_prev_disbursed_employer + $refund_process_employer;

        if ((int)($total_gross_amount + $as_on_date_interest_1 + $as_on_date_interest_2) < (int)$total_refund_amount) {

            return response()->json(['error', 'message' => 'Total refund amount is more than refundable amount', 'db os' => $total_gross_amount, 'ui os' => $total_refund_amount]);

        } else {

            $gross_amount_1 = round(((float)$gross_amount_1 + (float)$as_on_date_interest_1 - $refund_process_employee), 2);
            $gross_amount_2 = round(((float)$gross_amount_2 + (float)$as_on_date_interest_2 - $refund_process_employer), 2);

            $net_amount_1 = round(((float)$net_amount_1 - $refund_process_employee), 2);
            $net_amount_2 = round(((float)$net_amount_2 - $refund_process_employer), 2);
        }
        if ((int)$net_amount_1 < 0) {
            $net_amount_1 = 0;
        }
        if ((int)$net_amount_2 < 0) {
            $net_amount_2 = 0;
        }
        if ((int)$gross_amount_1 < 0) {
            $gross_amount_1 = 0;
        }
        if ((int)$gross_amount_2 < 0) {
            $gross_amount_2 = 0;
        }

        // Employee
        if ($overall_refund_amount_employee <= (float)$total_accumulated_employee_contribution) {

            $refundable_contribution_employee = $refund_process_employee;
            $refundable_interest_employee = 0;

        } else {

            $refundable_interest_employee = $overall_refund_amount_employee - (float)$total_accumulated_employee_contribution;
            $refundable_contribution_employee = $refund_process_employee - $refundable_interest_employee; // 0.81

            if ($refundable_contribution_employee < 0) {

                $refundable_interest_employee = $refundable_interest_employee + $refundable_contribution_employee; // .81
                $refundable_contribution_employee = 0;
            }
        }

        //Employer
        if ($overall_refund_amount_employer < (float)$total_accumulated_employer_contribution) {

            $refundable_contribution_employer = $refund_process_employer;
            $refundable_interest_employer = 0;

        } else {

            $refundable_interest_employer = $overall_refund_amount_employer - (float)$total_accumulated_employer_contribution; 
            $refundable_contribution_employer = $refund_process_employer - $refundable_interest_employer;

            if ($refundable_contribution_employer < 0) {

                $refundable_interest_employer = $refundable_interest_employer + $refundable_contribution_employer;
                $refundable_contribution_employer = 0;
            }
        }

        $total_refund_contribution = (float)$refundable_contribution_employee + (float)$refundable_contribution_employer;

        $refund->refund_employee_contribution = (float)$refundable_contribution_employee;
        $refund->refund_employer_contribution = (float)$refundable_contribution_employer; 
        
        if((float)$refundable_interest_employer == '0'){

            $refund->refund_as_on_interest_employee = 0;
            $refund->refund_as_on_interest_employer = 0;
            $refund->refund_interest_on_employee_contr = (float)$refundable_interest_employee;
            $refund->refund_interest_on_employer_contr = (float)$refundable_interest_employer;
           
            $total_refund_interest = 0;

        }else{

            $refund->refund_as_on_interest_employee = (float)$as_on_date_interest_1;
            $refund->refund_as_on_interest_employer = (float)$as_on_date_interest_2;
            
            $refund->refund_interest_on_employee_contr = (float)$refundable_interest_employee - (float)$as_on_date_interest_1;
            $refund->refund_interest_on_employer_contr = (float)$refundable_interest_employer - (float)$as_on_date_interest_2;
           
            $total_refund_interest = (float)$refundable_interest_employee + (float)$refundable_interest_employer;
            // (float)$as_on_date_interest_1 + (float)$as_on_date_interest_2
        }

        $refund->refund_total_contr = $total_refund_contribution;
        $refund->refund_total_interest = $total_refund_interest;  // 3 + 4 + 5 + 6  = 8

        $refund->refund_total_disbursed_amount = (float)$total_refund_amount;

        $refund->refund_net_refundable = (float)$request->refund_net_refundable;  //  7 + 8

        $refund->reg_branch_id = $request->reg_branch_id;
        $refund->registration_type = 'GF';

        $contactMailList = Contactperson::where('contact_person_company_id', '=', $request->refund_company_id)
            ->where('effective_end_date', '=', NULL)->get();

        $proprietorMailList = Proprietordetail::where('prop_company_id', '=', $request->refund_company_id)
            ->where('effective_end_date', '=', NULL)->get();


        $company_data = Companyregistration::where('company_id', '=', $request->refund_company_id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)->get()->first();

        $emp_data = Pfemployeeregistration::where('pf_employee_id', '=', $request->refund_employee_id)
            ->where('effective_end_date', '=', NULL)
            ->where('registration_type', '=', 'GF')->get()->first();

        $employeeName = $emp_data->employee_name;
        $companyName = $company_data->org_name;


        if (count($contactMailList) > 0 || count($proprietorMailList) > 0) {

            foreach ($contactMailList as $contact_data) {
                foreach ($proprietorMailList as $proprietor_data) {
                    $proprietor_email = $proprietor_data->email_id;

                    //** Mail */
                    $details = array(
                        'title' => 'Refund Request Letter',
                        'employee_name' => $employeeName,
                        'company_name' => $companyName,
                        'employee_account_no' => $emp_account_no
                    );

                    try {
                        $contactEmail = $contact_data->email_id;

                        Mail::send('emails.gf_refundprocess', $details, function ($message) use ($contactEmail, $proprietor_email) {

                            $message->from('info.bhutaninsurance@gmail.com', 'PF/GF SYSTEM [BIL]');
                            $message->to($contactEmail);
                            $message->cc($proprietor_email);
                            $message->subject('Refund Process Request');
                        });

                    } catch (\Exception $e) {
                        //never reach
                    }
                    //** Mail end */
                }

            }

        } else {

            $contactEmail = '';
            $proprietor_email = '';

            //** Mail */
            $details = array(
                'title' => 'Refund Request Letter',
                'employee_name' => $employeeName,
                'company_name' => $companyName,
                'employee_account_no' => $emp_account_no
            );

            try {
                Mail::send('emails.gf_refundprocess', $details, function ($message) use ($contactEmail, $proprietor_email) {
                    $message->from('info.bhutaninsurance@gmail.com', 'PF/GF SYSTEM [BIL]');
                    $message->to($contactEmail);
                    $message->cc($proprietor_email);
                    $message->subject('Refund Process Request');
                });

            } catch (\Exception $e) {
                //never reach
            }
            //** Mail end */
        }

        if ($refund->save()) {

            DB::table('pfemployeeregistrations')
                ->where('effective_end_date', '=', NULL)
                ->where('registration_type', '=', 'GF')
                ->where('pf_employee_id', '=', $request->refund_employee_id)
                ->update(['pf_emp_acc_no' => $emp_account_no]);


            if ($this->saveintopfindividualdeposit($request, $refundRefNo, $forTheYear, $forTheMonth, $as_on_date_interest_1,
                    $as_on_date_interest_2, $net_amount_1, $net_amount_2, $gross_amount_1, $gross_amount_2, $total_refund_contribution,
                    $total_refund_interest, $totalRefundAmount) == 'error') {

                DB::rollBack();
                return response()->json(['error', 'message' => 'Unable to save data in pf statement']);

            } else {

                if ($request->hasFile('refund_file_name')) {

                    $save_doc_upload = $this->refundProcessFileUpload($request, $refundRefNo);

                } else {
                    $save_doc_upload = 'success';
                }

                $userData = auth('api')->user();
                $userName = $userData->name;
                $userBranch = Branch::where('id', '=', $userData->users_branch_id)->get()->first()->branch_name;
                $userBranch_id = Branch::where('id', '=', $userData->users_branch_id)->get()->first()->id;

                $save_refund_slip = $this->createRefundProcessedDocument($request, $refundRefNo, $refund_remarks, $userName, $userBranch, $userBranch_id);

                if ((int)$gross_amount_1 <= 0 && (int)$gross_amount_2 <= 0) {

                    Pfemployeeregistration::where('pf_employee_id', '=', $request->refund_employee_id)
                        ->where('pf_employee_company_id', '=', $request->refund_company_id)
                        ->where('effective_end_date', '=', NULL)
                        ->where('registration_type', '=', 'GF')
                        ->update(['status' => 'Closed']);
                }

                if ($save_doc_upload == 'error' || $save_refund_slip == 'error') {

                    DB::rollBack();
                    return response()->json(['error', 'message' => 'unable to save files an documents']);
                }

                DB::commit();
                return response()->json('success');
            }

        } else {
            return response()->json(['error', 'message' => 'Unable to save data in refund statement']);
        }

    }

    /** Insert into PF-Statement */
    public function saveintopfindividualdeposit(Request $request, $refundRefNo, $forTheYear, $forTheMonth, $as_on_date_interest_1, $as_on_date_interest_2, $net_amount_1, $net_amount_2, $gross_amount_1, $gross_amount_2, $total_refund_contribution, $total_refund_interest, $totalRefundAmount)
    {
        $emp_data = Pfstatement::where('employee_ref_id', '=', $request->refund_employee_id)
            ->where('registration_type', '=', 'GF')
            ->orderBy('transaction_version_no', 'DESC')
            ->get()->first();

        $mou_data = Pfmoudetail::where('pfmou_company_id', '=', $emp_data->company_ref_id)
            ->where('effective_end_date', '=', NULL)
            ->where('registration_type', '=', 'GF')
            ->get()->first();

        $pfstatementObj = new Pfstatement();
        $deposit_ref_no = date('Ymd') . rand(pow(10, 9 - 1), pow(10, 9) - 1);
        $pfstatementObj->transaction_no = $deposit_ref_no;

        $pfstatementObj->company_ref_id = $request->refund_company_id;
        $pfstatementObj->employee_ref_id = $request->refund_employee_id;

        $pfstatementObj->transaction_type = 'Refund';
        $pfstatementObj->transaction_ref_no = $refundRefNo;
        $pfstatementObj->transaction_date = $request->refund_processing_date;

        $pfstatementObj->for_the_month = $forTheMonth;
        $pfstatementObj->for_the_year = $forTheYear;

        $pfstatementObj->employee_contribution = 0;
        $pfstatementObj->employer_contribution = 0;

        $pfstatementObj->total_employee_contribution = (float)$emp_data->total_employee_contribution;
        $pfstatementObj->total_employer_contribution = (float)$emp_data->total_employer_contribution;

        $pfstatementObj->interest_accrued_employee_contribution = (float)$as_on_date_interest_1;
        $pfstatementObj->interest_accrued_employer_contribution = (float)$as_on_date_interest_2;

        $pfstatementObj->total_interest_on_employee_contribution = (float)$emp_data->total_interest_on_employee_contribution + (float)$as_on_date_interest_1;
        $pfstatementObj->total_interest_on_employer_contribution = (float)$emp_data->total_interest_on_employer_contribution + (float)$as_on_date_interest_2;

        $pfstatementObj->interest_chargeable_amount_1 = (float)$net_amount_1;
        $pfstatementObj->interest_chargeable_amount_2 = (float)$net_amount_2;

        $pfstatementObj->gross_os_balance_employee = $gross_amount_1;
        $pfstatementObj->gross_os_balance_employer = $gross_amount_2;

        $pfstatementObj->transaction_version_no = $emp_data->transaction_version_no + 1;

        $pfstatementObj->ref_interest_rate = $mou_data->interest_rate;
        $pfstatementObj->prev_total_disbursed_amount = (float)$totalRefundAmount;
        $pfstatementObj->created_date = Carbon::now()->format('Y-m-d');
        $pfstatementObj->created_by = auth('api')->user()->name;
        $pfstatementObj->registration_type = 'GF';

        if ($pfstatementObj->save()) {

            if ($this->SaveRefundInterestAcruedTransaction($deposit_ref_no, $refundRefNo, $as_on_date_interest_1,
                    $as_on_date_interest_2, $request->refund_processing_date,
                    $request->reg_branch_id, $request->refund_company_id,
                    $request->refund_employee_id) == 'error') {

                return 'error';

            } else {

                $this->SaveRefundCollectionAccountTransaction($deposit_ref_no, $refundRefNo, $total_refund_contribution,
                    $total_refund_interest, $request->refund_processing_date, $request->reg_branch_id,
                    $request->refund_company_id, $request->refund_employee_id);
            }
        } else {

            return 'error';
        }

        return 'success';
    }

    public function SaveRefundInterestAcruedTransaction($deposit_ref_no, $refundRefNo, $refund_as_on_interest_employee,
                                                        $refund_as_on_interest_employer, $transactionDate, $ref_branch_id,
                                                        $companyRefNo, $empRefId)
    {

        $accountTransaction = new Accounttransaction();
        $accountTrasactionId = date('Ymd') . rand(pow(10, 9 - 1), pow(10, 9) - 1);
        $accountTransaction->account_transaction_id = $accountTrasactionId;
        $accountTransaction->account_voucher_type = 'SYS';
        $accountTransaction->account_voucher_date = $transactionDate;
        $accountTransaction->account_transaction_mode = 'Deposit';
        $accountTransaction->registration_type = 'GF';

        $accountTransaction->account_voucher_amount = (float)$refund_as_on_interest_employee + (float)$refund_as_on_interest_employer;
        $accountTransaction->account_voucher_narration = 'Interest Refund by the GF Dept against the Refund-Ref-No : ' . $refundRefNo;

        $year = date('Y');
        $getAccountTransactionSerialNo = DB::table('masteridholders')
            ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
            ->where('id_type', 'GF_Account_Transaction')
            ->where('masteridholders.registration_type', '=', 'GF')
            ->where('branch_id', $ref_branch_id)
            ->where('f_year', $year)
            ->get(['serial_no', 'branch_code'])
            ->first();

        $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
        $branch_code = $getAccountTransactionSerialNo->branch_code;
        $voucher_number = 'SYS/GF/' . $year . '/' . $branch_code . '/' . $accountTransactionSerialNo;

        $accountTransaction->account_collection_instrument_no = NULL;
        $accountTransaction->account_cheque_date = NULL;
        $accountTransaction->account_collection_bank = NULL;
        $accountTransaction->account_voucher_number = $voucher_number;

        $accountTransaction->account_payment_id = NULL;
        $accountTransaction->account_collection_id = NULL;
        $accountTransaction->account_reference_no = $deposit_ref_no;
        $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
        $accountTransaction->account_effective_end_date = NULL;
        $accountTransaction->account_created_by = auth('api')->user()->name;
        $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');

        $year = Carbon::now()->year;
        if ($accountTransaction->save()) {

            DB::table('masteridholders')
                ->where('branch_id', '=', $ref_branch_id)
                ->where('f_year', '=', $year)
                ->where('id_type', '=', 'GF_Account_Transaction')
                ->where('registration_type', '=', 'GF')
                ->update(['serial_no' => $accountTransactionSerialNo]);

            if ($this->saveRefundInterestAccountTransactionDetail($accountTrasactionId, $deposit_ref_no,
                    $refundRefNo, $refund_as_on_interest_employee, $refund_as_on_interest_employer,
                    $companyRefNo, $empRefId, $ref_branch_id) == 'error') {

                return 'error';
            }
        } else {

            return 'error';
        }
        return 'success';
    }

    public function saveRefundInterestAccountTransactionDetail($accountTrasactionId, $deposit_ref_no, $refundRefNo,
                                                               $refund_as_on_interest_employee, $refund_as_on_interest_employer,
                                                               $companyRefNo, $empRefId, $ref_branch_id)
    {

        $transactionCategorySave = ['InterestPaid', 'InterestPayableAc'];

        foreach ($transactionCategorySave as $transactionId) {

            $saveAccountTransactionDetail = new Accounttransactiondetail();
            $genAccountTransactiondetailsId = date('Ymd') . rand(pow(10, 9 - 1), pow(10, 9) - 1);
            $saveAccountTransactionDetail->acc_transaction_detail_id = $genAccountTransactiondetailsId;
            $saveAccountTransactionDetail->acc_transaction_type_id = $accountTrasactionId;
            $saveAccountTransactionDetail->registration_type = 'GF';

            if ($transactionId == 'InterestPaid') {

                $saveAccountTransactionDetail->acc_account_group_id = '5A3C8E80-45DE-11EC-A079-D9F2813EF00D';
                $saveAccountTransactionDetail->acc_account_type_id = '02438250-C3BC-11EC-A142-EF0DDDCC0F8E';
                $saveAccountTransactionDetail->acc_debit_amount = $refund_as_on_interest_employee + $refund_as_on_interest_employer;
                $saveAccountTransactionDetail->acc_credit_amount = 0;
                $saveAccountTransactionDetail->acc_narration = 'Interest Paid CR against the Refund-No : ' . $refundRefNo;

            } else {

                $saveAccountTransactionDetail->acc_account_group_id = '785E1CF0-45DE-11EC-A4C9-0D3C9D51C511';
                $saveAccountTransactionDetail->acc_account_type_id = '50115C20-C3BC-11EC-8808-D9088D57FFC1';
                $saveAccountTransactionDetail->acc_debit_amount = 0;
                $saveAccountTransactionDetail->acc_credit_amount = $refund_as_on_interest_employee + $refund_as_on_interest_employer;
                $saveAccountTransactionDetail->acc_narration = 'GF Interest Payable DR against the Refund-No :  ' . $refundRefNo;
            }

            $saveAccountTransactionDetail->acc_reference_no = $deposit_ref_no;
            $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;
            $saveAccountTransactionDetail->acc_company_id = $companyRefNo;
            $saveAccountTransactionDetail->acc_employee_id = $empRefId;
            $saveAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
            $saveAccountTransactionDetail->acc_effective_end_date = NULL;
            $saveAccountTransactionDetail->acc_td_branch_id = $ref_branch_id;

            if (!$saveAccountTransactionDetail->save()) {

                return 'error';
            }
        }
        return 'success';
    }

    public function SaveRefundCollectionAccountTransaction($deposit_ref_no, $refundRefNo, $refundTotalContribution,
                                                           $refundTotalInterest, $transactionDate, $ref_branch_id,
                                                           $companyRefNo, $empRefId)
    {
        $year = Carbon::now()->year;
        $currentYear = date('Y');
        $accountTransaction = new Accounttransaction();
        $accountTrasactionId = date('Ymd') . rand(pow(10, 9 - 1), pow(10, 9) - 1);
        $accountTransaction->account_transaction_id = $accountTrasactionId;
        $accountTransaction->account_voucher_type = 'SYS';
        $accountTransaction->account_voucher_date = $transactionDate;
        $accountTransaction->account_transaction_mode = 'Refund';
        $accountTransaction->registration_type = 'GF';

        $total_refundable_amount = (float)$refundTotalContribution + (float)$refundTotalInterest;

        $accountTransaction->account_voucher_amount = $total_refundable_amount;
        $accountTransaction->account_voucher_narration = 'GF Refund Payment against Refund-No : ' . $refundRefNo;

        $getAccountTransactionSerialNo = DB::table('masteridholders')
            ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
            ->where('id_type', 'GF_Account_Transaction')
            ->where('registration_type', '=', 'GF')
            ->where('branch_id', $ref_branch_id)
            ->where('f_year', $year)
            ->get(['serial_no', 'branch_code'])
            ->first();

        $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
        $branch_code = $getAccountTransactionSerialNo->branch_code;
        $voucher_number = 'Refund/GF/' . $currentYear . '/' . $branch_code . '/' . $accountTransactionSerialNo;

        $accountTransaction->account_collection_instrument_no = null;
        $accountTransaction->account_cheque_date = null;
        $accountTransaction->account_collection_bank = null;
        $accountTransaction->account_voucher_number = $voucher_number;

        $accountTransaction->account_payment_id = null;
        $accountTransaction->account_collection_id = null;
        $accountTransaction->account_reference_no = $deposit_ref_no;
        $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
        $accountTransaction->account_effective_end_date = null;
        $accountTransaction->account_created_by = auth('api')->user()->name;
        $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');

        if ($accountTransaction->save()) {

            DB::table('masteridholders')
                ->where('branch_id', '=', $ref_branch_id)
                ->where('f_year', '=', $year)
                ->where('id_type', '=', 'GF_Account_Transaction')
                ->where('registration_type', '=', 'GF')
                ->update(['serial_no' => $accountTransactionSerialNo]);

            if ($this->saveRefundCollectionAccountTransactionDetail($accountTrasactionId, $deposit_ref_no, $refundRefNo, $refundTotalContribution,
                    $refundTotalInterest, $companyRefNo, $empRefId) == 'error') {

                return 'error';
            }

        } else {

            return 'error';
        }
        return 'success';
    }

    public function saveRefundCollectionAccountTransactionDetail($accountTrasactionId, $deposit_ref_no, $refundRefNo, $refundTotalContribution,
                                                                 $refundTotalInterest, $companyRefNo, $empRefId)
    {

        $transactionCategory = ['PfcollectionAcc', 'PfColRefundPayableAcc', 'PfInterestRefundPayableAcc', 'InterestPayableAcc'];

        foreach ($transactionCategory as $transaction) {

            $saveAccountTransactionDetail = new Accounttransactiondetail();
            $genAccountTransactionId = date('Ymd') . rand(pow(10, 9 - 1), pow(10, 9) - 1);

            $saveAccountTransactionDetail->acc_transaction_detail_id = $genAccountTransactionId;
            $saveAccountTransactionDetail->acc_transaction_type_id = $accountTrasactionId;
            $saveAccountTransactionDetail->registration_type = 'GF';

            if ($transaction == 'PfcollectionAcc') {

                $saveAccountTransactionDetail->acc_account_group_id = '9F1D02A0-421A-11EC-92A6-BB4CCAAF9B33';
                $saveAccountTransactionDetail->acc_account_type_id = 'A8CE35A0-C3BB-11EC-BBC4-5976BCDF5AAA';
                $saveAccountTransactionDetail->acc_debit_amount = (float)$refundTotalContribution;
                $saveAccountTransactionDetail->acc_credit_amount = 0;
                $saveAccountTransactionDetail->acc_narration = 'GF Refund contribution against Refund-No : ' . $refundRefNo;

            } elseif ($transaction == 'PfColRefundPayableAcc') {

                $saveAccountTransactionDetail->acc_account_group_id = '987A9B20-45DE-11EC-973C-47DC726D5DD3';
                $saveAccountTransactionDetail->acc_account_type_id = 'A2AC6540-C3BC-11EC-A548-07FC7107F9BF';
                $saveAccountTransactionDetail->acc_debit_amount = 0;
                $saveAccountTransactionDetail->acc_credit_amount = (float)$refundTotalContribution;
                $saveAccountTransactionDetail->acc_narration = 'GF Refund contribution payable against Refund-No : ' . $refundRefNo;

            } elseif ($transaction == 'PfInterestRefundPayableAcc') {

                $saveAccountTransactionDetail->acc_account_group_id = '987A9B20-45DE-11EC-973C-47DC726D5DD3';
                $saveAccountTransactionDetail->acc_account_type_id = '176CD330-C3BD-11EC-8C43-83D7FBDFBAA0';
                $saveAccountTransactionDetail->acc_debit_amount = 0;
                $saveAccountTransactionDetail->acc_credit_amount = (float)$refundTotalInterest;
                $saveAccountTransactionDetail->acc_narration = 'GF Refund Interest against Refund-No : ' . $refundRefNo;

            } else {  // InterestPayableAcc

                $saveAccountTransactionDetail->acc_account_group_id = '785E1CF0-45DE-11EC-A4C9-0D3C9D51C511';
                $saveAccountTransactionDetail->acc_account_type_id = '50115C20-C3BC-11EC-8808-D9088D57FFC1';
                $saveAccountTransactionDetail->acc_debit_amount = (float)$refundTotalInterest;
                $saveAccountTransactionDetail->acc_credit_amount = 0;
                $saveAccountTransactionDetail->acc_narration = 'GF Refund Interest payable against Refund-No ' . $refundRefNo;
            }

            $saveAccountTransactionDetail->acc_reference_no = $deposit_ref_no;
            $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;
            $saveAccountTransactionDetail->acc_company_id = $companyRefNo;
            $saveAccountTransactionDetail->acc_employee_id = $empRefId;
            $saveAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
            $saveAccountTransactionDetail->acc_effective_end_date = NULL;

            if (!$saveAccountTransactionDetail->save()) {
                return 'error';
            }
        }
        return 'success';
    }

    /** Refund Process Upload */
    public function refundProcessFileUpload(Request $request, $refundRefNo)
    {
        $refund_doc_save = new Document();
        $refund_doc_save->doc_type_id = 300000;
        $refund_doc_save->doc_ref_no = $refundRefNo;
        $refund_doc_save->doc_ref_type = 'Refund';
        $refund_doc_save->doc_date = Carbon::now()->format('Y-m-d');
        $refund_doc_save->registration_type = 'GF';

        $original_file_name = $request->file('refund_file_name')->getClientOriginalName();
        $file_extension = $request->file('refund_file_name')->getClientOriginalExtension();

        $filename = 'gf_refund_process_upload_' . $refundRefNo . '_' . $original_file_name;

        $refund_doc_save->doc_type = $file_extension;
        $refund_doc_save->doc_path = $filename;
        $refund_doc_save->doc_date = Carbon::now()->format('Y-m-d');
        $refund_doc_save->doc_user_id = auth('api')->user()->id;

        if ($refund_doc_save->save()) {

            $request->file('refund_file_name')->storeAs('/', $filename, 'refundslip');

            return 'success';

        } else {

            return 'error';
        }
    }

    /** Refund Process Slip */
    public function createRefundProcessedDocument(Request $request, $refundRefNo, $refund_remarks, $userName, $userBranch, $userBranch_id)
    {

        $getPfEmployeeDetails = Pfemployeeregistration::where('pf_employee_id', $request->refund_employee_id)
            ->where('effective_end_date', '=', NULL)
            ->where('registration_type', '=', 'GF')
            ->get(['employee_id_no', 'employee_name']);

        $getEmpCmpData = Companyregistration::where('company_id', $request->refund_company_id)
            ->where('effective_end_date', '=', NULL)
            ->where('registration_type', '=', 'GF')
            ->get()->first();

        $getPfCompanyName = $getEmpCmpData->org_name;
        $getPfEmployeeNo = $getPfEmployeeDetails->first()->employee_id_no;
        $getPfEmployeeName = $getPfEmployeeDetails->first()->employee_name;

        $refundProcessAmount = $request->refund_amount;

        $whole = intval($refundProcessAmount);
        $decimal1 = $refundProcessAmount - $whole;
        $decimal2 = round($decimal1, 2);
        $get_substring_value = substr($decimal2, 2);
        $convert_to_int = intval($get_substring_value);
        $f = new \NumberFormatter(locale_get_default(), \NumberFormatter::SPELLOUT);
        $word = $f->format($convert_to_int);

        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('en');
        $numWord = $numberTransformer->toWords($refundProcessAmount);
        $numInWords = $numWord . ' and chhetrum ' . $word;

        $year = Carbon::now()->year;
        $refund_process_data = DB::table('masteridholders')
            ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
            ->where('id_type', 'Refund_Processed')
            ->where('branch_id', $userBranch_id)
            ->where('f_year', $year)
            ->get(['serial_no', 'branch_code'])
            ->first();
        $refund_serial_no = (int)$refund_process_data->serial_no + 1;
        $refundRef_No = 'BIL/GF/' . $year . '/RF/' . $refund_serial_no;

        $data = [
            'refundRefNo' => $refundRef_No,
            'date' => $request->refund_processing_date,
            'refundProcessAmount' => $refundProcessAmount,
            'numInWords' => $numInWords,
            'pfEmployeeNo' => $getPfEmployeeNo,
            'empName' => $getPfEmployeeName,
            'companyName' => $getPfCompanyName,
            'refund_remarks' => $refund_remarks,
            'process_by_name' => $userName,
            'process_by_branch' => $userBranch
        ];

        $pdf = App::make('dompdf.wrapper');
        $bladeView = view('refundfiles.gf_refundprocess', $data);
        $pdf->loadHTML($bladeView);

        $genRefundSlipExtensionNo = date('Ymd') . rand(pow(10, 9 - 1), pow(10, 9) - 1);
        $currentDateTime = Carbon::now()->format('YmdHis');
        $fileName = 'gf_refund_process_slip_' . $genRefundSlipExtensionNo . '_' . $currentDateTime . '.pdf';

        if ($pdf->save(Storage::disk('refundslip')->put($fileName, $pdf->output()))) {

            DB::table('masteridholders')
                ->where('branch_id', '=', $userBranch_id)
                ->where('f_year', '=', $year)
                ->where('id_type', '=', 'Refund_Processed')
                ->update(['serial_no' => $refund_serial_no]);

            $save_doc_data = DB::table('documents')->insert([
                'doc_type_id' => 200000,
                'doc_ref_no' => $refundRefNo,
                'doc_ref_type' => 'Refund',
                'doc_type' => 'pdf',
                'doc_path' => $fileName,
                'registration_type' => 'GF',
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'doc_user_id' => auth('api')->user()->id
            ]);

            if (!$save_doc_data) {

                return 'error';
            }

        } else {

            return 'error';
        }

        return 'success';
    }
}
