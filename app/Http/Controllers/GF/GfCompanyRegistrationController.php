<?php

namespace App\Http\Controllers\GF;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use App\Models\Pfcompanylog;
use App\Models\Pfmoudetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GfCompanyRegistrationController extends Controller
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
            $CompanyRegistration=CompanyRegistration::with('getPfMouDetails')
            ->join("dzongkhags", "dzongkhags.dzongkhag_id", "=", "companyregistrations.cmp_dzongkhag_id")
            ->join("branches", "branches.id", "=", "companyregistrations.reg_branch_id")
            ->where("companyregistrations.effective_end_date", '=', NULL)
            ->where("companyregistrations.registration_type", '=', 'GF')
            ->orderBy('companyregistrations.created_at', 'DESC')
            ->select(
                "companyregistrations.id", "company_id", "company_account_no",
                "org_name", "license_no", "license_validity", "address",
                "companyregistrations.cmp_dzongkhag_id", "dzongkhag_name",
                "phone_no", "email_id", "companyregistrations.reg_branch_id",
                "branches.branch_name"
            )->get();
            return $CompanyRegistration ? $this->sendResponse($CompanyRegistration,'CompanyRegistration Details'):$this->sendError('CompanyRegistration not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function store(Request $request)
    {
        request()->validate([
            'org_name' => 'required',
            'license_no' => 'required',
            'license_validity' => 'required',
            'org_type' => 'required',
            'reg_branch' => 'required',

            /** PF MOU Details */
            'mou_date' => 'required|date',
            'mou_expiry_date' => 'required',
        ]);

        DB::beginTransaction();
        $gfCompanyRegistration = new CompanyRegistration();
        $gfCompanyId = Carbon::now()->format('YmdH') . random_int(333333, 999999);
        $gfCompanyRegistration->company_id = $gfCompanyId;
        $gfCompanyRegistration->org_name = $request->org_name;
        $gfCompanyRegistration->license_no = $request->license_no;
        $gfCompanyRegistration->license_validity = $request->license_validity;
        $gfCompanyRegistration->bit_cit_no = $request->bit_cit_no;
        $gfCompanyRegistration->org_type = $request->org_type;
        $gfCompanyRegistration->address = $request->address;
        $gfCompanyRegistration->cmp_dzongkhag_id = $request->dzongkhag_id;

        $gfCompanyRegistration->phone_no = $request->phone_no;
        $gfCompanyRegistration->email_id = $request->email_id;
        $gfCompanyRegistration->website = $request->website;
        $gfCompanyRegistration->post_box_no = $request->post_box_no;
        $gfCompanyRegistration->reg_branch_id = $request->reg_branch;

        $getPfCompanySerialNo = DB::table('masteridholders')
            ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
            ->where('id_type', '=', 'GF_Company_Registration')
            ->where('registration_type', '=', 'GF')
            ->where('branch_id', $request->reg_branch)
            ->where('f_year', Carbon::now()->year)
            ->get(['serial_no', 'branch_code'])
            ->first();

        $cmpSerial_no = (int)$getPfCompanySerialNo->serial_no + 1;
        $branch_code = $getPfCompanySerialNo->branch_code;

        $setCompanyGfAccountNo = 'BIL/' . 'GF/' . $branch_code . '/' . Carbon::now()->year . '/' . $cmpSerial_no;
        $gfCompanyRegistration->company_account_no = $setCompanyGfAccountNo;

        $gfCompanyRegistration->effective_start_date = Carbon::now()->format('Y-m-d');
        $gfCompanyRegistration->effective_end_date = NULL;
        $gfCompanyRegistration->registration_type = 'GF';
        $lumpsumRegisterAs = $request->registered_as;
        $gfCompanyRegistration->sector_type = $request->sector_type;

        if ($gfCompanyRegistration->save()) {

            if ($lumpsumRegisterAs == 'Lumpsum') {

                Companyregistration::where('company_id', $gfCompanyId)
                    ->update([
                        'registered_as' => 'Lumpsum',
                    ]);

                $array = array([
                    'pf_employee_id' => Carbon::now()->format('YmdH') . random_int(444444, 888888),
                    'company_pf_acc_no' => $setCompanyGfAccountNo,
                    'pf_employee_company_id' => (int)$gfCompanyId,
                    'employee_name' => $request->org_name . ' [Employee]',
                    'date_of_birth' => '',
                    'gender' => '',
                    'marital_status' => '',
                    'nationality' => '',
                    'identification_types' => 'License_No',
                    'identification_no' => $request->license_no,
                    'designation' => '',
                    'department' => '',
                    'employee_id_no' => '',
                    'service_joining_date' => Carbon::now()->format('Y-m-d'),
                    'contact_no' => $request->phone_no,
                    'email_id' => $request->email_id,
                    'address' => $request->address,
                    'basic_pay' => '',
                    'contribution' => '',
                    'employee_contribution_amount' => 0,
                    'employer_contribution_amount' => $request->total_gf_amount,
                    'total_contribution' => $request->total_gf_amount,
                    'status' => 'Active',
                    'registration_date' => Carbon::now()->format('Y-m-d'),
                    'closing_date' => NULL,
                    'effective_start_date' => Carbon::now()->format('Y-m-d'),
                    'effective_end_date' => NULL,
                    'pf_emp_acc_no' => $request->pf_emp_acc_no,
                    'registration_type' => 'GF',
                ]);

                foreach ($array as $key => $rows) {
                    DB::table('pfemployeeregistrations')->insert($rows);
                }

            } else {

                Companyregistration::where('company_id', $gfCompanyId)
                    ->update([
                        'registered_as' => 'Individual',
                    ]);
            }

            if ($this->saveMouDetails($request, $gfCompanyId) == 'success') {

                DB::commit();
                DB::table('masteridholders')
                    ->where('branch_id', '=', $request->reg_branch)
                    ->where('f_year', '=', Carbon::now()->year)
                    ->where('id_type', '=', 'GF_Company_Registration')
                    ->where('registration_type', '=', 'GF')
                    ->update(['serial_no' => $cmpSerial_no]);

                $gfCompanyData = CompanyRegistration::where('company_id', $gfCompanyId)
                    ->first();

                return response()->json(['data' => $gfCompanyData]);

            } else {

                DB::rollback();
                return response()->json("Error");
            }

        } else {

            return response()->json("Error");
        }
    }

    public function saveMouDetails($request, $gfCompanyId)
    {
        $gfMoudetail = new Pfmoudetail();
        $gfMoudetail->pfmou_company_id = $gfCompanyId;
        $gfMoudetail->mou_ref_no = $request->mou_ref_no;
        $gfMoudetail->mou_date = $request->mou_date;
        $gfMoudetail->mou_expiry_date = $request->mou_expiry_date;
        $gfMoudetail->interest_rate = (float)$request->interest_rate;
        $gfMoudetail->effective_start_date = Carbon::now()->format('Y-m-d');
        $gfMoudetail->effective_end_date = NULL;
        $gfMoudetail->registration_type = 'GF';

        if ($gfMoudetail->save()) {

            return 'success';
        } else {

            return 'error';
        }
    }

    public function show($id)
    {
        return CompanyRegistration::join("dzongkhags", "dzongkhags.dzongkhag_id", "=", "companyregistrations.dzongkhag_id")
            ->join("branches", "branches.id", "=", "companyregistrations.reg_branch_id")
            ->where("companyregistrations.effective_end_date", '=', NULL)
            ->where("companyregistrations.registration_type", '=', 'GF')
            ->with('gfProprietorDetails')
            ->with('gfIntroducerList')
            ->with('gfContactPersonList')
            ->with('getGfMouDetails')
            ->get()
            ->find($id);
    }

    public function edit($id)
    {
        $getCompany = CompanyRegistration::join("dzongkhags", "dzongkhags.dzongkhag_id", "=", "companyregistrations.cmp_dzongkhag_id")
            ->join("branches", "branches.id", "=", "companyregistrations.reg_branch_id")
            ->where("companyregistrations.effective_end_date", '=', NULL)
            ->where("companyregistrations.registration_type", '=', 'GF')
            ->with('gfProprietorDetails')
            ->with('gfIntroducerList')
            ->with('gfContactPersonList')
            ->with('getGfMouDetails')
            ->where('companyregistrations.company_id', $id)
            ->get();

        return response()->json(['Data' => $getCompany]);
    }

    public function update(Request $request, $company_id)
    {
        request()->validate([
            'license_validity' => 'required',
            'org_type' => 'required',
            'address' => 'required',
            'dzongkhag_id' => 'required',
            'reg_branch' => 'required',
            /**
             * PF MOU Details
             */
            'mou_date' => 'required',
            'mou_expiry_date' => 'required',
            'interest_rate' => 'required',
        ]);

        DB::beginTransaction();
        $current_date = date('Y-m-d');
        $pfCompanyLog = new Pfcompanylog();
        $pfCompanyLog->company_id = $company_id;
        $pfCompanyLog->effective_start_date = $current_date;
        $pfCompanyLog->effective_end_date = Carbon::now()->format('Y-m-d');
        $pfCompanyLog->updated_by = auth('api')->user()->name;

        $pfCompanyLog->save();

        $update_pf_company_data = CompanyRegistration::where('company_id', $company_id)
            ->update([
                'org_name' => $request->org_name,
                'license_no' => $request->license_no,
                'license_validity' => $request->license_validity,
                'bit_cit_no' => $request->bit_cit_no,
                'org_type' => $request->org_type,
                'address' => $request->address,
                'cmp_dzongkhag_id' => $request->dzongkhag_id,
                'phone_no' => $request->phone_no,
                'email_id' => $request->email_id,
                'website' => $request->website,
                'post_box_no' => $request->post_box_no,
                'reg_branch_id' => $request->reg_branch,
                'effective_end_date' => null,
                'closing_date' => $request->closing_date,
            ]);

        $close_prev_mou_data = Pfmoudetail::where('pfmou_company_id', $company_id)
            ->where('effective_end_date', '=', null)
            ->update(['effective_end_date' => $current_date]);

        if($update_pf_company_data && $close_prev_mou_data){

            $pfmoudetail = new Pfmoudetail();
            $pfmoudetail->pfmou_company_id = $company_id;
            $pfmoudetail->mou_ref_no = $request->mou_ref_no;
            $pfmoudetail->mou_date = $request->mou_date;
            $pfmoudetail->mou_expiry_date = $request->mou_expiry_date;
            $pfmoudetail->interest_rate = (float)$request->interest_rate;
            $pfmoudetail->effective_start_date = $current_date;
            $pfmoudetail->effective_end_date = NULL;
            $pfmoudetail->registration_type = 'GF';

            if ($pfmoudetail->save()) {

                DB::commit();
                return response()->json("Success");

            } else {

                DB::rollback();
                return response()->json("Error");
            }
        }
    }

    function destroy($id)
    {
        $gfCompanyRegistration = CompanyRegistration::find($id);
        $current_date = date('Y-m-d');

        if (empty($gfCompanyRegistration)) {
            return "Data Not Found To Be Deleted.";
        } else if ($gfCompanyRegistration->update(['effective_end_date'=> $current_date])) {
            return response()->json('PF Company Deleted Successfully');
        } else {
            return response()->json('Error Deleting the PF Company');
        }
    }
}
