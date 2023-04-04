<?php

namespace App\Http\Controllers\AccountType;

use App\Http\Controllers\Controller;
use App\Models\Accountgroup;
use App\Models\Accounttype;
use App\Models\Branch;
use App\Models\Companyregistration;
use App\Models\Pfemployeeregistration;
use App\Models\Stakeholder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webpatser\Uuid\Uuid;
use Exception;
use ESolution\DBEncryption\Encrypter;

class AccounttypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:accounttype-list|accounttype-create|accounttype-edit|accounttype-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:accounttype-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:accounttype-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:accounttype-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
    try{
        $accountType = DB::table('accounttypes')->get();
        return $accountType ? $this->sendResponse($accountType,'Account Type Details'):$this->sendError('Account Type not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }

    }

    public function store(Request $request)
    {
        request()->validate([
            'account_group_id' => 'required',
            'acc_name' => 'required',
            'acc_nature' => 'required',
        ]);
     try{
        $accountType = new Accounttype();
        $accountType->account_type_id = Uuid::generate();
        $accountType->account_group_id = $request->account_group_id;
        $accountType->acc_code = $request->acc_code;
        $accountType->acc_name = $request->acc_name;
        $accountType->acc_nature = $request->acc_nature;
        $accountType->acc_sub_ledger = $request->acc_sub_ledger;
        $accountType->acc_branch_id = $request->acc_branch_id;
        $accountType->acc_description = $request->acc_description;

        if ($accountType->save()) {
            return response()->json(['success','message'=>'Account type added successfully']);
        } else {
            return response()->json(['error','message'=>'Error adding Account type']);
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function show($id)
    {
        try{
            $accountType= Accounttype::find($id);
            return $accountType ? $this->sendResponse($accountType,'Account Type Details'):$this->sendError('Account Type not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }    
    }

    public function edit($id)
    {
        return Accounttype::find($id);
    }

    public function update(Request $request, $id)
    {
        try{
        $accountType = Accounttype::find($id);
        $accountType->account_group_id = $request->account_group_id;
        $accountType->acc_code = $request->acc_code;
        $accountType->acc_name = $request->acc_name;
        $accountType->acc_nature = $request->acc_nature;
        $accountType->acc_sub_ledger = $request->acc_sub_ledger;
        $accountType->acc_branch_id = $request->acc_branch_id;
        $accountType->acc_description = $request->acc_description;

        if ($accountType->save()) {

            return response()->json(['success','message'=>'Account type updated successfully']);
        } else {

            return response()->json(['error','message'=>'Error updating the Account Type']);
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function destroy($id)
    {
        try{
        $accountType = Accounttype::find($id);
        if (empty($accountType)) {
            return "Data Not Found. ";
        }
        if ($accountType->delete()) {
            return response()->json('Deleted ');
        } else {
            return response()->json('Error Deleting the Account Type ');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function getAccountTypeName($group_id)
    {
        try{
            $accountGroup= Accountgroup::join('accounttypes', 'accounttypes.account_group_id', '=', 'accountgroups.account_group_id')
            ->where('accounttypes.account_group_id', '=', $group_id)
            ->get();
            return $accountGroup ? $this->sendResponse($accountGroup,'Account Group Details'):$this->sendError('Account Group not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    
    }

    public function getAccountPostingSearchItems(Request $request)
    {
            try{
                $acc_grouptype_data =  Accountgroup::join('accounttypes', 'accounttypes.account_group_id', '=', 'accountgroups.account_group_id')
                ->where('accountgroups.account_group_id', '=', $request->account_group)
                ->where('accounttypes.account_type_id', '=', $request->account_group_type)
                ->get(['accountgroups.account_group_id','account_group_code','account_group_name','account_type_id','acc_code','acc_name','acc_description'])->first();

            $getBranch = Branch::where('id','=',$request->reg_branch)
                ->get(['id as branch_id','branch_name','branch_code'])->first();

            $ledger_search_data = DB::select("select
                    accountgroups.account_group_id,
                    account_group_code,
                    account_group_name,
                    account_type_id,
                    acc_code,
                    acc_name,
                    acc_description,
                    (select id from branches where id = '$request->reg_branch') as branch_id,
                    (select branch_name from branches where id = '$request->reg_branch') as branch_name

                    from accountgroups
                    inner join  accounttypes on accounttypes.account_group_id = accountgroups.account_group_id
                    where accountgroups.account_group_id = '$request->account_group'
                    and accounttypes.account_type_id =  '$request->account_group_type'");

            return response()->json(['ledger_search_data'=>$ledger_search_data]);
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function getAccountPostingReferenceNumber(Request $request)
    {
        try{
            $partyId = $request->org_name;
            $empIdentificationNo = $request->identification_no;
            $email = $request->email_id;
            $mobileNo = $request->mobile_no;

            if ($request->relation_type == 'PF Company') {

                $company_data= Companyregistration::where('registration_type', '=', 'PF')
                    ->where('effective_end_date', '=', NULL)
                    ->orWhere('companyregistrations.company_id', '=', $partyId)
                    ->orWhere('companyregistrations.company_account_no', '=', $empIdentificationNo)
                    ->orWhere('companyregistrations.email_id', '=', $email)
                    ->orWhere('companyregistrations.phone_no', '=', $mobileNo)
                    ->select([\DB::raw("'PF Company' as RelationType"), 'company_id as PartyId', 'org_name as PartyName', 'company_account_no as IdentificationNo', 'company_account_no as RefNumber',
                    'email_id as EmailId', 'phone_no as ContactNo'])
                    ->get();

                //return $company_data;

                $company_data->transform(function($data) {
                        $data->PartyName = Encrypter::decrypt($data->PartyName);
                        $data->IdentificationNo = Encrypter::decrypt($data->IdentificationNo);
                        $data->RefNumber = Encrypter::decrypt($data->RefNumber);
                        $data->company_id = Encrypter::decrypt($data->company_id);
                         $data->EmailId = Encrypter::decrypt($data->EmailId);
                        $data->ContactNo = Encrypter::decrypt($data->ContactNo);
                        return  $data;
                    });
                return $company_data;

            } else if ($request->relation_type == 'GF Company') {

                $company_data = Companyregistration::where('registration_type', '=', 'GF')
                    ->where('effective_end_date', '=', NULL)
                    ->orWhere('companyregistrations.company_id', '=', $partyId)
                    ->orWhere('companyregistrations.company_account_no', '=', $empIdentificationNo)
                    ->orWhere('companyregistrations.email_id', '=', $email)
                    ->orWhere('companyregistrations.phone_no', '=', $mobileNo)
                    ->select([\DB::raw("'GF Company' as RelationType"), 'company_id as PartyId', 'org_name as PartyName', 'bit_cit_no as IdentificationNo', 'company_account_no as RefNumber',
                    'email_id as EmailId', 'phone_no as ContactNo'])
                    ->get();
                $company_data->transform(function($data) {
                    $data->PartyName = Encrypter::decrypt($data->PartyName);
                    $data->IdentificationNo = Encrypter::decrypt($data->IdentificationNo);
                    $data->RefNumber = Encrypter::decrypt($data->RefNumber);
                    $data->company_id = Encrypter::decrypt($data->company_id);
                     $data->EmailId = Encrypter::decrypt($data->EmailId);
                    $data->ContactNo = Encrypter::decrypt($data->ContactNo);
                    return  $data;
                });
                return $company_data;

            } else if ($request->relation_type == 'PF Employee') {

                return Pfemployeeregistration::where('registration_type', '=', 'PF')
                    ->where('effective_end_date', '=', NULL)
                    ->orWhere('pfemployeeregistrations.pf_employee_id', '=', $partyId)
                    ->orWhere('pfemployeeregistrations.identification_no', '=', $empIdentificationNo)
                    ->orWhere('pfemployeeregistrations.email_id', '=', $email)
                    ->orWhere('pfemployeeregistrations.contact_no', '=', $mobileNo)
                    ->select([\DB::raw("'PF Employee' as RelationType"), 'pf_employee_id as PartyId', 'employee_name as PartyName', 'identification_no as IdentificationNo', 'employee_id_no as RefNumber',
                    'email_id as EmailId', 'contact_no as ContactNo'])
                    ->get();

            } else if ($request->relation_type == 'GF Employee') {

                return Pfemployeeregistration::where('registration_type', '=', 'GF')
                    ->where('effective_end_date', '=', NULL)
                    ->orWhere('pfemployeeregistrations.pf_employee_id', '=', $partyId)
                    ->orWhere('pfemployeeregistrations.identification_no', '=', $empIdentificationNo)
                    ->orWhere('pfemployeeregistrations.email_id', '=', $email)
                    ->orWhere('pfemployeeregistrations.contact_no', '=', $mobileNo)
                    ->select([\DB::raw("'GF Employee' as RelationType"), 'pf_employee_id as PartyId', 'employee_name as PartyName', 'identification_no as IdentificationNo', 'employee_id_no as RefNumber',
                    'email_id as EmailId', 'contact_no as ContactNo'])
                    ->get();

            } else {

                return Stakeholder::where('stakeholder_party_type', '=', $request->relation_type)
                    ->orWhere('bank_account_no', '=', $empIdentificationNo)
                    ->orWhere('tpn_no', '=', $empIdentificationNo)
                    ->select(['stakeholder_party_type as RelationType', 'id as PartyId', 'stakeholder_name as PartyName',
                        'bank_account_no as IdentificationNo', 'employee_id as RefNumber', \DB::raw('NULL as EmailId'), \DB::raw('NULL as ContactNo')])
                    ->get();
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }
}
