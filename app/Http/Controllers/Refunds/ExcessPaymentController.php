<?php

namespace App\Http\Controllers\Refunds;

use App\Models\Accounttransactiondetail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Accounttransaction;  
use App\Models\Companyregistration;  
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Paymentdetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use NumberToWords\NumberToWords;

class ExcessPaymentController extends Controller
{
  // Permission Here

    public function saveExccessPayment(Request $request) 
    {
        DB::beginTransaction();
        
        $year = Carbon::now()->year;
        $accountTransaction = new Accounttransaction();
        $accountTransactionId = date('Ymd') . random_int(11111, 99999);
        $accountTransaction->account_transaction_id = $accountTransactionId;
        $accountTransaction->account_voucher_type = 'PV';
        $accountTransaction->account_voucher_date = $request->payment_date;
        $accountTransaction->account_transaction_mode = $request->payment_mode;
        $accountTransaction->account_voucher_amount = (float)$request->accountVoucherAmount;

        $accountTransaction->account_voucher_narration = 'Excess Payment against Payment-Ref-No : ' . $request->payment_advise_no;

        if ($request->registration_type == 'PF') {
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
            $voucher_number = 'PV/EP/PF/' . date('Y') . '/' . $branch_code . '/BANK/' . $accountTransactionSerialNo;
       
        }else{

                $getAccountTransactionSerialNo = DB::table('masteridholders')
                ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
                ->where('id_type', 'GF_PV_Transaction')
                ->where('registration_type', '=', 'GF')
                ->where('branch_id', $request->payment_branch_id)
                ->where('f_year', $year)
                ->get(['serial_no', 'branch_code'])
                ->first();

                $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
                $branch_code = $getAccountTransactionSerialNo->branch_code;
                $voucher_number = 'GF/EP/PF/' . date('Y') . '/' . $branch_code . '/BANK/' . $accountTransactionSerialNo;
            }

        $bank_data = explode('/', $request->payment_bank_account_data);
        $acc_account_type_id = $bank_data[0];
        $col_bank_acc_no = $bank_data[1];

        $instrument_no = $request->instrumentNo;
        $cheque_date = $request->instrument_date;
        $col_bank_name = $request->payment_bank_name;         

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
          $accountTransaction->registration_type = $request->registration_type;

          if ($accountTransaction->save()) {

              $refundPaymentRemarks = $request->refund_payment_remarks;

              if ($this->saveExcessPaymentAccountTransactionDetail($request, $accountTransactionId, $col_bank_acc_no,
                      $voucher_number, $instrument_no, $cheque_date, $refundPaymentRemarks, $acc_account_type_id) == 'error') {

                        DB::rollBack();
                        return response()->json('error');

              } else {

                  DB::commit();

                  if ($request->registration_type == 'PF') {
                      DB::table('masteridholders')
                      ->where('branch_id', '=', $request->payment_branch_id)
                      ->where('f_year', '=', $year)
                      ->where('id_type', '=', 'PV_Transaction')
                      ->where('registration_type', '=', 'PF')
                      ->update(['serial_no' => $accountTransactionSerialNo]);
                  }else{

                    DB::table('masteridholders')
                    ->where('branch_id', '=', $request->payment_branch_id)
                    ->where('f_year', '=', $year)
                    ->where('id_type', '=', 'GF_PV_Transaction')
                    ->where('registration_type', '=', 'GF')
                    ->update(['serial_no' => $accountTransactionSerialNo]);
                  }                      
              }

          } else {

                DB::rollBack();
                 return response()->json('error');
          }
          return response()->json('success');
    }

