<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Accounttransaction;
use App\Models\Branch;
use App\Models\Pfcollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DailyCollectionReportController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:daily-collection-report', ['only' => ['GenerateDailyCollectionReport']]);
    }

    public function GenerateDailyCollectionReport(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $branch_id = $request->branch_id;
        $regType = $request->registration_type;
        $oldCollectionData = NULL;

        if($branch_id == '' || $branch_id == NULL){
            if($request->registration_type == NULL || $request->registration_type == ''){
                $collection_SQLdata = Accounttransaction::with(['pfColectionData' => function ($query) {
                    return $query->with(['getCompanyData' => function($q) {
                        return $q->with('getPfMouDetails');
                    }])
                    ->with('getBranchData')
                        ->get()->first();
                }])
                    ->where('accounttransactions.account_voucher_type', '=', 'RV')
                    ->where('accounttransactions.account_effective_end_date', '=', NULL)
                    ->whereBetween('account_voucher_date', [$from_date, $to_date])
                    ->get();

                if($collection_SQLdata->count() == 0 || empty($collection_SQLdata)){                    
                    $oldCollectionData = Pfcollection::with('collectionCompany')
                    ->with('getBranchData')
                    ->where('pf_collection_effective_end_date', '=', NULL)
                    ->whereBetween('pf_collection_date', [$from_date, $to_date])
                    ->get();
                }
                    
            }else{
                 $collection_SQLdata = Accounttransaction::with(['pfColectionData' => function ($query) use ($regType) {
                    return $query->with(['getCompanyData' => function($q) {
                        return $q->with('getPfMouDetails');
                    }])
                    ->where('pfcollections.registration_type', '=', $regType)
                    ->with('getBranchData')
                    ->get()->first();
                    }])
                    ->where('accounttransactions.registration_type', '=', $regType)
                    ->where('accounttransactions.account_voucher_type', '=', 'RV')
                    ->where('accounttransactions.account_effective_end_date', '=', NULL)
                    ->whereBetween('account_voucher_date', [$from_date, $to_date])
                    ->whereHas('pfColectionData', function ($q) use ($regType) {
                        $q->where('pf_collection_effective_end_date', '=', null)
                            ->where('pfcollections.registration_type', '=', $regType);
                    })
                    ->get();

                    if($collection_SQLdata->count() == 0 || empty($collection_SQLdata)){                    
                        $oldCollectionData = Pfcollection::with('collectionCompany')
                            ->with('getBranchData')
                            ->where('registration_type', '=', $regType)
                            ->where('pf_collection_effective_end_date', '=', NULL)
                            ->whereBetween('pf_collection_date', [$from_date, $to_date])
                            ->get();
                    }
                }

        }else{
                if($request->registration_type == NULL || $request->registration_type == ''){
                    $collection_SQLdata = Accounttransaction::with(['pfColectionData' => function ($query) {
                        return $query->with('getCompanyData')
                            ->with('getBranchData')
                            ->get()->first();
                        }])
                        ->where('accounttransactions.account_voucher_type', '=', 'RV')
                        ->where('accounttransactions.account_effective_end_date', '=', NULL)
                        ->whereBetween('account_voucher_date', [$from_date, $to_date])
                        ->whereHas('pfColectionData', function ($q) use ($branch_id) {
                            $q->where('pf_collection_effective_end_date', '=', NULL)
                                ->where('pf_collection_branch_id', $branch_id);
                        })
                        ->get();
                        
                        if($collection_SQLdata->count() == 0 || empty($collection_SQLdata)){
                            $oldCollectionData = Pfcollection::with('collectionCompany')
                                ->with('getBranchData')
                                ->where('pf_collection_branch_id', '=', $branch_id)
                                ->where('pf_collection_effective_end_date', '=', NULL)
                                ->whereBetween('pf_collection_date', [$from_date, $to_date])
                                ->get();
                        }
                }else{
                    $collection_SQLdata = Accounttransaction::with(['pfColectionData' => function ($query) use ($regType,$branch_id) {
                        return $query->with(['getCompanyData' => function($q) {
                            return $q->with('getPfMouDetails');
                        }])
                        ->where('pfcollections.registration_type', '=', $regType)
                        ->with('getBranchData')
                        ->get()->first();
                        }])
                        ->where('accounttransactions.registration_type', '=', $regType)
                        ->where('accounttransactions.account_voucher_type', '=', 'RV')
                        ->where('accounttransactions.account_effective_end_date', '=', NULL)
                        ->whereBetween('account_voucher_date', [$from_date, $to_date])
                        ->whereHas('pfColectionData', function ($q) use ($regType,$branch_id) {
                            $q->where('pf_collection_effective_end_date', '=', null)
                                ->where('pf_collection_branch_id', $branch_id)
                                ->where('pfcollections.registration_type', '=', $regType);
                        })
                        ->get();

                        if($collection_SQLdata->count() == 0 || empty($collection_SQLdata)){
                            $oldCollectionData = Pfcollection::with('collectionCompany')
                                ->with('getBranchData')
                                ->where('pf_collection_branch_id', '=', $branch_id)
                                ->where('registration_type', '=', $regType)
                                ->where('pf_collection_effective_end_date', '=', NULL)
                                ->whereBetween('pf_collection_date', [$from_date, $to_date])
                                ->get();
                        }
                }
        }   
       
        if ($this->generateDailyCollectionStatement($request, $collection_SQLdata, $oldCollectionData) == 'success') {

            return response()->json(['success', 'message' => 'Daily Collection Report Generated Successfully']);
        } else {

            return response()->json(['error', 'message' => 'Unable to Generate Daily Collection Report']);
        }

       /*      
        if (($branch_id == '' || $branch_id == NULL) && ($request->registration_type == NULL || $request->registration_type == '')) {

            $collection_SQLdata = Accounttransaction::with(['pfColectionData' => function ($query) {
                return $query->with(['getCompanyData' => function($q) {
                    return $q->with('getPfMouDetails');
                }])
                ->with('getBranchData')
                    ->get()->first();
            }])
                ->where('accounttransactions.account_voucher_type', '=', 'RV')
                ->where('accounttransactions.account_effective_end_date', '=', NULL)
                ->whereBetween('account_voucher_date', [$from_date, $to_date])
                ->get();

            if ($collection_SQLdata->count() == 0) {

                $oldCollectionData = Pfcollection::with('collectionCompany')
                    ->with('getBranchData')
                    ->where('pf_collection_effective_end_date', '=', NULL)
                    ->whereBetween('pf_collection_date', [$from_date, $to_date])
                    ->get();

                if ($this->generateDailyCollectionStatement($request, $collection_SQLdata, $oldCollectionData) == 'success') {

                    return response()->json(['success', 'message' => 'Daily Collection Report Generated Successfully']);
                } else {

                    return response()->json(['error', 'message' => 'Unable to Generate Daily Collection Report']);
                }

            } else {

                $oldCollectionData = '';
                if ($this->generateDailyCollectionStatement($request, $collection_SQLdata, $oldCollectionData) == 'success') {

                    return response()->json(['success', 'message' => 'Daily Collection Report Generated Successfully']);
                } else {

                    return response()->json(['error', 'message' => 'Unable to Generate Daily Collection Report']);
                }
            }

        } else if (($branch_id != NULL || $branch_id != '') && ($request->registration_type == NULL || $request->registration_type == '')) {

            $collection_SQLdata = Accounttransaction::with(['pfColectionData' => function ($query) {
                return $query->with('getCompanyData')
                    ->with('getBranchData')
                    ->get()->first();
            }])
                ->where('accounttransactions.account_voucher_type', '=', 'RV')
                ->where('accounttransactions.account_effective_end_date', '=', NULL)
                ->whereBetween('account_voucher_date', [$from_date, $to_date])
                ->whereHas('pfColectionData', function ($q) use ($branch_id) {
                    $q->where('pf_collection_effective_end_date', '=', NULL)
                        ->where('pf_collection_branch_id', $branch_id);
                })
                ->get();

            if ($collection_SQLdata->count() == 0) {

                $oldCollectionData = Pfcollection::with('collectionCompany')
                    ->with('getBranchData')
                    ->where('pf_collection_branch_id', '=', $branch_id)
                    ->where('pf_collection_effective_end_date', '=', NULL)
                    ->whereBetween('pf_collection_date', [$from_date, $to_date])
                    ->get();

                if ($this->generateDailyCollectionStatement($request, $collection_SQLdata, $oldCollectionData) == 'success') {

                    return response()->json(['success', 'message' => 'Daily Collection Report Generated Successfully']);
                } else {

                    return response()->json(['error', 'message' => 'Unable to Generate Daily Collection Report']);
                }

            } else {
                $oldCollectionData = '';
                if ($this->generateDailyCollectionStatement($request, $collection_SQLdata, $oldCollectionData) == 'success') {

                    return response()->json(['success', 'message' => 'Daily Collection Report Generated Successfully']);
                } else {

                    return response()->json(['error', 'message' => 'Unable to Generate Daily Collection Report']);
                }
            }

        } else if (($branch_id == NULL || $branch_id == '') && ($request->registration_type != NULL || $request->registration_type != '')) {

            $regType = $request->registration_type;

            $collection_SQLdata = Accounttransaction::with(['pfColectionData' => function ($query) use ($regType) {
                    return $query->with(['getCompanyData' => function($q) {
                        return $q->with('getPfMouDetails');
                    }])
                    ->where('pfcollections.registration_type', '=', $regType)
                    ->with('getBranchData')
                    ->get()->first();
            }])
                ->where('accounttransactions.registration_type', '=', $regType)
                ->where('accounttransactions.account_voucher_type', '=', 'RV')
                ->where('accounttransactions.account_effective_end_date', '=', NULL)
                ->whereBetween('account_voucher_date', [$from_date, $to_date])
                ->whereHas('pfColectionData', function ($q) use ($regType) {
                    $q->where('pf_collection_effective_end_date', '=', null)
                        ->where('pfcollections.registration_type', '=', $regType);
                })
                ->get();

            // return $collection_SQLdata;

            if (empty($collection_SQLdata)) {

                $oldCollectionData = Pfcollection::with('collectionCompany')
                    ->with('getBranchData')
                    ->where('registration_type', '=', $regType)
                    ->where('pf_collection_effective_end_date', '=', NULL)
                    ->whereBetween('pf_collection_date', [$from_date, $to_date])
                    ->get();

                if ($this->generateDailyCollectionStatement($request, $collection_SQLdata, $oldCollectionData) == 'success') {

                    return response()->json(['success', 'message' => 'Daily Collection Report Generated Successfully']);
                } else {

                    return response()->json(['error', 'message' => 'Unable to Generate Daily Collection Report']);
                }

            } else {
                $oldCollectionData = '';
                if ($this->generateDailyCollectionStatement($request, $collection_SQLdata, $oldCollectionData) == 'success') {

                    return response()->json(['success', 'message' => 'Daily Collection Report Generated Successfully']);
                } else {

                    return response()->json(['error', 'message' => 'Unable to Generate Daily Collection Report']);
                }
            }

        } else {

            $regType = $request->registration_type;
            $collection_SQLdata = Accounttransaction::with(['pfColectionData' => function ($query) {
                return $query->with('getCompanyData')
                    ->with('getBranchData')
                    ->get()->first();
            }])
                ->where('accounttransactions.registration_type', '=', $regType)
                ->where('accounttransactions.account_voucher_type', '=', 'RV')
                ->where('accounttransactions.account_effective_end_date', '=', NULL)
                ->whereBetween('account_voucher_date', [$from_date, $to_date])
                ->whereHas('pfColectionData', function ($q) use ($branch_id) {
                    $q->where('pf_collection_effective_end_date', '=', NULL)
                        ->where('pf_collection_branch_id', '=', $branch_id);
                })
                ->get();

            if (empty($collection_SQLdata)) {

                $oldCollectionData = Pfcollection::with('collectionCompany')
                    ->with('getBranchData')
                    ->where('pf_collection_branch_id', '=', $branch_id)
                    ->where('registration_type', '=', $regType)
                    ->where('pf_collection_effective_end_date', '=', NULL)
                    ->whereBetween('pf_collection_date', [$from_date, $to_date])
                    ->get();

                if ($this->generateDailyCollectionStatement($request, $collection_SQLdata, $oldCollectionData) == 'success') {

                    return response()->json(['success', 'message' => 'Daily Collection Report Generated Successfully']);
                } else {

                    return response()->json(['error', 'message' => 'Unable to Generate Daily Collection Report']);
                }

            } else {
                $oldCollectionData = '';
                if ($this->generateDailyCollectionStatement($request, $collection_SQLdata, $oldCollectionData) == 'success') {

                    return response()->json(['success', 'message' => 'Daily Collection Report Generated Successfully']);
                } else {

                    return response()->json(['error', 'message' => 'Unable to Generate Daily Collection Report']);
                }
            }
        }
        */


    }

    public function generateDailyCollectionStatement(Request $request, $collection_SQLdata, $oldCollectionData)
    {

        $regType = $request->registration_type;

        if (empty($request->branch_id)) {

            $daily_data_blade = [
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'processing_date' => Carbon::now()->format('Y/m/d'),
                'collection_data' => $collection_SQLdata,
                'branch_name' => 'ALL',
                'registrationType' => $regType,
                'old_collection_data' => $oldCollectionData
            ];

        } else {

            $get_branch_name = Branch::where('id', $request->branch_id)
                ->get();
            $getBranchName = $get_branch_name[0]->branch_name;

            $daily_data_blade = [
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'processing_date' => Carbon::now()->format('Y/m/d'),
                'collection_data' => $collection_SQLdata,
                'branch_name' => $getBranchName,
                'registrationType' => $regType,
                'old_collection_data' => $oldCollectionData
            ];
        }

        $pdf = App::make('dompdf.wrapper');
        $bladeView = view('reports.dailyreport', $daily_data_blade);
        $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
        $genRandomExtension = random_int(666666, 999999);
        $currentDateTime = Carbon::now()->format('YmdHis');
        $fileName = 'daily_statement_report_' . $genRandomExtension . '_' . $currentDateTime . '.pdf';

        if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {

            DB::table('documents')->insert([
                'doc_type_id' => 900000,
                'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                'doc_ref_type' => 'DailyColStatement',
                'doc_type' => 'pdf',
                'doc_path' => $fileName,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'registration_type' => $request->registration_type,
                'doc_user_id' => auth('api')->user()->id
            ]);

            return 'success';
        } else {

            return 'error';
        }
    }
}
