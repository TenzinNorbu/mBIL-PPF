<?php

namespace App\Http\Controllers\Refunds;

use App\Http\Controllers\Controller;
use App\Models\Accounttransaction;
use App\Models\Accounttransactiondetail;
use App\Models\Companyregistration;
use App\Models\Payment;
use App\Models\Paymentdetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NumberToWords\NumberToWords;

class RefundPaymentController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:refund-payments', ['only' => ['SaveRefundPayment']]);
    }

    public function SaveRefundPayment(Request $request)
    {
        if ($request->registration_type == 'PF') {

            DB::beginTransaction();
            $updateRefundPayment = Payment::where('payment_advise_no', '=', $request->payment_advise_no)
                ->where('registration_type', '=', 'PF')
                ->get();

            if ($updateRefundPayment) {
                $save_refund_payment_account_transactions = $this->saveRefundPaymentAccountTransactions($request);
                if ($save_refund_payment_account_transactions == 'error') {

                    return 'error';

                } else {
                   
                    DB::table('payments')
                        ->where('payment_advise_no', '=', $request->payment_advise_no)
                        ->where('registration_type', '=', 'PF')
                        ->update([
                            'payment_status' => 'RP',
                        ]);

                    DB::commit();

                    return response()->json('success');
                }

            } else {

                DB::rollBack();
                return response()->json('error');
            }

        } else {

            DB::beginTransaction();
            $updateRefundPayment = Payment::where('payment_advise_no', '=', $request->payment_advise_no)
                ->where('registration_type', '=', 'GF')
                ->get();

            if ($updateRefundPayment) {
                $save_refund_payment_account_transactions = $this->saveRefundPaymentAccountTransactions($request);
                if ($save_refund_payment_account_transactions == 'error') {

                    return 'error';

                } else {

                    DB::table('payments')
                        ->where('payment_advise_no', '=', $request->payment_advise_no)
                        ->where('registration_type', '=', 'GF')
                        ->update([
                            'payment_status' => 'RP',
                        ]);

                    DB::commit();
                    return response()->json('success');
                }

            } else {

                DB::rollBack();
                return response()->json('error');
            }
        }
    }

    public function saveRefundPaymentAccountTransactions(Request $request)
    {
        if ($request->registration_type == 'PF') {
            $year = Carbon::now()->year;
            $accountTransaction = new Accounttransaction();
            $accountTransactionId = date('Ymd') . random_int(6666, 9999);
            $accountTransaction->account_transaction_id = $accountTransactionId;
            $accountTransaction->account_voucher_type = 'PV';
            $accountTransaction->account_voucher_date = $request->payment_date;
            $accountTransaction->account_transaction_mode = $request->payment_mode;
            $accountTransaction->account_voucher_amount = (float)$request->total_payable_amount;
            $accountTransaction->account_voucher_narration = 'PF Refund Payment against Payment-Ref-No : ' . $request->payment_advise_no;

            $getAccountTransactionSerialNo = DB::table('masteridholders')
                ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
                ->where('id_type', 'PV_Transaction')
                ->where('registration_type', '=', 'PF')
                ->where('branch_id', $request->payment_branch_id)
                ->where('f_year', $year)
                ->get(['serial_no', 'branch_code'])
                ->first();

            $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
            $branch_code = $getAccountTransactionSerialNo->branch_code;

            if ($request->payment_mode == 'Cash') {

                $instrument_no = NULL;
                $cheque_date = NULL;
                $col_bank_name = NULL;
                $voucher_number = 'PV/PF/' . date('Y') . '/' . $branch_code . '/CASH/' . $accountTransactionSerialNo;
                $col_bank_acc_no = NULL;
                $acc_account_type_id = NULL;

            } else {

                $bank_data = explode('/', $request->payment_bank_account_data);
                $acc_account_type_id = $bank_data[0];
                $col_bank_acc_no = $bank_data[1];

                $instrument_no = $request->instrumentNo;
                $cheque_date = $request->instrument_date;
                $col_bank_name = $request->payment_bank_name;

                $voucher_number = 'PV/PF/' . date('Y') . '/' . $branch_code . '/BANK/' . $accountTransactionSerialNo;
            }

            $accountTransaction->account_voucher_number = $voucher_number;

            $accountTransaction->account_collection_instrument_no = $instrument_no;
            $accountTransaction->account_cheque_date = $cheque_date;
            $accountTransaction->account_collection_bank = $col_bank_name;

            $accountTransaction->account_payment_id = $request->payment_advise_no;
            $accountTransaction->account_collection_id = null;

            $accountTransaction->account_reference_no = $request->payment_advise_no;
            $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
            $accountTransaction->account_effective_end_date = null;
            $accountTransaction->account_created_by = auth('api')->user()->name;
            $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');
            $accountTransaction->registration_type = 'PF';

            if ($accountTransaction->save()) {
                $refundPaymentRemarks = $request->refund_payment_remarks;

                if ($this->saveRefundPaymentAccountTransactionDetail($request, $accountTransactionId, $col_bank_acc_no,
                        $voucher_number, $instrument_no, $cheque_date, $refundPaymentRemarks, $acc_account_type_id) == 'error') {

                    return 'error';

                } else {

                    
                    DB::table('masteridholders')
                        ->where('branch_id', '=', $request->payment_branch_id)
                        ->where('f_year', '=', $year)
                        ->where('id_type', '=', 'PV_Transaction')
                        ->where('registration_type', '=', 'PF')
                        ->update(['serial_no' => $accountTransactionSerialNo]);

                    DB::commit();
                }

            } else {

                return 'error';
                
            }

            //return 'success';

        } else {

            $year = Carbon::now()->year;
            $accountTransaction = new Accounttransaction();
            $accountTransactionId = date('YmdH') . random_int(6666, 9999);
            $accountTransaction->account_transaction_id = $accountTransactionId;
            $accountTransaction->account_voucher_type = 'PV';

            $accountTransaction->account_voucher_date = $request->payment_date;

            $accountTransaction->account_transaction_mode = $request->payment_mode;

            $accountTransaction->account_voucher_amount = (float)$request->total_payable_amount;

            $accountTransaction->account_voucher_narration = 'GF Refund Payment against Payment-Ref-No : ' . $request->payment_advise_no;

            $getAccountTransactionSerialNo = DB::table('masteridholders')
                ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
                ->where('id_type', 'GF_RV_Transaction')
                ->where('registration_type', '=', 'GF')
                ->where('branch_id', $request->payment_branch_id)
                ->where('f_year', $year)
                ->get(['serial_no', 'branch_code'])
                ->first();

            $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
            $branch_code = $getAccountTransactionSerialNo->branch_code;

            if ($request->payment_mode == 'Cash') {
                $acc_account_type_id = NULL;
                $instrument_no = null;
                $cheque_date = null;
                $col_bank_name = null;
                $voucher_number = 'PV/GF/' . date('Y') . '/' . $branch_code . '/CASH/' . $accountTransactionSerialNo;
                $col_bank_acc_no = null;

            } else {

                $bank_data = explode('/', $request->payment_bank_account_data);
                $acc_account_type_id = $bank_data[0];
                $col_bank_acc_no = $bank_data[1];

                $instrument_no = $request->instrumentNo;
                $cheque_date = $request->instrument_date;
                $col_bank_name = $request->payment_bank_name;
                $voucher_number = 'PV/GF/' . date('Y') . '/' . $branch_code . '/BANK/' . $accountTransactionSerialNo;
            }

            $accountTransaction->account_voucher_number = $voucher_number;

            $accountTransaction->account_collection_instrument_no = $instrument_no;
            $accountTransaction->account_cheque_date = $cheque_date;
            $accountTransaction->account_collection_bank = $col_bank_name;

            $accountTransaction->account_payment_id = $request->payment_advise_no;
            $accountTransaction->account_collection_id = null;

            $accountTransaction->account_reference_no = $request->payment_advise_no;
            $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
            $accountTransaction->account_effective_end_date = null;
            $accountTransaction->account_created_by = auth('api')->user()->name;
            $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');
            $accountTransaction->registration_type = 'GF';

            if ($accountTransaction->save()) {
                $refundPaymentRemarks = $request->refund_payment_remarks;

                if ($this->saveRefundPaymentAccountTransactionDetail($request, $accountTransactionId, $col_bank_acc_no,
                        $voucher_number, $instrument_no, $cheque_date, $refundPaymentRemarks, $acc_account_type_id) == 'error') {

                    return 'error';
                } else {

                    DB::commit();
                    DB::table('masteridholders')
                        ->where('branch_id', '=', $request->payment_branch_id)
                        ->where('f_year', '=', $year)
                        ->where('id_type', '=', 'GF_PV_Transaction')
                        ->where('registration_type', '=', 'GF')
                        ->update(['serial_no' => $accountTransactionSerialNo]);
                }

            } else {
                return 'error';
            }

            //return 'success';
        }
    }

    public function saveRefundPaymentAccountTransactionDetail(Request $request, $accountTransactionId, $col_bank_acc_no, $voucher_number,
                                                                      $instrument_no, $cheque_date, $refundPaymentRemarks, $acc_account_type_id)
    {

        if ($request->registration_type == 'PF') {

            $refund_payment_requests = $request->refundTransactionCategory;

            foreach ($refund_payment_requests as $refund_detail_data) {

                // Condition for excess refund payment
                $refundTransactionCategory = ['PfColRefundPayableAc', 'PfInterestRefundPayableAc', 'BankAccount'];

                foreach ($refundTransactionCategory as $refundTransaction) {

                    $saveRefundAccountTransactionDetail = new Accounttransactiondetail();
                    $genRefundAccountTransactionId = date('YmdH') . random_int(6666, 9999);

                    $saveRefundAccountTransactionDetail->acc_transaction_detail_id = $genRefundAccountTransactionId;
                    $saveRefundAccountTransactionDetail->acc_transaction_type_id = $accountTransactionId;
                    $saveRefundAccountTransactionDetail->registration_type = 'PF';

                    if ($refundTransaction == 'PfColRefundPayableAc') {

                        $saveRefundAccountTransactionDetail->acc_account_group_id = '987A9B20-45DE-11EC-973C-47DC726D5DD3';
                        $saveRefundAccountTransactionDetail->acc_account_type_id = 'A5C730C0-45DF-11EC-AAE3-1D943BCC8402';
                        $saveRefundAccountTransactionDetail->acc_debit_amount = (float)$refund_detail_data['payment_contribution_amount'];

                        $saveRefundAccountTransactionDetail->acc_credit_amount = 0;
                        $saveRefundAccountTransactionDetail->acc_narration = 'PF Refund Contribution against Payment-Ref-No : ' . $refund_detail_data['payment_refund_ref_no'];

                    } elseif ($refundTransaction == 'PfInterestRefundPayableAc') {

                        $saveRefundAccountTransactionDetail->acc_account_group_id = '987A9B20-45DE-11EC-973C-47DC726D5DD3';
                        $saveRefundAccountTransactionDetail->acc_account_type_id = 'B68C9880-45DF-11EC-B0EC-4568E1C45C9C';
                        $saveRefundAccountTransactionDetail->acc_debit_amount = (float)$refund_detail_data['payment_interest_amount'];

                        $saveRefundAccountTransactionDetail->acc_credit_amount = 0;
                        $saveRefundAccountTransactionDetail->acc_narration = 'PF Refund Interest against Payment-Ref-No : ' . $refund_detail_data['payment_refund_ref_no'];

                    } else {  // Bank Account

                        $saveRefundAccountTransactionDetail->acc_account_group_id = 'A7621450-421A-11EC-858F-9BE7FA733BC4';
                        $saveRefundAccountTransactionDetail->acc_account_type_id = $acc_account_type_id;
                        $saveRefundAccountTransactionDetail->acc_debit_amount = 0;
                        $saveRefundAccountTransactionDetail->acc_credit_amount = (float)$refund_detail_data['payment_total_amount'];

                        $saveRefundAccountTransactionDetail->acc_narration = 'PF Refund Total Payment against Payment-Ref-No : ' . $refund_detail_data['payment_refund_ref_no'];
                    }

                    $saveRefundAccountTransactionDetail->acc_reference_no = $refund_detail_data['payment_refund_ref_no'];
                    $saveRefundAccountTransactionDetail->acc_sub_ledger_id = null;
                    $saveRefundAccountTransactionDetail->acc_company_id = $refund_detail_data['payment_dtl_company_id'];

                    $companyId = $refund_detail_data['payment_dtl_company_id'];
                    $getCompanyAccountNo = Companyregistration::where('company_id', '=', $companyId)
                        ->where('registration_type', '=', 'PF')
                        ->get();
                    $companyAccountNumber = $getCompanyAccountNo->first()->company_account_no;

                    $saveRefundAccountTransactionDetail->acc_employee_id = $refund_detail_data['payment_employee_id'];
                    $saveRefundAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
                    $saveRefundAccountTransactionDetail->acc_effective_end_date = null;

                    if (!$saveRefundAccountTransactionDetail->save()) {

                        return 'error';

                    }else{

                        DB::table('refunds')
                        ->where('refund_ref_no', '=', $refund_detail_data['payment_refund_ref_no'])
                        ->where('registration_type', '=', 'PF')
                        ->where('refund_status', '=', 'Approved')
                        ->update([
                            'refund_status' => 'Completed',
                            'reg_branch_id' => $request->payment_branch_id,
                            'refund_payment_date' => $request->refund_payment_date,
                            'refund_payment_remarks' => $request->refund_payment_remarks,
                            'refund_payment_processed_by' => auth('api')->user()->name,
                        ]);

                        DB::table('paymentdetails')
                        ->where('payment_refund_ref_no', '=', $refund_detail_data['payment_refund_ref_no'])
                        ->where('registration_type', '=', 'PF')
                        ->update([
                            'payment_ref_voucher_no' => $voucher_number,                           
                        ]);

                    }
                }
            }

            $paymentDate = $request->payment_date;
            $total_payable_amount = $request->total_payable_amount;

            if ($this->createRefundPaymentVoucher($request, $refund_payment_requests, $voucher_number, $paymentDate, $col_bank_acc_no,
                    $instrument_no, $cheque_date, $refundPaymentRemarks, $total_payable_amount,
                    $accountTransactionId, $companyAccountNumber, $acc_account_type_id) == 'error') {

                return 'error';

            }

            return 'success';

        } else {

            $refund_payment_requests = $request->refundTransactionCategory;

            foreach ($refund_payment_requests as $refund_detail_data) {

                $refundTransactionCategory = ['PfColRefundPayableAc', 'PfInterestRefundPayableAc', 'BankAccount'];

                foreach ($refundTransactionCategory as $refundTransaction) {

                    $saveRefundAccountTransactionDetail = new Accounttransactiondetail();
                    $genRefundAccountTransactionId = date('YmdH') . random_int(6666, 9999);

                    $saveRefundAccountTransactionDetail->acc_transaction_detail_id = $genRefundAccountTransactionId;
                    $saveRefundAccountTransactionDetail->acc_transaction_type_id = $accountTransactionId;
                    $saveRefundAccountTransactionDetail->registration_type = 'GF';

                    if ($refundTransaction == 'PfColRefundPayableAc') {

                        $saveRefundAccountTransactionDetail->acc_account_group_id = '987A9B20-45DE-11EC-973C-47DC726D5DD3';
                        $saveRefundAccountTransactionDetail->acc_account_type_id = 'A2AC6540-C3BC-11EC-A548-07FC7107F9BF';
                        $saveRefundAccountTransactionDetail->acc_debit_amount = (float)$refund_detail_data['payment_contribution_amount'];

                        $saveRefundAccountTransactionDetail->acc_credit_amount = 0;
                        $saveRefundAccountTransactionDetail->acc_narration = 'GF Refund Contribution against Payment-Ref-No : ' . $refund_detail_data['payment_refund_ref_no'];

                    } elseif ($refundTransaction == 'PfInterestRefundPayableAc') {
                        // For GF Interest Refund Payable Account
                        $saveRefundAccountTransactionDetail->acc_account_group_id = '987A9B20-45DE-11EC-973C-47DC726D5DD3';
                        $saveRefundAccountTransactionDetail->acc_account_type_id = '176CD330-C3BD-11EC-8C43-83D7FBDFBAA0';
                        $saveRefundAccountTransactionDetail->acc_debit_amount = (float)$refund_detail_data['payment_interest_amount'];

                        $saveRefundAccountTransactionDetail->acc_credit_amount = 0;
                        $saveRefundAccountTransactionDetail->acc_narration = 'GF Refund Interest against Payment-Ref-No : ' . $refund_detail_data['payment_refund_ref_no'];

                    } else {  // Bank Account

                        $saveRefundAccountTransactionDetail->acc_account_group_id = 'A7621450-421A-11EC-858F-9BE7FA733BC4';
                        $saveRefundAccountTransactionDetail->acc_account_type_id = $acc_account_type_id;
                        $saveRefundAccountTransactionDetail->acc_debit_amount = 0;
                        $saveRefundAccountTransactionDetail->acc_credit_amount = (float)$refund_detail_data['payment_total_amount'];

                        $saveRefundAccountTransactionDetail->acc_narration = 'GF Refund Total Payment against Payment-Ref-No : ' . $refund_detail_data['payment_refund_ref_no'];
                    }

                    $saveRefundAccountTransactionDetail->acc_reference_no = $refund_detail_data['payment_refund_ref_no'];
                    $saveRefundAccountTransactionDetail->acc_sub_ledger_id = null;
                    $saveRefundAccountTransactionDetail->acc_company_id = $refund_detail_data['payment_dtl_company_id'];

                    $companyId = $refund_detail_data['payment_dtl_company_id'];

                    $getCompanyAccountNo = Companyregistration::where('company_id', '=', $companyId)
                        ->where('registration_type', '=', 'GF')->get()->first();
                    $companyAccountNumber = $getCompanyAccountNo->company_account_no;

                    $saveRefundAccountTransactionDetail->acc_employee_id = $refund_detail_data['payment_employee_id'];
                    $saveRefundAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
                    $saveRefundAccountTransactionDetail->acc_effective_end_date = null;

                    if (!$saveRefundAccountTransactionDetail->save()) {

                        return 'error';
                    }else{

                        DB::table('refunds')
                        ->where('refund_ref_no', '=',$refund_detail_data['payment_refund_ref_no'])
                        ->where('registration_type', '=', 'GF')
                        ->where('refund_status', '=', 'Approved')
                        ->update([
                            'refund_status' => 'Completed',
                            'reg_branch_id' => $request->payment_branch_id,
                            'refund_payment_date' => $request->refund_payment_date,
                            'refund_payment_remarks' => $request->refund_payment_remarks,
                            'refund_payment_processed_by' => auth('api')->user()->name,
                        ]);

                        DB::table('paymentdetails')
                        ->where('payment_refund_ref_no', '=', $refund_detail_data['payment_refund_ref_no'])
                        ->where('registration_type', '=', 'GF')
                        ->update([
                            'payment_ref_voucher_no' => $voucher_number,                           
                        ]);
                    }
                }
            }

            $paymentDate = $request->payment_date;
            $total_payable_amount = $request->total_payable_amount;

            if ($this->createRefundPaymentVoucher($request, $refund_payment_requests, $voucher_number, $paymentDate, $col_bank_acc_no,
                    $instrument_no, $cheque_date, $refundPaymentRemarks, $total_payable_amount, $companyAccountNumber, $companyId, $acc_account_type_id) == 'error') {

                        return 'error';

            }
            return 'success';
        }
    }

    /** Generate Refund Payment Voucher */
    public function createRefundPaymentVoucher(Request $request, $refund_payment_requests, $voucher_number, $paymentDate, $col_bank_acc_no, $instrument_no,
                                                       $cheque_date, $refundPaymentRemarks, $total_payable_amount, $companyAccountNumber, $companyId,$acc_account_type_id)
    {

        if ($request->registration_type == 'PF') {

            $whole = intval($total_payable_amount);
            $decimal1 = $total_payable_amount - $whole;
            $decimal2 = round($decimal1, 2);
            $get_substring_value = substr($decimal2, 2);
            $convert_to_int = intval($get_substring_value);
            $f = new \NumberFormatter(locale_get_default(), \NumberFormatter::SPELLOUT);
            $word = $f->format($convert_to_int);

            $numberToWords = new NumberToWords();
            $numberTransformer = $numberToWords->getNumberTransformer('en');
            $numWord = $numberTransformer->toWords($total_payable_amount);
            $numbertowords_payable = $numWord . ' and chhetrum ' . $word;

            $pdf = App::make('dompdf.wrapper');
            $bladeView = view('refundfiles.refundpayment', compact('refund_payment_requests', 'voucher_number', 'paymentDate',
                'col_bank_acc_no', 'instrument_no', 'cheque_date', 'refundPaymentRemarks', 'numbertowords_payable', 'total_payable_amount',
                'companyAccountNumber','acc_account_type_id'));
            $pdf->loadHTML($bladeView);

            $genRefundRandomNo = random_int(6666, 9999);
            $currentDateTime = Carbon::now()->format('YmdHis');
            $fileName = 'pf_refund_payment_voucher_' . $genRefundRandomNo . '_' . $currentDateTime . '.pdf';

            if ($pdf->save(Storage::disk('refundslip')->put($fileName, $pdf->output()))) {

                DB::table('documents')->insert([
                    'doc_type_id' => 700000,
                    'doc_ref_no' => $request->payment_advise_no,
                    'doc_ref_type' => 'PF Payment',
                    'doc_type' => 'pdf',
                    'doc_path' => $fileName,
                    'doc_date' => Carbon::now()->format('Y-m-d'),
                    'registration_type' => 'PF',
                    'document_type' => 'RefundPaymentDoc',
                    'doc_user_id' => auth('api')->user()->id
                ]);

                return 'success';
            } else {

                return 'error';
            }

        } else {

            $whole = intval($total_payable_amount);
            $decimal1 = $total_payable_amount - $whole;
            $decimal2 = round($decimal1, 2);
            $get_substring_value = substr($decimal2, 2);
            $convert_to_int = intval($get_substring_value);
            $f = new \NumberFormatter(locale_get_default(), \NumberFormatter::SPELLOUT);
            $word = $f->format($convert_to_int);

            $numberToWords = new NumberToWords();
            $numberTransformer = $numberToWords->getNumberTransformer('en');
            $numWord = $numberTransformer->toWords($total_payable_amount);
            $numbertowords_payable = $numWord . ' and chhetrum ' . $word;

            $pdf = App::make('dompdf.wrapper');
            $bladeView = view('refundfiles.gf_refundpayment', compact('refund_payment_requests', 'voucher_number', 'paymentDate', 'col_bank_acc_no',
                'instrument_no', 'cheque_date', 'refundPaymentRemarks', 'numbertowords_payable', 'total_payable_amount', 'companyAccountNumber','acc_account_type_id'));
            $pdf->loadHTML($bladeView);

            $genRefundRandomNo = random_int(6666, 9999);
            $currentDateTime = Carbon::now()->format('YmdHis');
            $fileName = 'gf_refund_payment_voucher_' . $genRefundRandomNo . '_' . $currentDateTime . '.pdf';

            if ($pdf->save(Storage::disk('refundslip')->put($fileName, $pdf->output()))) {

                DB::table('documents')->insert([
                    'doc_type_id' => 700000,
                    'doc_ref_no' => $request->payment_advise_no,
                    'doc_ref_type' => 'GF Payment',
                    'doc_type' => 'pdf',
                    'doc_path' => $fileName,
                    'doc_date' => Carbon::now()->format('Y-m-d'),
                    'registration_type' => 'GF',
                    'document_type' => 'RefundPaymentDoc',
                    'doc_user_id' => auth('api')->user()->id
                ]);

                return 'success';

            } else {

                return 'error';
            }
        }
    }

}
