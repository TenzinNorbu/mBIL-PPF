<?php

namespace App\Http\Controllers\Refunds;

use App\Http\Controllers\Controller;
use App\Models\Accounttransaction;
use App\Models\Accounttransactiondetail;
use App\Models\Companyregistration;
use NumberToWords\NumberToWords;
use App\Models\Payment;
use App\Models\Paymentdetail;
use App\Models\Pfcollection;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class ExcessRefundPaymentController extends Controller
{
     // function __construct()
     // {
     //     $this->middleware('permission:refund-excess-payment', ['only' => ['createExcessRefundPayment']]);
     // }

    public function createExcessRefundPayment(Request $request)
    {
        $businessType = $request->registration_type;
        $companyId = $request->company_id;
        $collectionReceiptNo = $request->collection_receipt_no;
        $collectionReceiptAmount = $request->collection_receipt_amt;
        $collectionDate = $request->collection_date;
        $excessRefundAmount = $request->excess_refund_amt;
        $refundDate = $request->refund_date;
        $excessRefundBranchId = $request->branch_id;

        DB::beginTransaction();
        $excessRefund = new Refund();
        $excessRefundRefNo = 'ER' . date('Ymd') . random_int(666, 999);
        $excessRefund->refund_ref_no = $excessRefundRefNo;
        $excessRefund->refund_company_id = $companyId;
        $excessRefund->refund_employee_id = NULL;
        $excessRefund->refund_employee_cid = NULL;
        $excessRefund->refund_processing_date = $refundDate;
        $excessRefund->refund_processed_remarks = 'Excess Payment Refund';
        $excessRefund->refund_approved_remarks = NULL;
        $excessRefund->refund_payment_date = NULL;
        $excessRefund->refund_payment_processed_by = auth('api')->user()->name;
        $excessRefund->refund_payment_remarks = NULL;
        $excessRefund->refund_processed_by = auth('api')->user()->name;
        $excessRefund->refund_approval_date = NULL;
        $excessRefund->refund_approved_by = NULL;
        $excessRefund->refund_status = 'Approved';
        $excessRefund->refund_employee_contribution = 0;
        $excessRefund->refund_employer_contribution = 0;
        $excessRefund->refund_interest_on_employee_contr = 0;
        $excessRefund->refund_interest_on_employer_contr = 0;
        $excessRefund->refund_as_on_interest_employee = 0;
        $excessRefund->refund_as_on_interest_employer = 0;
        $excessRefund->refund_total_contr = 0;  //$excessRefundAmount;
        $excessRefund->refund_total_interest = 0;
        $excessRefund->refund_total_disbursed_amount = $excessRefundAmount;
        $excessRefund->refund_net_refundable = 0;
        $excessRefund->reg_branch_id = $excessRefundBranchId;
        $excessRefund->registration_type = $businessType;
        $excessRefund->col_ref_no_for_excess_refund = $collectionReceiptNo;
        $excessRefund->refund_bank_name = $request->bank_name;
        $excessRefund->refund_bank_account_no = $request->pf_emp_acc_no;


        if ($excessRefund->save()) {

            Pfcollection::where('pf_collection_no', '=', $collectionReceiptNo)
                ->update([
                    'excess_refund_amount' => $excessRefundAmount,
                    'excess_refund_ref_no' => $excessRefundRefNo,
                    'pf_collection_created_by' => auth('api')->user()->name,
                ]);

            if ($this->saveExcessRefundAccountTransactions($excessRefundRefNo, $refundDate, $businessType,
                    $excessRefundAmount, $excessRefundBranchId, $companyId) == 'success') {

                //** Payment Table */
                $refundApprovePayment = new Payment();
                $refundApproveId = random_int(666666, 999999);
                $refundApprovePayment->payment_advise_no = $refundApproveId;
                $refundApprovePayment->payment_company_id = $companyId;

                $refundApprovePayment->total_payable_amount = $excessRefundAmount;
                $refundApprovePayment->payment_status = 'RNP';
                $refundApprovePayment->payment_process_date = Carbon::now()->format('Y-m-d');
                $refundApprovePayment->registration_type = $businessType;

                if (!$refundApprovePayment->save()) {

                    DB::rollBack();
                    return response()->json(['error', 'message' => 'Excess Refund Approve Failed!']);

                } else {

                    $save_payment_detail = $this->ExcessRefundPaymentDetails($request, $refundApproveId, $companyId, $businessType, $excessRefundAmount, $excessRefundRefNo);

                    if ($save_payment_detail == 'error') {
                        DB::rollBack();
                        return response()->json(['error', 'message' => 'error']);

                    } else {
                        $doc_path = $this->generateDocForExcessPayment($businessType,$excessRefundRefNo,$refundDate,$excessRefundAmount,$companyId,$collectionReceiptNo,$refundApproveId,$request->pf_emp_acc_no, $request->bank_name);
                        DB::commit();
                        return response()->json(['success', 'message' => 'success']);
                    }
                }
                //** Excess Payment */

            } else {

                DB::rollBack();
                return response()->json(['error', 'message' => 'Could not Process the Excess Payment Refund']);
            }

        } else {

          DB::rollBack();
          return response()->json(['error', 'message' => 'Error processing the Excess Payment Refund']);
        }
    }

    public function ExcessRefundPaymentDetails(Request $request, $refundApproveId, $companyId, $businessType, $excessRefundAmount, $excessRefundRefNo)
    {

        $refundPaymentApproveDetails = new Paymentdetail();
        $refundPaymentApproveDetails->payment_advise_ref_no = $refundApproveId;
        $refundPaymentApproveDetails->payment_dtl_company_id = $companyId;
        $refundPaymentApproveDetails->payment_employee_id = NULL;
        $refundPaymentApproveDetails->payment_refund_ref_no = $excessRefundRefNo;

        // Newly Added Fields
        $refundPaymentApproveDetails->payment_contribution_employee = 0;
        $refundPaymentApproveDetails->payment_contribution_employer = 0;
        $refundPaymentApproveDetails->payment_interest_employee = 0;
        $refundPaymentApproveDetails->payment_interest_employer = 0;

        $refundPaymentApproveDetails->payment_contribution_amount = $excessRefundAmount;
        $refundPaymentApproveDetails->payment_interest_amount = 0;
        $refundPaymentApproveDetails->payment_total_amount = $excessRefundAmount;
        $refundPaymentApproveDetails->registration_type = $businessType;

        if (!$refundPaymentApproveDetails->save()) {

            return 'error';

        } else {

            $refund_approval = DB::table('refunds')
                ->where('refund_company_id', '=', $companyId)
                ->where('registration_type', '=', $businessType)
                ->update([
                    'refund_status' => 'Approved',
                    'payment_advise_ref_no' => $refundApproveId,
                    'refund_approval_date' => Carbon::now()->format('Y-m-d'),
                    'refund_approved_by' => auth('api')->user()->name,
                ]);

            if (!$refund_approval) {
                return 'error';
            }
        }
        return 'success';
    }

    public function saveExcessRefundAccountTransactions($excessRefundRefNo, $refundDate, $businessType,$excessRefundAmount, $excessRefundBranchId, $companyId)
    {
        $accountTransaction = new Accounttransaction();
        $accountTrasactionId = date('YmdH') . rand(pow(10, 9 - 1), pow(10, 9) - 1);
        $accountTransaction->account_transaction_id = $accountTrasactionId;
        $accountTransaction->account_voucher_type = 'SYS';
        $accountTransaction->account_voucher_date = $refundDate;
        $accountTransaction->account_transaction_mode = 'ExcessRefund';
        $accountTransaction->registration_type = $businessType;
        $accountTransaction->account_voucher_amount = (float)$excessRefundAmount;
        $accountTransaction->account_voucher_narration = 'Excess Refund Payment against Refund Ref No : ' . $excessRefundRefNo;

        if($businessType == 'PF'){
            $year = date('Y');
            $getAccountTransactionSerialNo = DB::table('masteridholders')
                ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
                ->where('masteridholders.id_type','=','Account_Transaction')
                ->where('masteridholders.registration_type', '=', $businessType)
                ->where('masteridholders.branch_id','=',$excessRefundBranchId)
                ->where('masteridholders.f_year','=',$year)
                ->get(['masteridholders.serial_no', 'branches.branch_code'])->first();
        }else{
            $year = date('Y');
            $getAccountTransactionSerialNo = DB::table('masteridholders')
                ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
                ->where('masteridholders.id_type','=','GF_Account_Transaction')
                ->where('masteridholders.registration_type', '=', $businessType)
                ->where('masteridholders.branch_id','=',$excessRefundBranchId)
                ->where('masteridholders.f_year','=',$year)
                ->get(['masteridholders.serial_no', 'branches.branch_code'])->first();
        }

        $serial_no = $getAccountTransactionSerialNo->serial_no;
        $serial_branch = $getAccountTransactionSerialNo->branch_code;

        $accountTransactionSerialNo = (int)$serial_no + 1;
        $branch_code = $serial_branch;
        $voucher_number = 'SYS/' . $businessType . '/' . $year . '/' . $branch_code . '/' . $accountTransactionSerialNo;

        $accountTransaction->account_collection_instrument_no = NULL;
        $accountTransaction->account_cheque_date = NULL;
        $accountTransaction->account_collection_bank = NULL;
        $accountTransaction->account_voucher_number = $voucher_number;
        $accountTransaction->account_payment_id = NULL;
        $accountTransaction->account_collection_id = NULL;
        $accountTransaction->account_reference_no = $excessRefundRefNo;

        $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
        $accountTransaction->account_effective_end_date = NULL;
        $accountTransaction->account_created_by = auth('api')->user()->name;
        $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');

        if ($accountTransaction->save()) {

            if($businessType == 'PF'){
                DB::table('masteridholders')
                ->where('branch_id', '=', $excessRefundBranchId)
                ->where('f_year', '=', Carbon::now()->year)
                ->where('id_type', '=', 'Account_Transaction')
                ->where('registration_type', '=', $businessType)
                ->update(['serial_no' => $accountTransactionSerialNo]);
            }else{
                DB::table('masteridholders')
                ->where('branch_id', '=', $excessRefundBranchId)
                ->where('f_year', '=', Carbon::now()->year)
                ->where('id_type', '=', 'GF_Account_Transaction')
                ->where('registration_type', '=', $businessType)
                ->update(['serial_no' => $accountTransactionSerialNo]);
            }

            if ($this->saveExcessRefundAccountTransactionDetails($accountTrasactionId, $excessRefundRefNo,$businessType,
             $excessRefundAmount, $companyId) == 'error') {

                return 'error';
            }

        } else {
            return 'error';
        }

        return 'success';
    }

    public function saveExcessRefundAccountTransactionDetails($accountTrasactionId, $excessRefundRefNo,
                                                              $businessType, $excessRefundAmount, $companyId)
    {
        if ($businessType == 'PF') {

            $transactionCategory = ['PPF_Received_Acc', 'PF_Excess_Payment_Account'];

            foreach ($transactionCategory as $transaction) {

                $saveAccountTransactionDetail = new Accounttransactiondetail();
                $genAccountTransactionId = date('YmdH') . rand(pow(10, 9 - 1), pow(10, 9) - 1);
                $saveAccountTransactionDetail->acc_transaction_detail_id = $genAccountTransactionId;
                $saveAccountTransactionDetail->acc_transaction_type_id = $accountTrasactionId;
                $saveAccountTransactionDetail->registration_type = $businessType;

                if ($transaction == 'PPF_Received_Acc') {

                    $saveAccountTransactionDetail->acc_account_group_id = '9F1D02A0-421A-11EC-92A6-BB4CCAAF9B33';
                    $saveAccountTransactionDetail->acc_account_type_id = 'FDDA5B70-421A-11EC-A70A-5FD74528FECD';
                    $saveAccountTransactionDetail->acc_debit_amount = (float)$excessRefundAmount;
                    $saveAccountTransactionDetail->acc_credit_amount = 0;
                    $saveAccountTransactionDetail->acc_narration = 'PF Excess Payment Refund [PPF Received A/c] against Refund-No : ' . $excessRefundRefNo;

                } else {

                    $saveAccountTransactionDetail->acc_account_group_id = '987A9B20-45DE-11EC-973C-47DC726D5DD3';
                    $saveAccountTransactionDetail->acc_account_type_id = 'AAE231D0-C525-11EC-99CE-71ED67D278D2';
                    $saveAccountTransactionDetail->acc_debit_amount = 0;
                    $saveAccountTransactionDetail->acc_credit_amount = (float)$excessRefundAmount;
                    $saveAccountTransactionDetail->acc_narration = 'PF Excess Payment Refund [Excess Payment A/c] against Refund-No : ' . $excessRefundRefNo;
                }

                $saveAccountTransactionDetail->acc_reference_no = $excessRefundRefNo;
                $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;
                $saveAccountTransactionDetail->acc_company_id = $companyId;
                $saveAccountTransactionDetail->acc_employee_id = NULL;
                $saveAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
                $saveAccountTransactionDetail->acc_effective_end_date = NULL;
                if (!$saveAccountTransactionDetail->save()) {
                    return 'error';
                }

            }

        } else {

            $transactionCategory = ['GF_Received_Acc', 'GF_Excess_Payment_Account'];

            foreach ($transactionCategory as $transaction) {

                $saveAccountTransactionDetail = new Accounttransactiondetail();
                $genAccountTransactionId = date('YmdH') . rand(pow(10, 9 - 1), pow(10, 9) - 1);
                $saveAccountTransactionDetail->acc_transaction_detail_id = $genAccountTransactionId;
                $saveAccountTransactionDetail->acc_transaction_type_id = $accountTrasactionId;
                $saveAccountTransactionDetail->registration_type = $businessType;

                if ($transaction == 'GF_Received_Acc') {

                    $saveAccountTransactionDetail->acc_account_group_id = '9F1D02A0-421A-11EC-92A6-BB4CCAAF9B33';
                    $saveAccountTransactionDetail->acc_account_type_id = '379A35D0-C3BB-11EC-B51B-79FB79A98A69';
                    $saveAccountTransactionDetail->acc_debit_amount = (float)$excessRefundAmount;
                    $saveAccountTransactionDetail->acc_credit_amount = 0;
                    $saveAccountTransactionDetail->acc_narration = 'GF Excess Payment Refund [PPF Received A/c] against Refund-No : ' . $excessRefundRefNo;

                } else {

                    $saveAccountTransactionDetail->acc_account_group_id = '987A9B20-45DE-11EC-973C-47DC726D5DD3';
                    $saveAccountTransactionDetail->acc_account_type_id = '5AE5E410-C53C-11EC-90F3-799F616C2F34';
                    $saveAccountTransactionDetail->acc_debit_amount = 0;
                    $saveAccountTransactionDetail->acc_credit_amount = (float)$excessRefundAmount;
                    $saveAccountTransactionDetail->acc_narration = 'GF Excess Payment Refund [Excess Payment A/c] against Refund-No : ' . $excessRefundRefNo;
                }
                $saveAccountTransactionDetail->acc_reference_no = $excessRefundRefNo;
                $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;
                $saveAccountTransactionDetail->acc_company_id = $companyId;
                $saveAccountTransactionDetail->acc_employee_id = NULL;
                $saveAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
                $saveAccountTransactionDetail->acc_effective_end_date = NULL;

                if (!$saveAccountTransactionDetail->save()) {
                    return 'error';
                }
            }
        }
        return 'success';
    }

    public function generateDocForExcessPayment($businessType,$excessRefundRefNo,$refundDate,$excessRefundAmount,$companyId,$collectionReceiptNo,$refundApproveId, $bank_account_no, $bank_name) {

          $whole = intval($excessRefundAmount);
          $decimal1 = $excessRefundAmount - $whole;
          $decimal2 = round($decimal1, 2);
          $get_substring_value = substr($decimal2, 2);
          $convert_to_int = intval($get_substring_value);
          $f = new \NumberFormatter(locale_get_default(), \NumberFormatter::SPELLOUT);
          $word = $f->format($convert_to_int);

          $numberToWords = new NumberToWords();
          $numberTransformer = $numberToWords->getNumberTransformer('en');
          $numWord = $numberTransformer->toWords($excessRefundAmount);
          $numInWords = $numWord . ' and chhetrum ' . $word;

          $company_data = Companyregistration::where('company_id','=',$companyId)->get()->first();
          $company_name = $company_data->org_name;
          $pf_gf_account_no = $company_data->company_account_no;

          $data = [
            'registration_type' => $businessType,
            'excess_refund_ref_no' => $excessRefundRefNo,
            'refund_processing_date' => $refundDate,
            'excess_amount' => $excessRefundAmount,
            'amount_in_words' => $numInWords,
            'organization_name' => $company_name,
            'account_no' => $pf_gf_account_no,
            'col_ref_no' =>$collectionReceiptNo,
            'bank_account_no' => $bank_account_no,
            'bank_name' => $bank_name,
          ];

          $pdf = App::make('dompdf.wrapper');
          $bladeView = view('refundfiles.excess_refund', $data);
          $pdf->loadHTML($bladeView);
          $currentDateTime = Carbon::now()->format('YmdHis');
          $fileName = 'excess_refund' . '_' . $currentDateTime . '.pdf';

          if($pdf->save(Storage::disk('refundslip')->put($fileName, $pdf->output()))){
            DB::table('documents')->insert([
                'doc_type_id' => 74656474,
                'doc_ref_no' => $refundApproveId, // Check for excessRefundRefNo[ Pre-saved data]
                'doc_ref_type' => 'ExcessPaymentRefund',
                'doc_type' => 'pdf',
                'registration_type' => $businessType,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'doc_path' => $fileName,
                'document_type' => 'ExcessPaymentRefund',
                'doc_user_id' => auth('api')->user()->id
            ]);
            return 'success';
          }else{
            return 'error';
          }
    }

    public function getPendingCollections($collection_no)
    {
        return Pfcollection::where('pf_collection_no', '=', $collection_no)
            ->where('pf_collection_status', '=', 'under_process')
            ->get(['pf_collection_amount', 'pf_collection_date'])->first();
    }

    public function collectionNos($company_id)
    {
        return Pfcollection::where('pf_collection_status', '=', 'under_process')
            ->where('pf_collection_company_account_no_id', '=', $company_id)
            ->get('pf_collection_no')->all();
    }
}
