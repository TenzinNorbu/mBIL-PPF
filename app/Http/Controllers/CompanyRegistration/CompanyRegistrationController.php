<?php

namespace App\Http\Controllers\CompanyRegistration;

use App\Http\Controllers\Controller;
use App\Models\CompanyRegistration;
use App\Models\Pfcompanylog;
use App\Models\Pfmoudetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ESolution\DBEncryption\Encrypter;
use Exception;

class CompanyRegistrationController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:company-list|company-create|company-edit|company-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:company-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:company-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:company-delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        try{
            $companyRegistration= CompanyRegistration::join("dzongkhags", "dzongkhags.dzongkhag_id", "=", "companyregistrations.cmp_dzongkhag_id")
            ->join("branches", "branches.id", "=", "companyregistrations.reg_branch_id")
            ->where("companyregistrations.effective_end_date", '=', NULL)
            ->where("companyregistrations.registration_type", '=', 'PF')
            ->orderBy('companyregistrations.created_at', 'DESC')
            ->select(
                "companyregistrations.id", "company_id", "company_account_no",
                "org_name", "license_no", "license_validity", "address",
                "companyregistrations.cmp_dzongkhag_id", "dzongkhag_name",
                "phone_no", "email_id", "companyregistrations.reg_branch_id",
                "branches.branch_name"
            )->get();
            return $companyRegistration ? $this->sendResponse($companyRegistration,'CompanyRegistration Details'):$this->sendError('Departments not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function store(Request $request)
    {
        try{
            $year = Carbon::now()->format('Y');

            DB::beginTransaction();
            $companyRegistration = new CompanyRegistration();
            $compId = date('Ymd') . random_int(666666, 999999);
            $companyRegistration->company_id = $compId;

            $companyRegistration->org_name = $request->org_name;
            $companyRegistration->license_no = $request->license_no;
            $companyRegistration->license_validity = $request->license_validity;
            $companyRegistration->bit_cit_no = $request->bit_cit_no;
            $companyRegistration->org_type = $request->org_type;
            $companyRegistration->address = $request->address;
            $companyRegistration->cmp_dzongkhag_id = $request->dzongkhag_id;

            $companyRegistration->phone_no = $request->phone_no;
            $companyRegistration->email_id = $request->email_id;
            $companyRegistration->website = $request->website;
            $companyRegistration->post_box_no = $request->post_box_no;

            $companyRegistration->reg_branch_id = $request->reg_branch;
            $companyRegistration->encrypted=1;


            $getPfCompanySerialNo = DB::table('masteridholders')
                ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
                ->where('id_type', '=', 'Company_Registration')
                ->where('registration_type', '=', 'PF')
                ->where('branch_id', $request->reg_branch)
                ->where('f_year', $year)
                ->get(['serial_no', 'branch_code'])
                ->first();
                

            $cmpSerial_no = (int)$getPfCompanySerialNo->serial_no + 1;
            $branch_code = $getPfCompanySerialNo->branch_code;
            $setCompanyPfAccountNo = 'BIL/PF/' . $branch_code . '/' . $year . '/' . $cmpSerial_no;

            $companyRegistration->company_account_no = $setCompanyPfAccountNo;

            $companyRegistration->effective_start_date = Carbon::now()->format('Y-m-d');
            $companyRegistration->effective_end_date = NULL;
            $companyRegistration->registration_type = 'PF';
            $companyRegistration->registered_as = 'PF_Individual';
            $companyRegistration->sector_type = $request->sector_type;

            if ($companyRegistration->save()) {

                if ($this->saveMouDetails($request, $compId) == 'success') {

                    if(DB::table('masteridholders')
                        ->where('branch_id', '=', $request->reg_branch)
                        ->where('f_year', '=', $year)
                        ->where('id_type', '=', 'Company_Registration')
                        ->where('registration_type', '=', 'PF')
                        ->update(['serial_no' => $cmpSerial_no])){

                        DB::commit();
                        $newdata = CompanyRegistration::where('company_id', $compId)->first();

                        return response()->json(['data' => $newdata]);
                        }else{

                            DB::rollback();
                            return response()->json("Error");
                        }
                } else {

                    DB::rollback();
                    return response()->json("Error");
                }

            } else {

                return response()->json("Error");
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function saveMouDetails($request, $compId)
    {
        try{
            $pfmoudetail = new Pfmoudetail();
            $pfmoudetail->pfmou_company_id = $compId;
            $pfmoudetail->mou_ref_no = $request->mou_ref_no;
            $pfmoudetail->mou_date = $request->mou_date;
            $pfmoudetail->mou_expiry_date = $request->mou_expiry_date;
            $pfmoudetail->interest_rate = (float)$request->interest_rate;
            $pfmoudetail->effective_start_date = Carbon::now()->format('Y-m-d');
            $pfmoudetail->effective_end_date = NULL;
            $pfmoudetail->registration_type = 'PF';

            if ($pfmoudetail->save()) {

                return 'success';
            } else {

                return 'error';
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function show($id)
    {
        try{
            $getProprietors = CompanyRegistration::join("dzongkhags", "dzongkhags.dzongkhag_id", "=", "companyregistrations.dzongkhag_id")
            ->join("branches", "branches.id", "=", "companyregistrations.reg_branch_id")
            ->where("companyregistrations.effective_end_date", '=', NULL)
            ->where("companyregistrations.registration_type", '=', 'PF')
            ->with('proprietorDetails')
            ->with('introducerList')
            ->with('contactPersonList')
            ->with('getPfMouDetails')
            ->get()
            ->find($id);

        return response()->json(['Data' => $getProprietors]);
        }catch(Exception $e){
                return $this->errorResponse('Page not found');
        }
    }

    public function edit($id)
    {
        try{
            $getCompany = CompanyRegistration::join("dzongkhags", "dzongkhags.dzongkhag_id", "=", "companyregistrations.cmp_dzongkhag_id")
            ->join("branches", "branches.id", "=", "companyregistrations.reg_branch_id")
            ->where("companyregistrations.effective_end_date", '=', NULL)
            ->where("companyregistrations.registration_type", '=', 'PF')
            ->with('proprietorDetails')
            ->with('introducerList')
            ->with('contactPersonList')
            ->with('getPfMouDetails')
            ->where('companyregistrations.company_id', $id)
            ->get();

        return response()->json(['Data' => $getCompany]);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function update(Request $request, $company_id)
    {
        try{
            //pfcompany log
        DB::beginTransaction();
        $current_date = date('Y-m-d');
        $pfCompanyLog = new Pfcompanylog();
        $pfCompanyLog->company_id = $company_id;
        $pfCompanyLog->effective_start_date = $current_date;
        $pfCompanyLog->effective_end_date = Carbon::now()->format('Y-m-d');
        $pfCompanyLog->updated_by = auth('api')->user()->name;

        $pfCompanyLog->save();
       
       //update the company registration
        $update_pf_company_data = CompanyRegistration::where('company_id', $company_id)->first();
        $update_pf_company_data->org_name=$request->org_name;
        $update_pf_company_data->license_no = $request->license_no; 
        $update_pf_company_data-> license_validity = $request->license_validity;
        $update_pf_company_data ->bit_cit_no = $request->bit_cit_no;
        $update_pf_company_data -> org_type = $request->org_type;
        $update_pf_company_data->address = $request->address;
        $update_pf_company_data ->cmp_dzongkhag_id = $request->dzongkhag_id;
        $update_pf_company_data ->phone_no = $request->phone_no;
        $update_pf_company_data-> email_id = $request->website;
        $update_pf_company_data-> post_box_no = $request->post_box_no;
        $update_pf_company_data-> reg_branch_id = $request->reg_branch;
        $update_pf_company_data->  effective_end_date = null;
        $update_pf_company_data-> closing_date = $request->closing_date;

        $update_pf_company_data =$update_pf_company_data->save();

        //update pfmoudetail
        $close_prev_mou_data = Pfmoudetail::where('pfmou_company_id', $company_id)
                ->where('effective_end_date', '=', null)->first();
            $close_prev_mou_data->effective_end_date=$current_date;
            $save_prev_mou_data=$close_prev_mou_data->save();

        //creating new pfmou
        if($update_pf_company_data && $save_prev_mou_data){

            $pfmoudetail = new Pfmoudetail();
            $pfmoudetail->pfmou_company_id = $company_id;
            $pfmoudetail->mou_ref_no = $request->mou_ref_no;
            $pfmoudetail->mou_date = $request->mou_date;
            $pfmoudetail->mou_expiry_date = $request->mou_expiry_date;
            $pfmoudetail->interest_rate = (float)$request->interest_rate;
            $pfmoudetail->effective_start_date = $current_date;
            $pfmoudetail->effective_end_date = NULL;
            $pfmoudetail->registration_type = 'PF';

            if ($pfmoudetail->save()) {
                DB::commit();
                return response()->json("Success");
            } else {
                DB::rollback();
                return response()->json("Error");
            }
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    function destroy($id)
    {
        try{
            $current_date = date('Y-m-d');

        $pfCompanyRegistration = CompanyRegistration::find($id);
        if (empty($pfCompanyRegistration)) {

            return "Data not found to be deleted.";

        } else if ($pfCompanyRegistration->update(['effective_end_date'=> $current_date])) {

            $current_date = date('Y-m-d');
            $pfCompanyLog = new Pfcompanylog();
            $pfCompanyLog->company_id = $id;
            $pfCompanyLog->effective_start_date = $current_date;
            $pfCompanyLog->effective_end_date = $current_date;
            $pfCompanyLog->updated_by = auth('api')->user()->name;

            if ($pfCompanyLog->save()) {
              return response()->json('PF Company Deleted Successfully');
            }
            return response()->json('Error');

        } else {
            return response()->json('Error Deleting the PF Company');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    //mBil app api controller
    public function getCompanyByLicenseNo($licenseNo) {

    }
}
