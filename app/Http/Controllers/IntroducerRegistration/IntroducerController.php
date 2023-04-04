<?php

namespace App\Http\Controllers\IntroducerRegistration;

use App\Http\Controllers\Controller;
use App\Models\Introducer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class IntroducerController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:introducer-list|introducer-create|introducer-edit|introducer-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:introducer-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:introducer-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:introducer-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        try{
            return Introducer::where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function store(Request $request)
    {
        try{
            $introducer = new Introducer();
        $introducer->introducer_id = auth('api')->user()->id;
        $introducer->introducer_company_id = $request->introducer_company_id;
        $introducer->introducer_business_code = $request->introducer_business_code;
        $introducer->introducer_branch = $request->introducer_branch;
        $introducer->introducer_department = $request->introducer_department;
        $introducer->percentage_share = $request->percentage_share;
        $introducer->effective_start_date = Carbon::now()->format('Y-m-d');
        $introducer->effective_end_date = NULL;
        $introducer->registration_type = 'PF';

        if ($introducer->save()) {

            return response()->json(['success', 'message' => 'Introducer Saved In the Database']);
        } else {

            return response()->json(['error', 'message' => 'Error Saving the Introducer']);
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function show($id)
    {
        try{
            return Introducer::find($id)
            ->where('id', '=', $id)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function edit($id)
    {
        return Introducer::find($id)
            ->where('id', '=', $id)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    public function update(Request $request, $id)
    {
        try{request()->validate([
            'introducer_business_code' => 'required',
            'percentage_share' => 'required',
            'introducer_branch' => 'required',
            'introducer_department' => 'required',
        ]);


        DB::beginTransaction();
        $prev_introducer_data = Introducer::find($id);
        $introducer_company_id = $prev_introducer_data->introducer_company_id;
        $prev_introducer_data->effective_end_date = Carbon::now()->format('Y-m-d');

        if ($prev_introducer_data->save()) {

            $introducer = new Introducer();
            $introducerId = date('Ymd') . random_int(666666, 999999);
            $introducer->introducer_id = $introducerId;

            $introducer->introducer_company_id = $introducer_company_id;
            $introducer->introducer_business_code = $request->introducer_business_code;
            $introducer->introducer_branch = $request->introducer_branch;
            $introducer->introducer_department = $request->introducer_department;
            $introducer->percentage_share = $request->percentage_share;
            $introducer->effective_start_date = Carbon::now()->format('Y-m-d');
            $introducer->effective_end_date = null;
            $introducer->registration_type = 'PF';

            if ($introducer->save()) {

                DB::commit();
                return response()->json('Introducer Saved');

            } else {

                DB::rollback();
                return response()->json('Error Saving Introducer');
            }
        } else {

            DB::rollback();
            return response()->json('Error Saving Introducer');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function destroy($id)
    {
        try{
            $current_date = date('Y-m-d');

        $introducer = Introducer::find($id)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get();

        if (empty($introducer)) {
            return "Introducer Not Found. ";
        }

        if ($introducer->update(['effective_end_date' => $current_date])) {

            return response()->json('Introducer Deleted Successfully');

        } else {

            return response()->json('Error Deleting the Introducer');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    //    Get PF Introducer List by Company ID
    public function getintroducerbycompanyid($company_id)
    {
        try{
            return Introducer::join("companyregistrations", "companyregistrations.company_id", "=", "introducers.introducer_company_id")
            ->with('introducerBranch')
            ->where("introducer_company_id", "=", $company_id)
            ->where('introducers.registration_type', '=', 'PF')
            ->where('introducers.effective_end_date', '=', NULL)
            ->get([
                'introducers.id', 'introducers.introducer_id',
                'introducers.introducer_company_id', 'introducers.introducer_business_code',
                'introducers.percentage_share', 'introducers.introducer_branch',
                'introducers.introducer_department', 'introducers.effective_start_date',
                'introducers.effective_end_date'
            ]);
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

}
