<?php

namespace App\Http\Controllers\ProprietorDetails;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use App\Models\Proprietordetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ProprietordetailsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:proprietor-list|proprietor-create|proprietor-edit|proprietor-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:proprietor-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:proprietor-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:proprietor-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        try{
            return Proprietordetail::where('registration_type', '=', 'PF')
            ->where('effective_end_date','=',NULL)
            ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function create()
    {
        try{
            return Companyregistration::pluck('company_id')
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date','=',NULL)
            ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function store(Request $request)
    {
        try{
            request()->validate([
            'proprietor_name' => 'required',
            'contact_number' => 'required',
            'status' => 'required',
        ]);

        $savePropDetails = new Proprietordetail();
        $savePropDetails->p_id = date('Ymd') . random_int(666666, 999999);

        $savePropDetails->prop_company_id = $request->prop_company_id;
        $savePropDetails->proprietor_name = $request->proprietor_name;
        $savePropDetails->contact_number = $request->contact_number;
        $savePropDetails->email_id = $request->email_id;
        $savePropDetails->address = $request->address;
        $savePropDetails->status = $request->status;
        $savePropDetails->employee_detail = $request->employee_detail;
        $savePropDetails->designation = $request->designation;
        $savePropDetails->effective_start_date = Carbon::now()->format('Y-m-d');
        $savePropDetails->effective_end_date = null;
        $savePropDetails->registration_type = 'PF';
        $savePropDetails->encrypted=1;

        if ($savePropDetails->save()) {

            return response()->json('Proprietor Saved');
        } else {

            return response()->json('Error Creating Proprietor');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function show($id)
    {
        try{
            $proprietors = Proprietordetail::find($id)
            ->where('id','=',$id)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date','=',NULL)
            ->get();
        return response()->json($proprietors);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function edit($id)
    {
        try{
            $editPfProprietor = Proprietordetail::find($id)
            ->where('id','=',$id)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date','=',NULL)
            ->get();
        return response()->json($editPfProprietor);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function update(Request $request, $id)
    {
        try{
            request()->validate([
            'proprietor_name' => 'required',
            'contact_number' => 'required',
            'status' => 'required',
        ]);

        DB::beginTransaction();

        $prev_pfProprietor_data = Proprietordetail::find($id);
        $prev_pfProprietor_data->effective_end_date = Carbon::now()->format('Y-m-d');

        if ($prev_pfProprietor_data->save()) {

            $pfProprietor = new Proprietordetail();
            $pfProprietor->p_id = date('Ymd') . random_int(666666, 999999);
            $pfProprietor->prop_company_id = $request->prop_company_id;
            $pfProprietor->proprietor_name = $request->proprietor_name;
            $pfProprietor->contact_number = $request->contact_number;
            $pfProprietor->email_id = $request->email_id;
            $pfProprietor->address = $request->address;
            $pfProprietor->employee_detail = $request->employee_detail;
            $pfProprietor->designation = $request->designation;
            $pfProprietor->status = $request->status;
            $pfProprietor->registration_type = 'PF';
            $pfProprietor->effective_start_date = Carbon::now()->format('Y-m-d');
            $pfProprietor->effective_end_date = null;

            if ($pfProprietor->save()) {

                DB::commit();
                return response()->json('PF Proprietor Details Updated Successfully');

            }else{

                DB::rollback();
                return response()->json('Error Updating the Proprietor Details');
            }


        } else {

            DB::rollback();
            return response()->json('Error Updating the Proprietor Details');

        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function destroy($id)
    {
        try{
            $deletePfProprietor = Proprietordetail::find($id)
            ->where('registration_type','=','PF')
            ->where('effective_end_date','=',NULL)
            ->get();

        if (empty($deletePfProprietor)) {
            return "Data Not Found. ";
        }
        if ($deletePfProprietor->update(['effective_end_date' => Carbon::now()->format('Y-m-d')])) {

            return response()->json('PF Proprietor Deleted Successfully');

        } else {

            return response()->json('Error Deleting the Proprietor');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

//    Get PF Proprietor By ID
    public function getproprietorbycompanyid($company_id)
    {
        try{
            return Proprietordetail::Join("companyregistrations", "companyregistrations.company_id", "=", "proprietordetails.prop_company_id")
            ->where("proprietordetails.prop_company_id", "=", $company_id)
            ->where("proprietordetails.registration_type", "=", 'PF')
            ->where("proprietordetails.effective_end_date", "=", NULL)
            ->get([
                'proprietordetails.id', 'proprietordetails.p_id', 'proprietordetails.prop_company_id',
                'proprietordetails.proprietor_name', 'proprietordetails.contact_number', 'proprietordetails.email_id',
                'proprietordetails.address', 'proprietordetails.employee_detail', 'proprietordetails.designation',
                'proprietordetails.status', 'proprietordetails.effective_start_date', 'proprietordetails.effective_end_date'
            ]);
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }
    
}

