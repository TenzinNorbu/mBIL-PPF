<?php

namespace App\Http\Controllers\GF;

use App\Http\Controllers\Controller;
use App\Models\Nominee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GfNomineeController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:nominee-list|nominee-create|nominee-edit|nominee-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:nominee-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:nominee-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:nominee-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $listNominees = Nominee::where('registration_type', '=', 'GF')
            ->get();

        if (empty($listNominees)) {
            return 'Nominess are not Added yet !';
        } else {
            return response()->json($listNominees);
        }
    }

    public function store(Request $request)
    {
        request()->validate([
            'name' => 'required',
            'relationship' => 'required',
            'identification_no' => 'required',
            'date_of_birth' => 'required',
            'contact_no' => 'required|unique:nominees',
            'email_id' => 'required',
            'percentage_share' => 'required',
            'remarks' => 'required',
        ]);

        DB::beginTransaction();
        $nominee = new Nominee();
        $nomineeId = date('Ymd') . random_int(666666, 999999);
        $nominee->nominee_id = $nomineeId;
        $nominee->nominee_employee_id = $request->nominee_employee_id;
        $nominee->name = $request->name;
        $nominee->relationship = $request->relationship;
        $nominee->identification_no = $request->identification_no;
        $nominee->date_of_birth = $request->date_of_birth;
        $nominee->contact_no = $request->contact_no;
        $nominee->email_id = $request->email_id;
        $nominee->address = $request->address;

        $nominee->percentage_share = $request->percentage_share;

        $nominee->remarks = $request->remarks;
        $nominee->registration_type = 'PF';
        $nominee->effective_start_date = Carbon::now()->format('Y-m-d');
        $nominee->effective_end_date = NULL;

        $nominees = Nominee::where('nominee_employee_id', '=', $request->nominee_employee_id)
            ->where('effective_end_date','=',null)
            ->get();
        $totalPercent = 0;
        foreach ($nominees as $nominee_data) {

            $totalPercent = $totalPercent + (float)$nominee_data->percentage_share;

        }

        if ((int)$totalPercent > 100) {
            DB::rollBack();
            return response()->json(['error', 'message' => 'Total percentage share cannot be more than 100%']);

        } else {

            DB::commit();
            if ($nominee->save()) {

                return response()->json(['success', 'message' => 'Nominee Successfully Added']);
            } else {

                DB::rollBack();
                return response()->json(['error', 'message' => 'Unable to Add Nominee']);
            }
        }

    }

    public function show($id)
    {
        return Nominee::find($id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    public function edit($id)
    {
        return Nominee::find($id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    public function getNomineeDetailsByEmployeeId($employee_id)
    {
        return Nominee::where('nominee_employee_id', '=', $employee_id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    public function update(Request $request, $id)
    {

        DB::beginTransaction();
        $prev_nominee_data = Nominee::find($id);
        $prev_nominee_data->effective_end_date = Carbon::now()->format('Y-m-d');

        if ($prev_nominee_data->save()) {

            $nominee = new Nominee();
            $nomineeId = date('Ymd') . random_int(666666, 999999);
            $nominee->nominee_id = $nomineeId;
            $nominee->nominee_employee_id = $request->nominee_employee_id;
            $nominee->name = $request->name;
            $nominee->relationship = $request->relationship;
            $nominee->identification_no = $request->identification_no;
            $nominee->date_of_birth = $request->date_of_birth;
            $nominee->contact_no = $request->contact_no;
            $nominee->email_id = $request->email_id;
            $nominee->address = $request->address;

            $nominee->percentage_share = $request->percentage_share;

            $nominee->remarks = $request->remarks;
            $nominee->registration_type = 'PF';
            $nominee->effective_start_date = Carbon::now()->format('Y-m-d');
            $nominee->effective_end_date = NULL;

            $nominees = Nominee::where('nominee_employee_id', '=', $request->nominee_employee_id)
                ->where('effective_end_date','=',null)
                ->get();
            $totalPercent = 0;
            foreach ($nominees as $nominee_data) {

                $totalPercent = $totalPercent + (float)$nominee_data->percentage_share;

            }

            if ((int)$totalPercent > 100) {
                DB::rollBack();
                return response()->json(['error', 'message' => 'Total percentage share cannot be more than 100%']);

            } else {

                DB::commit();
                if ($nominee->save()) {

                    return response()->json(['success', 'message' => 'Nominee Updated Successfully']);
                } else {

                    DB::rollBack();
                    return response()->json(['error', 'message' => 'Unable to Update Nominee']);
                }
            }

        } else {

            DB::rollBack();
            return response()->json(['error', 'message' => 'Unable to Add Nominee']);
        }

    }

    public function destroy($id)
    {
        $nominee = Nominee::find($id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();

        if (empty($nominee)) {

            return 'Nominees Not Found to be Deleted';
        } else if ($nominee->update(['effective_end_date' => Carbon::now()->format('Y-m-d')])) {

            return 'Deleted Nominee';
        } else {

            return 'Error Deleting the Nominee';
        }
    }
}
