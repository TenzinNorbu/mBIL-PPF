<?php

namespace App\Http\Controllers\StakeholderRegistration;

use App\Http\Controllers\Controller;
use App\Models\Stakeholder;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use ESolution\DBEncryption\Encrypter;

class StakeholderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:stakeholder-list|stakeholder-create|stakeholder-edit|stakeholder-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:stakeholder-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:stakeholder-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:stakeholder-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        try{
            $stakeholders = DB::table('stakeholders')->get();
            $stakeholders->transform(function($data) {
                $data->stakeholder_name = Encrypter::decrypt($data->stakeholder_name);
                $data->stakeholder_party_type = Encrypter::decrypt($data->stakeholder_party_type);
                $data->employee_id = Encrypter::decrypt($data->employee_id);
                $data->tpn_no = Encrypter::decrypt($data->tpn_no);
                return  $data;
            });
        return $stakeholders ? $this->sendResponse($stakeholders,'Stakeholder Details'):$this->sendError('Stakeholder not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function store(Request $request)
    {
        try{
            $this->validate($request, [
                'stakeholder_name' => 'required|string',
                'stakeholder_party_type' => 'required|string',
            ]);
            $stakeholder = new Stakeholder();
            $stakeholder->stakeholder_name = $request->stakeholder_name;
            $stakeholder->stakeholder_party_type = $request->stakeholder_party_type;
            $stakeholder->employee_id = $request->employee_id;
            $stakeholder->tpn_no = $request->tpn_no;
            $stakeholder->bank_account_no = $request->bank_account_no;
            $stakeholder->bank_name = $request->bank_name;
            $stakeholder->encrypted=1;
        if ($stakeholder->save()) {
            return $stakeholder;
        } else {
            return response()->json('error');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function show($id)
    {
       try{
         $stakeholder = Stakeholder::find($id);
        return $stakeholder ? $this->sendResponse($stakeholder,'Stakeholder Details'):$this->sendError('Stakeholder not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }        
    }

    public function edit($id)
    {
        $stakeholder = Stakeholder::find($id);
        return response()->json($stakeholder);
    }

    public function update(Request $request, $id)
    {
        try{
            $this->validate($request, [
                'stakeholder_name' => 'required|string',
                'stakeholder_party_type' => 'required|string',
            ]);
        $stakeholder = Stakeholder::find($id);
        $stakeholder->stakeholder_name = $request->stakeholder_name;
        $stakeholder->stakeholder_party_type = $request->stakeholder_party_type;
        $stakeholder->employee_id = $request->employee_id;
        $stakeholder->tpn_no = $request->tpn_no;
        $stakeholder->bank_account_no = $request->bank_account_no;
        $stakeholder->bank_name = $request->bank_name;
        if ($stakeholder->save()) {
            return $stakeholder;
        } else {
            return response()->json('error');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function destroy($id)
    {
        try{
            $stakeholder = Stakeholder::find($id);
        if ($stakeholder->delete()) {
            return response()->json('Stakeholder Deleted Successfully');
        } else {
            return response()->json('Error Deleting the Stakeholder');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }
}
