<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Pfcollection;
use App\Models\Companyregistration;
use App\Models\Accounttransaction;
use App\Models\Accounttransactiondetail;
use App\Models\Month;
use NumberToWords\NumberToWords;

class CollectionController extends Controller
{
    public function createCollection(Request $request)
    {
        try{
            request()->validate([
            'registration_type' => 'required',
            'pf_collection_amount' => 'required',
            'pf_collection_company_account_no_id' => 'required',
            'accountCollectionInstrumentNo' => 'required',
            'bank_name' => 'required',
            'user_id'=> 'required',
            'pf_collection_bank_account_no' =>'required'
            ]);

            DB::beginTransaction();
            $pfcollection = new Pfcollection();
            $getPfColId = date('YmdH') . random_int(111111, 999999);

            $pfcollection->pf_collection_id = $getPfColId;
            $pfcollection->pf_collection_company_account_no_id = $request->pf_collection_company_account_no_id;
            $pfcollection->pf_collection_amount = $request->pf_collection_amount;
            $pfcollection->pf_collection_date = Carbon::now()->format('Y-m-d');
            $pfcollection->pf_collection_for_the_month = Carbon::now()->format('m');
            $pfcollection->pf_collection_for_the_year = Carbon::now()->format('Y');
            $pfcollection->pf_collection_narration = "mBIL Collection";
 
            /** Random PF-Collection Number $randomNumber */
            $randomNumber = random_int(100000, 999999);
            $getpfCollectionNumber = 'COL' . date("Ymd") . $randomNumber;
            $pfcollection->pf_collection_no = $getpfCollectionNumber;

            $pfcollection->pf_collection_status = 'under_process';

            $verNo = DB::table("pfcollections")
                ->where("pf_collection_company_account_no_id", "=", $request->pf_collection_company_account_no_id)
                ->orderByDesc("pf_version_number")
                ->get(['pf_collection_amount', 'pf_collection_for_the_month', 'pf_collection_for_the_year', 'pf_version_number'])->first();
               
            if ($verNo == null) {
              
                $version_no = 1;
            } else {

                $version_no = $verNo->pf_version_number + 1;
            }

            $pfcollection->pf_version_number = $version_no;
            $pfcollection->pf_collection_created_by = $request->pf_collection_company_account_no_id;
            $pfcollection->pf_collection_effective_start_date = Carbon::now();
            $pfcollection->pf_collection_effective_end_date = null;
            $pfcollection->registration_type = $request->registration_type;
            // return $pfcollection;
            if ($pfcollection->save()) {
             $save_tbl_account_transaction = $this->saveAccountTransaction($request, $getPfColId, $getpfCollectionNumber);
                if ($save_tbl_account_transaction == 'error') {

                    DB::rollBack();
                    return response()->json(['error', 'message' => 'Could not save data in account transaction tables!']);
                } else {
                    DB::commit();
                    return response()->json(['status'=>'success', 'message' => 'Collection created successfully']);
                }

            } else {
                return response()->json(['error', 'message' => 'Could not save data in collection table!']);
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function saveAccountTransaction(Request $request, $getPfColId, $getpfCollectionNumber)
    {
        try{
            $arryDocPath = [];
                $year = Carbon::now()->year;
                $accountTransaction = new Accounttransaction();
                $accountTrasactionId = date('YmdH') . random_int(1111111, 9999999);
                $accountTransaction->account_transaction_id = (int)$accountTrasactionId;
                $accountTransaction->account_voucher_type = 'RV';
                $accountTransaction->account_voucher_date = Carbon::now()->format('Y-m-d');
                $transaction_mode = 'mBIL';
                $transaction_amount = $request->pf_collection_amount; //$data->accountVoucherAmount;

                $accountTransaction->account_transaction_mode = $transaction_mode;
                $accountTransaction->account_voucher_amount = (float)$transaction_amount;
                $accountTransaction->account_voucher_narration = "mBIL Collection";

                $currentYear = date('Y');

                $getAccountTransactionSerialNo = DB::table('masteridholders')
                    ->orwhere('id_type', '=', 'RV_Transaction')
                    ->orwhere('id_type', '=', 'GF_RV_Transaction')
                    ->where('registration_type', '=', $request->registration_type)
                    ->where('f_year', $year)
                    ->get(['serial_no'])
                    ->first();
                // return $getAccountTransactionSerialNo;
                $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
                $branch_code = "mBIL";
                    $instrument_no = $request->accountCollectionInstrumentNo;
                    $cheque_date = Carbon::now();
                    $col_bank_name = $request->bank_name;

                    $bank_data = explode('/', $request->pf_collection_bank_account_no);
                    $acc_account_group_id = 'A7621450-421A-11EC-858F-9BE7FA733BC4';
                    $acc_account_type_id = $bank_data[0];
                    $col_bank_acc_no = $bank_data[1];
                    $voucher_number = 'RV/' . $currentYear . '/'.$request->registration_type.'/' . $branch_code . '/BANK/' . $accountTransactionSerialNo;
                

                $accountTransaction->account_collection_instrument_no =  $instrument_no;
                $accountTransaction->account_cheque_date = $cheque_date;
                $accountTransaction->account_collection_bank = $col_bank_name;
                $accountTransaction->account_voucher_number = $voucher_number;
                $accountTransaction->account_payment_id = null;
                $accountTransaction->account_collection_id = $getPfColId;
                $accountTransaction->account_reference_no = $getpfCollectionNumber;
                $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
                $accountTransaction->account_effective_end_date = null;
                $accountTransaction->account_created_by = $request->user_id;
                $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');
                $accountTransaction->registration_type = $request->registration_type;

                if ($accountTransaction->save()) {

                    DB::table('masteridholders')
                        ->where('f_year', '=', $year)
                        ->where('id_type', '=', 'RV_Transaction')
                        ->where('registration_type', '=',$request->registration_type)
                        ->update(['serial_no' => $accountTransactionSerialNo]);

                    $save_tbl_account_transaction_detail = $this->saveAccountTransactionDetail($request, $accountTrasactionId, $transaction_amount, $col_bank_acc_no,
                        $acc_account_group_id, $acc_account_type_id, $getpfCollectionNumber,
                        $transaction_mode);

                    if ($save_tbl_account_transaction_detail == 'error') {
                        return 'error';

                    } else {
                        $docPath = $this->createCollectionDocument($request, $voucher_number, $transaction_mode,
                            $instrument_no, $cheque_date, $col_bank_name, $col_bank_acc_no,
                            $getPfColId, $transaction_amount);

                        array_push($arryDocPath, $docPath);
                       
                    }
            }
            else {

                return 'error';
            }
            return $arryDocPath;
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function saveAccountTransactionDetail(Request $request, $accountTrasactionId, $transaction_amount,
    $col_bank_acc_no, $acc_account_group_id, $acc_account_type_id,
    $pfCollectionNumber, $transaction_mode)
        {
        try{
        $transactionCategory = ['PPFCollection', 'Bank'];

        foreach ($transactionCategory as $transaction) {
        $saveAccountTransactionDetail = new Accounttransactiondetail();
        $genAccountTransactionId = date('YmdH') . random_int(1111111, 9999999);
        $saveAccountTransactionDetail->acc_transaction_detail_id = $genAccountTransactionId;
        $saveAccountTransactionDetail->acc_transaction_type_id = $accountTrasactionId;
        $saveAccountTransactionDetail->registration_type = $request->registration_type;

        //  If only in cheque
        $saveAccountTransactionDetail->acc_narration = "mBIL collection";
        $saveAccountTransactionDetail->acc_reference_no = $pfCollectionNumber;
        $saveAccountTransactionDetail->acc_company_id = $request->pf_collection_company_account_no_id;
        $saveAccountTransactionDetail->acc_employee_id = null;
        $saveAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
        $saveAccountTransactionDetail->acc_effective_end_date = null;
        $saveAccountTransactionDetail->acc_td_branch_id = null;

        if ($transaction === 'PPFCollection') {
        $saveAccountTransactionDetail->acc_debit_amount = 0;
        $saveAccountTransactionDetail->acc_credit_amount = $transaction_amount;

        /* PPF Received Account */
        $saveAccountTransactionDetail->acc_account_group_id = '9F1D02A0-421A-11EC-92A6-BB4CCAAF9B33';
        $saveAccountTransactionDetail->acc_account_type_id = 'FDDA5B70-421A-11EC-A70A-5FD74528FECD';
        $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;

        } else {

        $saveAccountTransactionDetail->acc_debit_amount = $transaction_amount;
        $saveAccountTransactionDetail->acc_credit_amount = 0;

        $saveAccountTransactionDetail->acc_account_group_id = $acc_account_group_id;
        $saveAccountTransactionDetail->acc_account_type_id = $acc_account_type_id;
        $saveAccountTransactionDetail->acc_sub_ledger_id = $col_bank_acc_no;
      }

        if ($saveAccountTransactionDetail->save()) {
            return 'success';
        } else {
        return 'error';
        }
    }

        }catch(Exception $e){
        return $this->errorResponse('Page not found');
        }
    }

    public function createCollectionDocument(Request $request, $voucher_number, $transaction_mode, $instrument_no,
    $cheque_date, $col_bank_name, $col_bank_acc_no, $getPfColId, $transaction_amount)
    {
        try{
        $getList = CompanyRegistration::where('company_id', $request->pf_collection_company_account_no_id)
        ->get();
        $getAccountName = $getList->first()->org_name;
        $companyAccountNumber = $getList->first()->company_account_no;

        $getMonth = Month::where('id', Carbon::now()->format('m'))
        ->get();
        $forTheMonth = $getMonth->first()->month_name;

        $whole = intval($transaction_amount);
        $decimal1 = $transaction_amount - $whole;
        $decimal2 = round($decimal1, 2);
        $get_substring_value = substr($decimal2, 2);
        $convert_to_int = intval($get_substring_value);
        $f = new \NumberFormatter(locale_get_default(), \NumberFormatter::SPELLOUT);
        $word = $f->format($convert_to_int);

        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('en');
        $numWord = $numberTransformer->toWords($transaction_amount);
        $numInWords = $numWord . ' and chhetrum ' . $word;

        $data = [
        'collectionDate' => $request->pf_collection_date,
        'receiptNumber' => $voucher_number,
        'colAccountName' => $getAccountName,
        'colAmount' => $transaction_amount,
        'colType' => $transaction_mode,
        'chequeNo' => $instrument_no,
        'colBankName' => $col_bank_name,
        'chequeDate' => $cheque_date,
        'collectionBankAccount' => $col_bank_acc_no,
        'forTheMonth' => $forTheMonth,
        'forTheYear' => Carbon:: now()->format('Y'),
        'numInWords' => $numInWords,
        'companyAccountNumber' => $companyAccountNumber,
        'narration' => 'mBIL Collection',
        'registration_type'=>$request->registration_type
        ];

        $pdf = App::make('dompdf.wrapper');
        $bladeView = view('moneyreceipt.mBILmoneyreceipt', $data);
        $pdf->loadHTML($bladeView);
        $currentDateTime = Carbon::now()->format('YmdHis');
        $strReplaceVoucherNo = str_replace('/', '_', $voucher_number);
        $fileName = $transaction_mode . '_coll_moneyreceipt_' . $strReplaceVoucherNo . '_' . $currentDateTime . '.pdf';

        $pdf->save(Storage::disk('local')->put($fileName, $pdf->output()));

        DB::table('documents')->insert([
        'doc_type_id' => 100000,
        'doc_ref_no' => $getPfColId,
        'doc_ref_type' => 'Collection',
        'doc_type' => 'pdf',
        'registration_type' => $request->registration_type,
        'doc_date' => Carbon::now()->format('Y-m-d'),
        'doc_path' => $fileName,
        'document_type' => $transaction_mode,
        'doc_user_id' => $request->user_id
        ]);

        $actualRefundFileName = storage_path('app/moneyreceipt/' . $fileName);

        $headers = [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="' . $actualRefundFileName . '"',
        'Access-Control-Expose-Headers' => 'Content-Disposition'
        ];

        $getRefundBaseFilename = basename($actualRefundFileName);

          return response()->download($actualRefundFileName, $getRefundBaseFilename, $headers);
        }catch(Exception $e){
           return $this->errorResponse('Page not found');
        }
    }
    public function getCollectionAccoutByBranchId()
    {
        try{
            $bank_data = DB::table('accounttypes')
            ->select('account_type_id', 'acc_code', 'acc_name', 'acc_description')
            ->where('account_group_id', 'A7621450-421A-11EC-858F-9BE7FA733BC4')
            ->where('acc_description', 'like', '%collection%')
        
            ->get();
            return $bank_data ? $this->sendResponse($bank_data,'Bank details'):$this->sendError('Bank details not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }

        
    }
}
