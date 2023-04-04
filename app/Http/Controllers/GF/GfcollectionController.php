<?php

namespace App\Http\Controllers\GF;

use App\Http\Controllers\Controller;
use App\Models\Accounttransaction;
use App\Models\Accounttransactiondetail;
use App\Models\Accounttype;
use App\Models\Companyregistration;
use App\Models\Month;
use Auth;
use App\Models\Pfcollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NumberToWords\NumberToWords;
Use App\Models\User;
use Spatie\Permission\Traits\HasRoles;

class GfcollectionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:collection-list|collection-create|collection-edit|collection-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:collection-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:collection-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:collection-delete', ['only' => ['destroy']]);
    }

    public function store(Request $request)
    {
        request()->validate([
            'pf_collection_for_the_month' => 'required',
            'pf_collection_for_the_year' => 'required',
        ]);

        if (count($request->transationalDetail) == 0 || ($request->transationalDetail) == null) {

            return response()->json(['error', 'message' => 'Please add transaction detail first!']);
        }

        DB::beginTransaction();
        $gfcollection = new Pfcollection();
        $getPfColId = date('YmdH') . random_int(666666, 999999);
        $gfcollection->pf_collection_id = $getPfColId;

        $gfcollection->pf_collection_company_account_no_id = $request->pf_collection_company_account_no_id;
        $gfcollection->pf_collection_branch_id = $request->pf_collection_branch_id;
        $gfcollection->pf_collection_amount = $request->pf_collection_amount;
        $gfcollection->pf_collection_date = $request->pf_collection_date;
        $gfcollection->pf_collection_for_the_month = $request->pf_collection_for_the_month;
        $gfcollection->pf_collection_for_the_year = $request->pf_collection_for_the_year;
        $gfcollection->pf_collection_narration = $request->pf_collection_narration;

        /** GF-Collection Number */
        $randomNumber = random_int(100000, 999999);
        $getpfCollectionNumber = 'COL' . date("YmdH") . $randomNumber;
        $gfcollection->pf_collection_no = $getpfCollectionNumber;

        $gfcollection->pf_collection_status = 'under_process';

        $verNo = DB::table("pfcollections")
            ->where('pf_collection_company_account_no_id', '=', $request->pf_collection_company_account_no_id)
            ->orderByDesc("pf_version_number")
            ->get(['pf_collection_amount', 'pf_collection_for_the_month', 'pf_collection_for_the_year', 'pf_version_number'])->first();

        if ($verNo == null) {

            $version_no = 1;
        } else {

            $version_no = $verNo->pf_version_number + 1;
        }

        $gfcollection->pf_version_number = $version_no;
        $gfcollection->pf_collection_created_by = auth('api')->user()->name;
        $gfcollection->pf_collection_effective_start_date = Carbon::now();
        $gfcollection->pf_collection_effective_end_date = null;
        $gfcollection->registration_type = 'GF';

        if ($gfcollection->save()) {
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
    }

    public function saveAccountTransaction(Request $request, $getPfColId, $getpfCollectionNumber)
    {
        $transaction_detail_data = $request->transationalDetail;

        $arryDocPath = [];

        foreach ($transaction_detail_data as $data) {
            $year = Carbon::now()->year;
            $accountTransaction = new Accounttransaction();
            $accountTrasactionId = date('Ymd') . random_int(666666, 999999);
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
                ->where('id_type', '=', 'GF_RV_Transaction')
                ->where('registration_type', '=', 'GF')
                ->where('branch_id', $request->pf_collection_branch_id)
                ->where('f_year', $year)
                ->get(['serial_no', 'branch_code'])
                ->first();

            $accountTransactionSerialNo = (int)$getAccountTransactionSerialNo->serial_no + 1;
            $branch_code = $getAccountTransactionSerialNo->branch_code;

            if ($transaction_mode == 'Cash') {

                $instrument_no = NULL;
                $cheque_date = NULL;
                $col_bank_name = NULL;
                $voucher_number = 'RV/' . $currentYear . '/GF/' . $branch_code . '/CASH/' . $accountTransactionSerialNo;
                $col_bank_acc_no = NULL;

                $acc_account_group_id = 'B0B731C0-421A-11EC-B589-354B57453CBA';
                $acc_account_type_id = '5E9EE140-3D68-11ED-8415-E38523D22AC7';

            } else {

                $instrument_no = $data['accountCollectionInstrumentNo'];
                $cheque_date = $data['accountChequeDate'];
                $col_bank_name = $data['accountCollectionBank'];

                $bank_data = explode('/', $request->pf_collection_bank_account_no);
                $acc_account_group_id = 'A7621450-421A-11EC-858F-9BE7FA733BC4';
                $acc_account_type_id = $bank_data[0];
                $col_bank_acc_no = $bank_data[1];

                $voucher_number = 'RV/' . $currentYear . '/GF/' . $branch_code . '/BANK/' . $accountTransactionSerialNo;
            }

            $accountTransaction->account_collection_instrument_no = $instrument_no;
            $accountTransaction->account_cheque_date = $cheque_date;
            $accountTransaction->account_collection_bank = $col_bank_name;
            $accountTransaction->account_voucher_number = $voucher_number;

            $accountTransaction->account_payment_id = NULL;
            $accountTransaction->account_collection_id = $getPfColId;
            $accountTransaction->account_reference_no = $getpfCollectionNumber;
            $accountTransaction->account_effective_start_date = Carbon::now()->format('Y-m-d');
            $accountTransaction->account_effective_end_date = NULL;
            $accountTransaction->account_created_by = auth('api')->user()->name;
            $accountTransaction->account_created_date = Carbon::now()->format('Y-m-d');
            $accountTransaction->registration_type = 'GF';

            if ($accountTransaction->save()) {

                DB::table('masteridholders')
                    ->where('branch_id', '=', $request->pf_collection_branch_id)
                    ->where('f_year', '=', $year)
                    ->where('id_type', '=', 'GF_RV_Transaction')
                    ->where('registration_type', '=', 'GF')
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
    }

    public function saveAccountTransactionDetail(Request $request, $accountTrasactionId, $transaction_amount,
                                                         $col_bank_acc_no, $acc_account_group_id, $acc_account_type_id,
                                                         $pfCollectionNumber, $transaction_mode)
    {
        $transactionCategory = ['PPFCollection', 'Bank'];

        foreach ($transactionCategory as $transaction) {
            $saveAccountTransactionDetail = new Accounttransactiondetail();
            $genAccountTransactionId = date('Ymd') . random_int(666666, 999999);
            $saveAccountTransactionDetail->acc_transaction_detail_id = $genAccountTransactionId;

            $saveAccountTransactionDetail->acc_transaction_type_id = $accountTrasactionId;
            $saveAccountTransactionDetail->registration_type = 'GF';
            $saveAccountTransactionDetail->acc_narration = $request->pf_collection_narration;
            $saveAccountTransactionDetail->acc_reference_no = $pfCollectionNumber;
            $saveAccountTransactionDetail->acc_company_id = $request->pf_collection_company_account_no_id;
            $saveAccountTransactionDetail->acc_employee_id = NULL;
            $saveAccountTransactionDetail->acc_effective_start_date = Carbon::now();
            $saveAccountTransactionDetail->acc_effective_end_date = NULL;
            $saveAccountTransactionDetail->acc_td_branch_id = $request->pf_collection_branch_id;

            // GF Collection
            if ($transaction === 'PPFCollection') {
                $saveAccountTransactionDetail->acc_debit_amount = 0;
                $saveAccountTransactionDetail->acc_credit_amount = $transaction_amount;
                /* GF Received Account */
                $saveAccountTransactionDetail->acc_account_group_id = '9F1D02A0-421A-11EC-92A6-BB4CCAAF9B33';
                $saveAccountTransactionDetail->acc_account_type_id = '379A35D0-C3BB-11EC-B51B-79FB79A98A69';
                $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;

            } else {


                $saveAccountTransactionDetail->acc_credit_amount = 0;
                $saveAccountTransactionDetail->acc_debit_amount = $transaction_amount;

                if ($transaction_mode === 'Cash') {

                    $saveAccountTransactionDetail->acc_account_group_id = 'B0B731C0-421A-11EC-B589-354B57453CBA';
                    $saveAccountTransactionDetail->acc_account_type_id = '5E9EE140-3D68-11ED-8415-E38523D22AC7';
                    $saveAccountTransactionDetail->acc_sub_ledger_id = NULL;

                } else {

                    $saveAccountTransactionDetail->acc_account_group_id = $acc_account_group_id;
                    $saveAccountTransactionDetail->acc_account_type_id = $acc_account_type_id;
                    $saveAccountTransactionDetail->acc_sub_ledger_id = $col_bank_acc_no;
                }
            }

            if ($saveAccountTransactionDetail->save()) {

            } else {
                return 'error';
            }
        }
        return 'success';
    }

    public function createCollectionDocument(Request $request, $voucher_number, $transaction_mode,
                                                     $instrument_no, $cheque_date, $col_bank_name, $col_bank_acc_no,
                                                     $getPfColId, $transaction_detail_data, $transaction_amount)
    {

        $getList = CompanyRegistration::where('company_id', $request->pf_collection_company_account_no_id)
            ->get();
        $getAccountName = $getList[0]->org_name;
        $companyAccountNumber = $getList[0]->company_account_no;

        $forTheMonth = Month::where('id', $request->pf_collection_for_the_month)
            ->get()->first()->month_name;

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
        $bladeView = view('moneyreceipt.gf_moneyreceipt', $data);
        $pdf->loadHTML($bladeView);
        $fileName = $transaction_mode . '_coll_moneyreceipt_' . str_replace('/', '_', $voucher_number) . '_' . Carbon::now()->format('YmdHis') . '.pdf';

        if ($pdf->save(Storage::disk('local')->put($fileName, $pdf->output()))) {
            DB::table('documents')->insert([
                'doc_type_id' => 100000,
                'doc_ref_no' => $getPfColId,
                'doc_ref_type' => 'Collection',
                'doc_type' => 'pdf',
                'registration_type' => 'GF',
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

            $docPath = basename($actualRefundFileName);
            return response()->download($actualRefundFileName, $docPath, $headers);

        } else {

            return response()->json(['error', 'message' => 'Could not generate the GF Collection Document']);
        }
    }

    /** Get Collection File */
    public function getCollectionDocument($docpath)
    {
        $collectionFile = storage_path('app/moneyreceipt/' . $docpath);
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $collectionFile . '"',
            'Access-Control-Expose-Headers' => 'Content-Disposition'
        ];

        $filename = basename($collectionFile);
        return response()->download($collectionFile, $filename, $headers);
    }

    /** To Check the due amounts by Company ID in PF/GF Collection */
    public function getDueAmountByCompanyId($companyId)
    {
        $pfCollection = Pfcollection::where('pf_collection_company_account_no_id', $companyId)
            ->orderBy('pf_version_number', 'DESC')
            ->get(['pf_collection_amount', 'pf_collection_for_the_month', 'pf_collection_for_the_year'])->first();

        if ($pfCollection == NULL) {

            return response()->json('NULL');
        } else {

            return response()->json($pfCollection);
        }
    }

    public function getCollectionAccoutByBranchId($branchId)
    {
        return Accounttype::select('account_type_id', 'acc_code', 'acc_name', 'acc_description')
            ->where('account_group_id', 'A7621450-421A-11EC-858F-9BE7FA733BC4')
            ->where('acc_branch_id', $branchId)
            ->get();
    }

    public function getCollectionReceiptByUserBranch()
    {
        $user_branch_id = auth('api')->user()->users_branch_id;
        return CompanyRegistration::join('pfcollections', 'pfcollections.pf_collection_company_account_no_id', '=', 'companyregistrations.company_id')
            ->where('pf_collection_branch_id', $user_branch_id)
            ->where('pf_collection_status', '=', 'under_process')
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->where('companyregistrations.registration_type', '=', 'GF')
            ->with('pfEmployees')
            ->get();
    }

    //** View Deposit */
    public function getCollectionReceiptNo($collectionId)
    {
        return Pfcollection::with(['collectionCompany' => function ($org_query) {
            return $org_query->with(['gfEmployees' => function ($emp_query) {
                return $emp_query->where('status', '=', 'Active')
                    ->orderBy('pfemployeeregistrations.employee_name', 'ASC')
                    ->get();
            }])
                ->where('effective_end_date', '=', NULL)
                ->where('registration_type', '=', 'GF')
                ->get();
        }])
            ->with('getDocumentData')
            ->where('pf_collection_id', '=', $collectionId)
            ->where('pf_collection_status', '=', 'under_process')
            ->get();
    }

}
