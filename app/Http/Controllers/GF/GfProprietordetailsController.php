<?php

namespace App\Http\Controllers\GF;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use App\Models\Proprietordetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GfProprietordetailsController extends Controller
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
        return Proprietordetail::where('registration_type', '=', 'GF')
            ->where('effective_end_date','=',NULL)
            ->get();
    }

    public function create()
    {
        return Companyregistration::pluck('company_id')
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date','=',NULL)
            ->get();
    }

    public function store(Request $request)
    {
        request()->validate([
            'proprietor_name' => 'required',
            'contact_number' => 'required',
            'status' => 'required',
        ]);

        $gfPropDetail = new Proprietordetail();
        $gfPropId = Carbon::now()->format('YmdH') . random_int(666666, 999999);
        $gfPropDetail->p_id = $gfPropId;

        $gfPropDetail->prop_company_id = $request->prop_company_id;
        $gfPropDetail->proprietor_name = $request->proprietor_name;
        $gfPropDetail->contact_number = $request->contact_number;
        $gfPropDetail->email_id = $request->email_id;
        $gfPropDetail->address = $request->address;
        $gfPropDetail->status = $request->status;
        $gfPropDetail->employee_detail = $request->employee_detail;
        $gfPropDetail->designation = $request->designation;
        $gfPropDetail->effective_start_date = Carbon::now()->format('Y-m-d');
        $gfPropDetail->effective_end_date = NULL;
        $gfPropDetail->registration_type = 'GF';

        if ($gfPropDetail->save()) {
            return response()->json('GF Proprietor Saved');
        } else {
            return response()->json('Error Creating Proprietor');
        }
    }

    public function show($id)
    {
        $gfProprietors = Proprietordetail::find($id)
            ->where('id','=',$id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date','=',NULL)
            ->get();
        return response()->json($gfProprietors);
    }

    public function edit($id)
    {
        $editGfProprietors = Proprietordetail::find($id)
            ->where('id','=',$id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date','=',NULL)
            ->get();
        return response()->json($editGfProprietors);
    }

    public function update(Request $request, $id)
    {
        request()->validate([
            'proprietor_name' => 'required',
            'contact_number' => 'required',
            'designation' => 'required',
            'status' => 'required',
        ]);

        DB::beginTransaction();
        $prev_gfProprietor_data = Proprietordetail::find($id);
        $prev_gfProprietor_data->effective_end_date = Carbon::now()->format('Y-m-d');

        if ($prev_gfProprietor_data->save()) {

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
            $pfProprietor->registration_type = 'GF';
            $pfProprietor->effective_start_date = Carbon::now()->format('Y-m-d');
            $pfProprietor->effective_end_date = null;

            if ($pfProprietor->save()) {

                DB::commit();
                return response()->json('GF Proprietor Details Updated Successfully');
            }else{

                DB::rollback();
                return response()->json('Error Updating the Proprietor Details');
            }
        } else {

            DB::rollback();
            return response()->json('Error Updating the Proprietor Details');
        }
    }

    public function destroy($id)
    {
        $deleteGfProprietor = Proprietordetail::find($id)
            ->where('registration_type', '=', 'GF')
            ->get();

        if (empty($deleteGfProprietor)) {
            return "Data Not Found. ";
        }
        if ($deleteGfProprietor->update(['effective_end_date' => Carbon::now()->format('Y-m-d')])) {
            return response()->json('GF Proprietor Deleted Successfully');
        } else {
            return response()->json('Error Deleting the Proprietor');
        }
    }

//    Get GF Proprietor By ID
    public function getproprietorbycompanyid($company_id)
    {
        return Proprietordetail::join("companyregistrations", "companyregistrations.company_id", "=", "proprietordetails.prop_company_id")
            ->where("proprietordetails.prop_company_id", "=", $company_id)
            ->where("proprietordetails.registration_type", "=", 'GF')
            ->where("proprietordetails.effective_end_date", "=", NULL)
            ->get([
                'proprietordetails.id', 'proprietordetails.p_id', 'proprietordetails.prop_company_id',
                'proprietordetails.proprietor_name', 'proprietordetails.contact_number', 'proprietordetails.email_id',
                'proprietordetails.address', 'proprietordetails.employee_detail', 'proprietordetails.designation',
                'proprietordetails.status', 'proprietordetails.effective_start_date', 'proprietordetails.effective_end_date'
            ]);
    }
}