    public function saveExcessPaymentAccountTransactionDetail(Request $request, $accountTransactionId, $col_bank_acc_no, $voucher_number,
                                                                      $instrument_no, $cheque_date, $refundPaymentRemarks, $acc_account_type_id)
    {

            $refundTransactionCategory = ['Refund_Payable_Acc', 'Bank_Account'];

            $payment_data = Paymentdetail::where('payment_advise_ref_no', '=', $request->payment_advise_no)->get()->first();

            foreach ($refundTransactionCategory as $refund_detail_data) {

                    $saveRefundAccountTransactionDetail = new Accounttransactiondetail();
                    $genRefundAccountTransactionId = date('YmdH') . random_int(222222, 999999);

                    $saveRefundAccountTransactionDetail->acc_transaction_detail_id = $genRefundAccountTransactionId;
                    $saveRefundAccountTransactionDetail->acc_transaction_type_id = $accountTransactionId;
                    $saveRefundAccountTransactionDetail->registration_type = $request->registration_type;

                    if ($refund_detail_data == 'Refund_Payable_Acc') {

                        $saveRefundAccountTransactionDetail->acc_debit_amount = (float)$request->total_payable_amount;
                        $saveRefundAccountTransactionDetail->acc_credit_amount = 0;
                        $saveRefundAccountTransactionDetail->acc_account_group_id = '987A9B20-45DE-11EC-973C-47DC726D5DD3';

                        if($request->registration_type == 'PF'){
                            
                            $saveRefundAccountTransactionDetail->acc_account_type_id = 'AAE231D0-C525-11EC-99CE-71ED67D278D2';  

                        }else{

                            $saveRefundAccountTransactionDetail->acc_account_type_id = '5AE5E410-C53C-11EC-90F3-799F616C2F34';
                        }

                        $saveRefundAccountTransactionDetail->acc_narration = 'Excess Payment Contribution against Payment-Ref-No : ' . $payment_data['payment_refund_ref_no'];

                    }  else {  // Bank Account

                        $saveRefundAccountTransactionDetail->acc_debit_amount = 0;
                        $saveRefundAccountTransactionDetail->acc_credit_amount = (float)$request->total_payable_amount;
                        $saveRefundAccountTransactionDetail->acc_account_group_id = 'A7621450-421A-11EC-858F-9BE7FA733BC4';
                        $saveRefundAccountTransactionDetail->acc_account_type_id = $acc_account_type_id;     

                        $saveRefundAccountTransactionDetail->acc_narration = 'Excess Total Payment against Payment-Ref-No : ' . $payment_data['payment_refund_ref_no'];
                    }

                    $saveRefundAccountTransactionDetail->acc_reference_no = $payment_data['payment_refund_ref_no'];
                    $saveRefundAccountTransactionDetail->acc_sub_ledger_id = null;
                    $saveRefundAccountTransactionDetail->acc_company_id = $request->payment_dtl_company_id;

                    $getCompanyAccountNo = Companyregistration::where('company_id', '=', $request->payment_dtl_company_id)->get();
                    $companyAccountNumber = $getCompanyAccountNo->first()->company_account_no;

                    $saveRefundAccountTransactionDetail->acc_employee_id = NULL;
                    $saveRefundAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
                    $saveRefundAccountTransactionDetail->acc_effective_end_date = null;

                    if (!$saveRefundAccountTransactionDetail->save()) {

                        return 'error';

                    }else{

                            $update_refund = DB::table('refunds')
                            ->where('refund_ref_no', '=', $payment_data['payment_refund_ref_no'])
                            ->where('registration_type', '=', $request->registration_type)
                            ->where('refund_status', '=', 'Approved')
                            ->update([
                                'refund_status' => 'Completed',
                                'reg_branch_id' => $request->payment_branch_id,
                                'refund_payment_date' => $request->refund_payment_date,
                                'refund_payment_remarks' => $request->refund_payment_remarks,
                                'refund_payment_processed_by' => auth('api')->user()->name,
                            ]);

                            $update_payment = DB::table('paymentdetails')
                            ->where('payment_refund_ref_no', '=', $payment_data['payment_refund_ref_no'])
                            ->where('registration_type', '=', $request->registration_type)
                            ->update([
                                'payment_ref_voucher_no' => $voucher_number,                           
                            ]);
                    }                                  
            }

                if ($this->createExcessPaymentVoucher($request, $voucher_number, $col_bank_acc_no,$instrument_no,
                        $cheque_date, $refundPaymentRemarks, $companyAccountNumber, $acc_account_type_id, $payment_data['payment_refund_ref_no']) == 'error') {

                    return 'error';

                }else{

                    DB::table('payments')
                    ->where('payment_advise_no', '=', $request->payment_advise_no)
                    ->where('registration_type', '=', $request->registration_type)
                    ->update([
                        'payment_status' => 'RP',
                    ]);
                }

        
            return 'success';
    }

    /**  excess payment document */
    public function createExcessPaymentVoucher(Request $request, $voucher_number, $col_bank_acc_no,$instrument_no,
    $cheque_date, $refundPaymentRemarks,$companyAccountNumber, $acc_account_type_id, $payment_refund_ref_no)
    {

            $total_payable_amount = $request->total_payable_amount;
            $paymentDate = $request->payment_date;

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

            if($request->registration_type == 'PF'){

                $doc_ref_type = 'PF Excess Payment';
                $reg_type = 'PF';
               
            }else{

                $doc_ref_type = 'GF Excess Payment';
                $reg_type = 'GF';
            
            }

            $bladeView = view('refundfiles.excesspayment', compact('voucher_number', 'paymentDate',
            'col_bank_acc_no', 'instrument_no', 'cheque_date', 'refundPaymentRemarks', 'numbertowords_payable', 'total_payable_amount',
            'companyAccountNumber','acc_account_type_id','reg_type', 'payment_refund_ref_no'));

            $pdf->loadHTML($bladeView);

            $fileName = 'excess_payment_voucher_' . random_int(6666, 9999) . '_' . Carbon::now()->format('YmdHis') . '.pdf';

            if ($pdf->save(Storage::disk('refundslip')->put($fileName, $pdf->output()))) {

                DB::table('documents')->insert([
                    'doc_type_id' => 95768856,
                    'doc_ref_no' => $request->payment_advise_no,
                    'doc_ref_type' =>$doc_ref_type,
                    'doc_type' => 'pdf',
                    'doc_path' => $fileName,
                    'doc_date' => Carbon::now()->format('Y-m-d'),
                    'registration_type' => $reg_type,
                    'document_type' => 'ExcessPaymentVoucher',
                    'doc_user_id' => auth('api')->user()->id
                ]);

                return 'success';

            } else {

                return 'error';
            }         
    }
  
}
