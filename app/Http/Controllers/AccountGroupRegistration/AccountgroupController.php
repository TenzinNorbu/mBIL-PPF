<?php

namespace App\Http\Controllers\AccountGroupRegistration;

use App\Http\Controllers\Controller;
use App\Models\Accountgroup;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;
use Exception;
use Illuminate\Support\Facades\DB;

class AccountgroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:accountgroup-list|accountgroup-create|accountgroup-edit|accountgroup-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:accountgroup-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:accountgroup-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:accountgroup-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
    try{
        $accountGroup = DB::table('accountgroups')->get();
        return $accountGroup ? $this->sendResponse($accountGroup,'Account group Details'):$this->sendError('Account group not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function store(Request $request)
    {
        try{
            $this->validate($request, [
                'account_group_code' => 'required',
                'account_group_name' => 'required|string',
                'branch_wise' => 'required',
            ]);
        $accountGroupId = Uuid::generate();        
        $accountgroup = new Accountgroup();
        $accountgroup->account_group_id = $accountGroupId;
        $accountgroup->account_group_code = $request->account_group_code;
        $accountgroup->account_group_name = $request->account_group_name;
        $accountgroup->branch_wise = $request->branch_wise;
 
        if ($accountgroup->save()) {
            $accGroupFindById = Accountgroup::find($accountGroupId);
            return response()->json(['success','message'=>'Success adding account group']);
        } else {
            return response()->json(['error','message'=>'Error adding account group']);
        }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function show($id)
    {
    try{
        $accountGroup = Accountgroup::find($id);
        return $accountGroup ? $this->sendResponse($accountGroup,'Account Group Details'):$this->sendError('Account group not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function edit($id)
    {
        $accountGroup = Accountgroup::find($id);
        return response()->json($accountGroup);
    }

    public function update(Request $request, $id)
    {
        try{
            $this->validate($request, [
                'account_group_code' => 'required',
                'account_group_name' => 'required|string',
                'branch_wise' => 'required',
            ]);
        $accountGroup = Accountgroup::find($id);
        $accountGroup->account_group_code = $request->account_group_code;
        $accountGroup->account_group_name = $request->account_group_name;
        $accountGroup->branch_wise = $request->branch_wise;

        if ($accountGroup->save()) {

            return response()->json(['success','message'=>'Account Group updated successfully']);
        } else {

            return response()->json(['error','message'=>'error updating the Account group']);
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function destroy($id)
    {
        try{
            $accountGroup = Accountgroup::find($id);
        if (empty($accountGroup)) {
            return "Data Not Found. ";
        }
        if ($accountGroup->delete()) {
            return response()->json(['success','message'=>'Account group deleted successfully']);
        } else {
            return response()->json(['error','message'=>'Error deleting the Account group']);
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }
}
