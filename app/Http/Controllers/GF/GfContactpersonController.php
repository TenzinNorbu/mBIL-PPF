<?php

namespace App\Http\Controllers\GF;

use App\Http\Controllers\Controller;
use App\Models\Contactperson;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GfContactpersonController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:contactperson-list|contactperson-create|contactperson-edit|contactperson-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:contactperson-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:contactperson-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:contactperson-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        return Contactperson::where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    public function store(Request $request)
    {
        request()->validate([
            'contact_person_name' => 'required',
        ]);

        $gfContactPerson = new Contactperson();
        $gfContactId = Carbon::now()->format('YmdH') . random_int(666666, 999999);
        $gfContactPerson->contact_id = $gfContactId;

        $gfContactPerson->contact_person_company_id = $request->contact_person_company_id;
        $gfContactPerson->contact_person_name = $request->contact_person_name;
        $gfContactPerson->contact_no = $request->contact_no;
        $gfContactPerson->fix_line_no = $request->fix_line_no;
        $gfContactPerson->ext_no = $request->ext_no;
        $gfContactPerson->email_id = $request->email_id;
        $gfContactPerson->designation = $request->designation;
        $gfContactPerson->department = $request->department;
        $gfContactPerson->address = $request->address;
        $gfContactPerson->effective_start_date = Carbon::now()->format('Y-m-d');
        $gfContactPerson->effective_end_date = NULL;
        $gfContactPerson->registration_type = 'GF';

        if ($gfContactPerson->save()) {
            return response()->json('Contact Person Details Saved Successfully');
        } else {
            return response()->json('Error Saving Contact Person Details');
        }
    }

    public function show($id)
    {
        return Contactperson::find($id)
            ->where('id','=',$id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    public function edit($id)
    {
        $editGfContactPerson = Contactperson::find($id)
            ->where('id','=',$id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();
        return response()->json($editGfContactPerson);
    }

    public function update(Request $request, $id)
    {
        request()->validate([
            'contact_person_name' => 'required',
        ]);

        DB::beginTransaction();
        $prev_contactPerson_data = Contactperson::find($id);
        $prev_contactPerson_data->effective_end_date = Carbon::now()->format('Y-m-d');

        if ($prev_contactPerson_data->save()) {

            $contactPerson = new Contactperson();
            $contactId = date('Ymd') . random_int(666666, 999999);
            $contactPerson->contact_id = $contactId;
            $contactPerson->contact_person_company_id = $request->contact_person_company_id;
            $contactPerson->contact_person_name = $request->contact_person_name;
            $contactPerson->contact_no = $request->contact_no;
            $contactPerson->fix_line_no = $request->fix_line_no;
            $contactPerson->ext_no = $request->ext_no;
            $contactPerson->email_id = $request->email_id;
            $contactPerson->designation = $request->designation;
            $contactPerson->department = $request->department;
            $contactPerson->address = $request->address;
            $contactPerson->effective_start_date = Carbon::now()->format('Y-m-d');
            $contactPerson->effective_end_date = null;
            $contactPerson->registration_type = 'GF';

            if ($contactPerson->save()) {

                DB::commit();
                return response()->json('Contact Person Details Updated Successfully');

            }else{

                DB::rollback();
                return response()->json('Error Updating Contact Person Details');
            }

        } else {

            DB::rollback();
            return response()->json('Error Updating Contact Person Details');
        }
    }

    public function destroy($id)
    {
        $deleteGfContactPerson = Contactperson::find($id)->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();
        $current_date = date('Y-m-d');

        if (empty($deleteGfContactPerson)) {
            return "Data Not Found. ";
        }
        if ($deleteGfContactPerson->update(['effective_end_date'=> $current_date])) {
            return response()->json('Contact Person Deleted Successfully');
        } else {
            return response()->json('Error Deleting the Contact Person');
        }
    }

    // Get GF ContactPerson List By Company ID
    public function getcontactpersoncompanyid($company_id)
    {
        return Contactperson::join("companyregistrations",
            "companyregistrations.company_id", "=", "contactpersons.contact_person_company_id")
            ->where("contactpersons.contact_person_company_id", "=", $company_id)
            ->where('contactpersons.registration_type', '=', 'GF')
            ->where('contactpersons.effective_end_date', '=', NULL)
            ->get(['contactpersons.id', 'contactpersons.contact_id', 'contactpersons.contact_person_company_id',
                'contactpersons.contact_person_name', 'contactpersons.contact_no', 'contactpersons.fix_line_no',
                'contactpersons.ext_no', 'contactpersons.email_id', 'contactpersons.designation',
                'contactpersons.department', 'contactpersons.address', 'contactpersons.department',
                'contactpersons.address', 'contactpersons.effective_start_date', 'contactpersons.effective_end_date'
            ]);
    }
}
