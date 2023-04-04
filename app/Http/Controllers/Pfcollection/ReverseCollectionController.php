<?php

namespace App\Http\Controllers\Pfcollection;

use App\Http\Controllers\Controller;
use App\Models\Accounttransaction;
use App\Models\Accounttransactiondetail;
use App\Models\Pfcollection;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Exception;

class ReverseCollectionController extends Controller
{
//   function __construct()
//   {
//       $this->middleware('permission:reverse-collections', ['only' => ['ReverseCollections']]);
//   }

    public function ReverseCollection(Request $request)
    {
       try{
        DB::beginTransaction();
        $collectionId = $request->pf_collection_id;
        $collection_detail = Pfcollection::where('pf_collection_id', '=', $collectionId)
            ->where('pf_collection_status', '=', 'under_process')
            ->where('pf_collection_effective_end_date', '=', NULL)
            ->get()->first();

        if($collection_detail == null){

            return response()->json(['error', 'message' => 'problem']);
        }

        $updatePfcollection = DB::table('pfcollections')
            ->where('pf_collection_id', '=', $collectionId)
            ->where('pf_collection_status', '=', 'under_process')
            ->update(['pf_collection_effective_end_date' => Carbon::now()->format('Y-m-d')]);

        if ($updatePfcollection) {
            if ($this->reverseAccountTransactions($collectionId) == 'error') {
                DB::rollBack();
                return response()->json(['error', 'message' => 'Collection account transaction colud not Reversed']);

            } else{

                DB::commit();
                return response()->json(['success', 'message' => 'Collection account transaction reversed successfully']);
            }

        } else {

            DB::rollBack();
            return response()->json(['error', 'message' => 'Unable to reverse collection']);
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }

    }

    public function reverseAccountTransactions($collectionId)
    {

        try{
            $getAccountTransactionData = Accounttransaction::where('account_collection_id', '=', $collectionId)
                    ->where('account_effective_end_date', '=', NULL)->get()->all();
      
        if(count($getAccountTransactionData) <= 0){
            return 'error';
        }

        if (DB::table('accounttransactions')
                ->where('account_collection_id', '=', $collectionId)
                ->update(['account_effective_end_date' => Carbon::now()->format('Y-m-d')])) {

                    foreach ($getAccountTransactionData as $key => $data){

                        $account_transaction_id = $data['account_transaction_id'];

                        $update_tbl_accounting_transaction_detail = Accounttransactiondetail::where('acc_transaction_type_id', '=', $account_transaction_id)
                                ->update(['acc_effective_end_date' => Carbon::now()->format('Y-m-d')]);

                        if(!$update_tbl_accounting_transaction_detail){

                            return 'error';
                        }

                    }

                    return 'success';
        }else{

            return 'error';
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }

    }

    public function reverseCollectionModeData($collection_id)
    {
        try{
            return Pfcollection::where('pf_collection_id', '=', $collection_id)
            ->where('pf_collection_status', '=', 'under_process')
            ->with('colAcctTrsReverseData')
            ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }
}
