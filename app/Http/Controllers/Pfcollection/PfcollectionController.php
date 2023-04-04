<?php

namespace App\Http\Controllers\Pfcollection;

use App\Http\Controllers\Controller;
use App\Models\Accounttransaction;
use App\Models\Accounttransactiondetail;
use App\Models\Companyregistration;
use App\Models\Month;
use App\Models\Pfcollection;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NumberToWords\NumberToWords;
Use App\Models\User;
use Spatie\Permission\Traits\HasRoles;
use ESolution\DBEncryption\Encrypter;
use Exception;

class PfcollectionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:collection-list|collection-create|collection-edit|collection-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:collection-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:collection-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:collection-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        try{
            $user_id = auth('api')->user()->id;
            $hasAdminRole = User::where('id','=',$user_id)
            ->whereHas("roles", function($q){
                 $q->where("name", "=","Admin");
                 })->get()->first();
            $current_user_branch =  auth('api')->user()->users_branch_id;

            if ($hasAdminRole != NULL) {
                $col_data = Pfcollection::join('companyregistrations', 'pfcollections.pf_collection_company_account_no_id', '=', 'companyregistrations.company_id')
                  ->join('months', 'pfcollections.pf_collection_for_the_month', '=', 'months.id')
                  ->with('pfGfCollectionBranches')
                  ->with('getDocumentData')
                  //->with('collectionReceiptNo')
                  ->where('pfcollections.pf_collection_effective_end_date', '=', NULL)
                  ->where('pfcollections.pf_collection_status', '=', 'under_process')
                  ->orWhere('pf_collection_status', '=', 'Approved')
                  ->where('companyregistrations.effective_end_date', '=', NULL)
                  ->orderBy('pfcollections.created_at', 'DESC')
                  ->Paginate($perPage = 10, $columns = ['*'], $pageName = 'pages');

                $col_data->transform(function($collection_data) {
                    $collection_data->org_name = Encrypter::decrypt($collection_data->org_name);
                    $collection_data->license_no = Encrypter::decrypt($collection_data->license_no);
                    $collection_data->company_account_no = Encrypter::decrypt($collection_data->company_account_no);
                    $collection_data->phone_no = Encrypter::decrypt($collection_data->phone_no);
                    return  $collection_data;
                });
                return $col_data;

            } else {

                $collection_branch = DB::select("SELECT * FROM pfcollections WHERE pf_collection_branch_id = '$current_user_branch'");
                if (count($collection_branch) == 0) {
                    return response()->json(['success', 'message' => 'No Data Available']);
                }

                $col_data = Pfcollection::join('companyregistrations', 'pfcollections.pf_collection_company_account_no_id', '=', 'companyregistrations.company_id')
                  ->join('months', 'pfcollections.pf_collection_for_the_month', '=', 'months.id')
                  ->with('pfGfCollectionBranches')
                  ->with('getDocumentData')
                  //->with('collectionReceiptNo')
                  ->where('pfcollections.pf_collection_effective_end_date', '=', NULL)
                  ->where('pfcollections.pf_collection_status', '=', 'under_process')
                  ->orWhere('pf_collection_status', '=', 'Approved')
                  ->where('companyregistrations.effective_end_date', '=', NULL)
                  ->orderBy('pfcollections.created_at', 'DESC')
                  ->Paginate($perPage = 10, $columns = ['*'], $pageName = 'pages');

                $col_data->transform(function($collection_data) {
                    $collection_data->org_name = Encrypter::decrypt($collection_data->org_name);
                    $collection_data->license_no = Encrypter::decrypt($collection_data->license_no);
                    $collection_data->company_account_no = Encrypter::decrypt($collection_data->company_account_no);
                    $collection_data->phone_no = Encrypter::decrypt($collection_data->phone_no);
                    return  $collection_data;
                });
                return $col_data;
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function store(Request $request)
    {
        try{
            request()->validate([
            'pf_collection_for_the_month' => 'required',
            'pf_collection_for_the_year' => 'required',
            ]);

            if (count($request->transationalDetail) == 0 || $request->transationalDetail == null) {

                return response()->json(['error', 'message' => 'Please add transaction detail first!']);
            }

            DB::beginTransaction();
            $pfcollection = new Pfcollection();
            $getPfColId = date('YmdH') . random_int(111111, 999999);

            $pfcollection->pf_collection_id = $getPfColId;
            $pfcollection->pf_collection_company_account_no_id = $request->pf_collection_company_account_no_id;
            $pfcollection->pf_collection_branch_id = $request->pf_collection_branch_id;
            $pfcollection->pf_collection_amount = $request->pf_collection_amount;
            $pfcollection->pf_collection_date = $request->pf_collection_date;
            $pfcollection->pf_collection_for_the_month = $request->pf_collection_for_the_month;
            $pfcollection->pf_collection_for_the_year = $request->pf_collection_for_the_year;
            $pfcollection->pf_collection_narration = $request->pf_collection_narration;

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
            $pfcollection->pf_collection_created_by = auth('api')->user()->name;
            $pfcollection->pf_collection_effective_start_date = Carbon::now();
            $pfcollection->pf_collection_effective_end_date = null;
            $pfcollection->registration_type = 'PF';

            if ($pfcollection->save()) {
                $save_tbl_account_transaction = $this->saveAccountTransaction($request, $getPfColId, $getpfCollectionNumber);

                if ($save_tbl_account_transaction == 'error') {

                    DB::rollBack();
                    return response()->json(['error', 'message' => 'Could not save data in account transaction tables!']);
                } else {
                    DB::commit();
                    return response()->json(['success', 'message' => 'success']);
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
            $transaction_detail_data = $request->transationalDetail;
            $arryDocPath = [];

            foreach ($transaction_detail_data as $data) {
                $year = Carbon::now()->year;
                $accountTransaction = new Accounttransaction();
                $accountTrasactionId = date('YmdH') . random_int(1111111, 9999999);
                $accountTransaction->account_transaction_id = (int)$accountTrasactionId;
                $accountTransaction->account_voucher_type = 'RV';
                $accountTransaction->account_voucher_date = $request->pf_collection_date;
                $transaction_mode = $data['accountTransactionMode'];
                $transaction_amount = $data['accountVoucherAmount']; //$data->accountVoucherAmount;

                $accountTransaction->account_transaction_mode = $transaction_mode;
                $accountTransaction->account_voucher_amount = (float)$transaction_amount;
                $accountTransaction->account_voucher_narration = $request->pf_collection_narration;

                $currentYear = date('Y');

                $getAccountTransactionSerialNo = DB::table('masteridholders')
                    ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
                    ->where('id_type', '=', 'RV_Transaction')
                    ->where('registration_type', '=', 'PF')
                    ->where('branch_id', $request->pf_collection_branch_id)
                    ->where('f_year', $year)
                    ->get(['serial_no', 'branch_code'])
                    ->first();

                $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
                $branch_code = $getAccountTransactionSerialNo->branch_code;

                if ($transaction_mode == 'Cash') {

                    $instrument_no = null;
                    $cheque_date = null;
                    $col_bank_name = null;
                    $voucher_number = 'RV/' . $currentYear . '/PF/' . $branch_code . '/CASH/' . $accountTransactionSerialNo;
                    $col_bank_acc_no = null;
                    $acc_account_group_id = 'B0B731C0-421A-11EC-B589-354B57453CBA';
                    $acc_account_type_id = 'E95B7070-421B-11EC-A8F2-819ABFC6F02F';

                } else {

                    $instrument_no = $data['accountCollectionInstrumentNo'];
                    $cheque_date = $data['accountChequeDate'];
                    $col_bank_name = $data['accountCollectionBank'];

                    $bank_data = explode('/', $request->pf_collection_bank_account_no);
                    $acc_account_group_id = 'A7621450-421A-11EC-858F-9BE7FA733BC4';
                    $acc_account_type_id = $bank_data[0];
                    $col_bank_acc_no = $bank_data[1];
                    $voucher_number = 'RV/' . $currentYear . '/PF/' . $branch_code . '/BANK/' . $accountTransactionSerialNo;
                }

                $accountTransaction->account_collection_instrument_no = $instrument_no;
                $accountTransaction->account_cheque_date = $cheque_date;
                $accountTransaction->account_collection_bank = $col_bank_name;
                $accountTransaction->account_voucher_number = $voucher_number;
                $accountTransaction->account_payment_id = null;
                $accountTransaction->account_collection_id = $getPfColId;
                $accountTransaction->account_reference_no = $getpfCollectionNumber;
                $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
                $accountTransaction->account_effective_end_date = null;
                $accountTransaction->account_created_by = auth('api')->user()->name;
                $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');
                $accountTransaction->registration_type = 'PF';

                if ($accountTransaction->save()) {

                    DB::table('masteridholders')
                        ->where('branch_id', '=', $request->pf_collection_branch_id)
                        ->where('f_year', '=', $year)
                        ->where('id_type', '=', 'RV_Transaction')
                        ->where('registration_type', '=', 'PF')
                        ->update(['serial_no' => $accountTransactionSerialNo]);

                    $save_tbl_account_transaction_detail = $this->saveAccountTransactionDetail($request, $accountTrasactionId, $transaction_amount, $col_bank_acc_no,
                        $acc_account_group_id, $acc_account_type_id, $getpfCollectionNumber,
                        $transaction_mode);

                    if ($save_tbl_account_transaction_detail == 'error') {

                        return 'error';

                    } else {
                        $docPath = $this->createCollectionDocument($request, $voucher_number, $transaction_mode,
                            $instrument_no, $cheque_date, $col_bank_name, $col_bank_acc_no,
                            $getPfColId, $transaction_detail_data, $transaction_amount);

                        array_push($arryDocPath, $docPath);
                    }
                } else {

                    return 'error';
                }
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
                $saveAccountTransactionDetail->registration_type = 'PF';

                //  If only in cheque
                $saveAccountTransactionDetail->acc_narration = $request->pf_collection_narration;
                $saveAccountTransactionDetail->acc_reference_no = $pfCollectionNumber;
                $saveAccountTransactionDetail->acc_company_id = $request->pf_collection_company_account_no_id;
                $saveAccountTransactionDetail->acc_employee_id = null;
                $saveAccountTransactionDetail->acc_effective_start_date = Carbon::now()->format('Y-m-d');
                $saveAccountTransactionDetail->acc_effective_end_date = null;
                $saveAccountTransactionDetail->acc_td_branch_id = $request->pf_collection_branch_id;

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

                    if ($transaction_mode === 'Cash') {
                        $saveAccountTransactionDetail->acc_account_group_id = 'B0B731C0-421A-11EC-B589-354B57453CBA';
                        $saveAccountTransactionDetail->acc_account_type_id = 'E95B7070-421B-11EC-A8F2-819ABFC6F02F';
                        $saveAccountTransactionDetail->acc_sub_ledger_id = null;

                    } else {

                        $saveAccountTransactionDetail->acc_account_group_id = $acc_account_group_id;
                        $saveAccountTransactionDetail->acc_account_type_id = $acc_account_type_id;
                        $saveAccountTransactionDetail->acc_sub_ledger_id = $col_bank_acc_no;
                    }
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
                                                     $cheque_date, $col_bank_name, $col_bank_acc_no, $getPfColId,
                                                     $transaction_detail_data, $transaction_amount)
    {
        try{
            $getList = CompanyRegistration::where('company_id', $request->pf_collection_company_account_no_id)
            ->get();
            $getAccountName = $getList->first()->org_name;
            $companyAccountNumber = $getList->first()->company_account_no;

            $getMonth = Month::where('id', $request->pf_collection_for_the_month)
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
                'forTheYear' => $request->pf_collection_for_the_year,
                'numInWords' => $numInWords,
                'companyAccountNumber' => $companyAccountNumber,
                'narration' => $request->pf_collection_narration
            ];

            $pdf = App::make('dompdf.wrapper');
            $bladeView = view('moneyreceipt.moneyreceipt', $data);
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
                'registration_type' => 'PF',
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'doc_path' => $fileName,
                'document_type' => $transaction_mode,
                'doc_user_id' => auth('api')->user()->id
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

    /** Get Collection File */
    public function getCollectionDocument($docpath)
    {
        try{
            $collectionFile = storage_path('app/moneyreceipt/' . $docpath);
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $collectionFile . '"',
            'Access-Control-Expose-Headers' => 'Content-Disposition'
        ];

        $filename = basename($collectionFile);
        return response()->download($collectionFile, $filename, $headers);
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    /** To Check the due amounts by Company ID in PF/GF Collection */
    public function getDueAmountByCompanyId($companyId)
    {
        try{
            $pfCollection = Pfcollection::where('pf_collection_company_account_no_id', $companyId)
            ->orderBy('pf_version_number', 'DESC')
            ->get(['pf_collection_amount', 'pf_collection_for_the_month', 'pf_collection_for_the_year'])->first();

        if ($pfCollection == null) {

            return response()->json('NULL');
        } else {

            return response()->json($pfCollection);
        }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function getCollectionAccoutByBranchId($branchId)
    {
        try{
            return DB::table('accounttypes')
            ->select('account_type_id', 'acc_code', 'acc_name', 'acc_description')
            ->where('account_group_id', 'A7621450-421A-11EC-858F-9BE7FA733BC4')
            //->where('acc_branch_id', $branchId)
            ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function getCollectionReceiptByUserBranch()
    {
        try{
            $user_branch_id = auth('api')->user()->users_branch_id;
            return CompanyRegistration::join('pfcollections', 'pfcollections.pf_collection_company_account_no_id', '=', 'companyregistrations.company_id')
                ->where('pf_collection_branch_id', $user_branch_id)
                ->where('pf_collection_status', '=', 'under_process')
                ->where('companyregistrations.effective_end_date', '=', NULL)
                ->where('companyregistrations.registration_type', '=', 'PF')
                ->with('pfEmployees')
                ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    //** View Deposit */
    public function getCollectionReceiptNo($collectionId)
    {
        try{
            return Pfcollection::with(['collectionCompany' => function ($org_query) {
            return $org_query->with(['pfEmployees' => function ($emp_query) {
                return $emp_query->where('status', '=', 'Active')
                    ->orderBy('pfemployeeregistrations.employee_name', 'ASC')
                    ->get();
            }])
                ->where('effective_end_date', '=', NULL)
                ->where('registration_type', '=', 'PF')
                ->get();
        }])
            ->with('getDocumentData')
            ->where('pf_collection_id', '=', $collectionId)
            ->where('pf_collection_status', '=', 'under_process')
            ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function viewApprovedDeposits($collectionId)
    {
        try{
            return CompanyRegistration::join('pfcollections', 'pfcollections.pf_collection_company_account_no_id', '=', 'companyregistrations.company_id')
            ->where('pf_collection_id', $collectionId)
            ->where('pf_collection_status', '=', 'Approved')
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->with(['getEmployeeDetails' => function ($query) {
                return $query->where('status', '=', 'Active')
                    ->get();
            }])
            ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function getCollectionDataByColId($collection_id)
    {
        try{
            $collection_data = Pfcollection::where('pf_collection_id', '=', $collection_id)->get()->first();
            $collection_account_name = CompanyRegistration::where('company_id', '=', $collection_data->pf_collection_company_account_no_id)->get()->first()->org_name;
            $collectionForTheMonth = Month::where('id', '=', $collection_data->pf_collection_for_the_month)->get()->first()->month_name;
            $collectionCompanyAccountNumber = CompanyRegistration::where('company_id', '=', $collection_data->pf_collection_company_account_no_id)->get()->first()->company_account_no;
            $bussiness_type = Pfcollection::where('pf_collection_id', '=', $collection_id)->get()->first()->registration_type;

            $whole = intval($collection_data->pf_collection_amount);
            $decimal1 = $collection_data->pf_collection_amount - $whole;
            $decimal2 = round($decimal1, 2);
            $get_substring_value = substr($decimal2, 2);
            $convert_to_int = intval($get_substring_value);
            $f = new \NumberFormatter(locale_get_default(), \NumberFormatter::SPELLOUT);
            $word = $f->format($convert_to_int);

            $numberToWords = new NumberToWords();
            $numberTransformer = $numberToWords->getNumberTransformer('en');
            $numWord = $numberTransformer->toWords($collection_data->pf_collection_amount);
            $numInWords = $numWord . ' and chhetrum ' . $word;

            $data = [
                'collectionDate' => $collection_data->pf_collection_date,
                'receiptNumber' => $collection_data->pf_collection_no,
                'colAccountName' => $collection_account_name,
                'colAmount' => $collection_data->pf_collection_amount,
                'colType' => $collection_data->collection_transaction_mode,
                'chequeNo' => $collection_data->collection_instrument_no,
                'colBankName' => $collection_data->bank_name,
                'chequeDate' => $collection_data->collection_cheque_date,
                'forTheMonth' => $collectionForTheMonth,
                'forTheYear' => $collection_data->pf_collection_for_the_year,
                'numInWords' => $numInWords,
                'companyAccountNumber' => $collectionCompanyAccountNumber,
            ];

            $pdf = App::make('dompdf.wrapper');
            $bladeView = view('moneyreceipt.old_moneyreceipt', $data);
            $pdf->loadHTML($bladeView);
            $currentDateTime = Carbon::now()->format('YmdHis');
            $fileName = $collection_data->collection_transaction_mode . '_old_coll_moneyreceipt_' . '_' . $currentDateTime . '_' . random_int(333, 666) . '.pdf';

            $pdf->save(Storage::disk('local')->put($fileName, $pdf->output()));

            DB::table('documents')->insert([
                'doc_type_id' => 239999,
                'doc_ref_no' => $collection_id,
                'doc_ref_type' => 'Collection',
                'doc_type' => 'pdf',
                'registration_type' => $bussiness_type,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'doc_path' => $fileName,
                'document_type' => $collection_data->collection_transaction_mode,
                'doc_user_id' => auth('api')->user()->id
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
}
