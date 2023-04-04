<?php

namespace App\Http\Controllers\AccountPosting;

use App\Http\Controllers\Controller;
use App\Models\Accounttransaction;
use App\Models\Accounttransactiondetail;
use App\Models\Accounttype;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Auth;
use NumberToWords\NumberToWords;
Use App\Models\User;
use Spatie\Permission\Traits\HasRoles;
use Exception;

class AccountPostingController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:accountposting|manual-posting', ['only' => ['saveAccountPostingTransactions']]);
    // }

    public function saveAccountPostingTransactions(Request $request)
    {
        try{
            $user = auth('api')->user();
            $voucherType = $request->voucher_type;
            $voucherDate = $request->voucher_date;
            $postingBranch = $request->posting_branch;
            $postingAmount = $request->posting_amount;
            $postingParty = $request->posting_Ref_No;

            $total_debit_amount = 0;
            $total_credit_amount = 0;

            if ($voucherType != 'MP') {
                if ($total_debit_amount != $total_credit_amount) {
                    return response()->json(['error','message'=>'Total Debit Amount does not match with the Total Credit Amount']);
                }
            } else {

                if (!$user->hasPermissionTo('manual-posting')) {
                    return response()->json(['error','message'=>'user does not have permission for manual posting']);
                }
            }

            if (!empty($postingParty)) {

                $postingPartyData = explode('*', $postingParty);
                $partyId = $postingPartyData[0];
                $refNo = $postingPartyData[1];
                $ref_type = $postingPartyData[3];

            } else {

                $partyId = NULL;
                $refNo = NULL;
                $ref_type = NULL;
            }

            $voucherNarration = $request->posting_description;
            $accountLedgerData = $request->quantities;

            if ($voucherType == 'PV' || $voucherType == 'RV') {

                $instrumentNo = $request->instrumentNo;
                $instrumentDate = $request->instrumentDate;
                $instrumentBankName = $request->instrumentBank;
            } else {

                $instrumentNo = NULL;
                $instrumentDate = NULL;
                $instrumentBankName = NULL;
            }

            DB::beginTransaction();
            $accountTransaction = new Accounttransaction();
            $accountTrasactionId = date('YmdH') . random_int(6666666, 9999999);
            $accountTransaction->account_transaction_id = (int)$accountTrasactionId;
            $accountTransaction->account_voucher_type = $voucherType;
            $accountTransaction->account_voucher_date = $voucherDate;
            $accountTransaction->account_transaction_mode = 'Posting';
            $accountTransaction->account_voucher_amount = (float)$postingAmount;
            $accountTransaction->account_voucher_narration = $voucherNarration;

            $year = Carbon::createFromFormat('Y-m-d', $voucherDate)->year;
            $getAccountTransactionSerialNo = DB::table('masteridholders')
                ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
                ->where('id_type', '=', 'Account_Posting_Transaction')
                ->where('registration_type', '=', NULL)
                ->where('branch_id', $postingBranch)
                ->where('f_year', $year)
                ->get(['serial_no', 'branch_code'])
                ->first();

            $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
            $branch_code = $getAccountTransactionSerialNo->branch_code;

            $voucher_number = $voucherType . '/' . $year . '/' . $branch_code . '/' . $accountTransactionSerialNo;

            $accountTransaction->account_voucher_number = $voucher_number;
            $accountTransaction->account_collection_instrument_no = $instrumentNo;
            $accountTransaction->account_cheque_date = $instrumentDate;
            $accountTransaction->account_collection_bank = $instrumentBankName;

            $accountTransaction->account_payment_id = NULL;
            $accountTransaction->account_collection_id = NULL;
            $accountTransaction->account_reference_no = $refNo;
            $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
            $accountTransaction->account_effective_end_date = NULL;
            $accountTransaction->account_created_by = auth('api')->user()->name;
            $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');
            $accountTransaction->registration_type = NULL;

            if ($accountTransaction->save()) {

                $accountTransactionDetail = $this->saveAccountPostingTransactionDetail($accountTrasactionId, $accountLedgerData, $refNo, $partyId, $ref_type, $voucherNarration);

                $accountPostingDocument = $this->generateAccountPostingVoucher($voucherDate, $postingAmount, $instrumentNo,
                    $instrumentDate, $instrumentBankName, $voucher_number, $accountLedgerData, $voucherNarration,$voucherType);

                if ($accountTransactionDetail == 'error' || $accountPostingDocument == 'error') {

                    DB::rollBack();
                    return response()->json('error');

                } else {

                    if (DB::table('masteridholders')
                        ->where('branch_id', '=', $postingBranch)
                        ->where('f_year', '=', $year)
                        ->where('id_type', '=', 'Account_Posting_Transaction')
                        ->where('registration_type', '=', NULL)
                        ->update(['serial_no' => $accountTransactionSerialNo])) {

                        DB::commit();
                        return response()->json(['success', 'filepath' => $accountPostingDocument]);

                    } else {

                        DB::rollBack();
                        return response()->json('error');
                    }
                }

            } else {

                DB::rollBack();
                return response()->json('error');
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function saveAccountPostingTransactionDetail($accountTrasactionId, $accountLedgerData, $refNo, $partyId, $ref_type, $voucherNarration)
    {

        try{
            foreach ($accountLedgerData as $key => $value) {

            $saveAccountTransactionDetail = new Accounttransactiondetail();
            $genAccountTransactionId = date('YmdH') . random_int(6666666, 9999999);
            $saveAccountTransactionDetail->acc_transaction_detail_id = $genAccountTransactionId;

            $saveAccountTransactionDetail->acc_transaction_type_id = $accountTrasactionId;
            $saveAccountTransactionDetail->registration_type = NULL;

            $saveAccountTransactionDetail->acc_narration = $voucherNarration;
            $saveAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
            $saveAccountTransactionDetail->acc_effective_end_date = NULL;

            $account_ledger_data = explode('*', $accountLedgerData[$key]['account_ledger_id']);
            $account_group_id = $account_ledger_data[0];
            $account_group_name = $account_ledger_data[1];
            $account_type_id = $account_ledger_data[2];
            $account_type_name = $account_ledger_data[3];
            $branch_id = $account_ledger_data[4];

            if ($account_group_id === 'A7621450-421A-11EC-858F-9BE7FA733BC4') {

                $saveAccountTransactionDetail->acc_sub_ledger_id = $account_type_name;
            } else {

                $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;
            }

            $saveAccountTransactionDetail->acc_reference_no = $refNo;

            if ($ref_type == 'PF Company' || $ref_type == 'GF Company') {

                $saveAccountTransactionDetail->acc_company_id = $partyId;
                $saveAccountTransactionDetail->acc_employee_id = NULL;

            } else if ($ref_type == 'PF Employee' || $ref_type == 'GF Employee') {

                $saveAccountTransactionDetail->acc_company_id = null;
                $saveAccountTransactionDetail->acc_employee_id = $partyId;

            } else {

                $saveAccountTransactionDetail->acc_company_id = null;
                $saveAccountTransactionDetail->acc_employee_id = NULL;
            }

            $saveAccountTransactionDetail->acc_td_branch_id = $branch_id;
            $saveAccountTransactionDetail->acc_account_group_id = $account_group_id;
            $saveAccountTransactionDetail->acc_account_type_id = $account_type_id;

            $mst_acc_type_data = Accounttype::where('account_type_id','=', "$account_type_id")->get()->first();
            if($mst_acc_type_data->registration_type == null || $mst_acc_type_data->registration_type ==''){
                
                $saveAccountTransactionDetail->registration_type = NULL;

            }else{
                $saveAccountTransactionDetail->registration_type = $mst_acc_type_data->registration_type;

            }
            
            if ($accountLedgerData[$key]['debit_credit'] === 'CR') {

                $saveAccountTransactionDetail->acc_debit_amount = 0;
                $saveAccountTransactionDetail->acc_credit_amount = $accountLedgerData[$key]['amount'];
            } else {

                $saveAccountTransactionDetail->acc_debit_amount = $accountLedgerData[$key]['amount'];
                $saveAccountTransactionDetail->acc_credit_amount = 0;
            }

            if (!$saveAccountTransactionDetail->save()) {

                return 'error';
            }
        }
        return 'success';
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function generateAccountPostingVoucher($voucherDate, $postingAmount, $instrumentNo,
                                                  $instrumentDate, $instrumentBankName, $voucher_number, $accountLedgerData, $voucherNarration,$voucherType)
    {
        try{
            if ($voucherType == 'PV') {
                $voucherType = 'Payment Voucher';
            } else if ($voucherType == 'RV') {
                $voucherType = 'Receipt Voucher';
            } else if ($voucherType == 'JV') {
                $voucherType = 'Journal Voucher';
            } else if ($voucherType == 'MP') {
                $voucherType = 'Manual Posting';
            } else {
                $voucherType = 'Contra Voucher';
            }

            $pdf = App::make('dompdf.wrapper');
            $bladeView = view('reports.accountpostingreport', compact('voucherDate', 'postingAmount',
                'voucher_number', 'instrumentNo', 'instrumentDate', 'instrumentBankName',
                'accountLedgerData', 'voucherNarration','voucherType'));
            $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
            $fileName = 'account_posting_voucher' . random_int(666666, 999999) . '_' . Carbon::now()->format('YmdHis') . '.pdf'; // $genRandomExtension

            if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {

                DB::table('documents')->insert([
                    'doc_type_id' => 410000,
                    'doc_ref_no' => date('Ymd') . random_int(666, 999),
                    'doc_ref_type' => 'accountPostingVoucher',
                    'doc_type' => 'pdf',
                    'doc_path' => $fileName,
                    'doc_date' => Carbon::now()->format('Y-m-d'),
                    'registration_type' => NULL,
                    'doc_user_id' => auth('api')->user()->id
                ]);

                return $fileName;

            } else {

                return 'error';
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }
}
