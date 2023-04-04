<?php

namespace App\Http\Controllers\ContactPersonRegistration;

use App\Http\Controllers\Controller;
use App\Models\Contactperson;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ContactpersonController extends Controller
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
        try{
            return Contactperson::where('registration_type','=','PF')
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
            'contact_person_name' => 'required',
        ]);

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
        $contactPerson->registration_type = 'PF';
        $contactPerson->encrypted=1;

        if ($contactPerson->save()) {

            return response()->json('Contact Person Details Saved Successfully');

        } else {

            return response()->json('Error Saving Contact Person Details');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function show($id)
    {
       try{
         $getPfContactPerson = Contactperson::find($id)
            ->where('id','=',$id)
            ->where('registration_type','=','PF')
            ->where('effective_end_date','=',NULL)
            ->get();
        return response()->json($getPfContactPerson);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function edit($id)
    {
        try{
            $editPfContactPerson = Contactperson::find($id)
            ->where('id','=',$id)
            ->where('registration_type','=','PF')
            ->where('effective_end_date','=',NULL)
            ->get();
        return response()->json($editPfContactPerson);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function update(Request $request, $id)
    {
        try{
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
            $contactPerson->registration_type = 'PF';

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
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function destroy($id)
    {
       try{
         $current_date = date('Y-m-d');

        $deleteContactPerson = Contactperson::find($id)
            ->where('registration_type','=','PF')
            ->where('effective_end_date','=',NULL)
            ->get();

        if (empty($deleteContactPerson)) {

            return response()->json('Data Not Found.');
        }

        if ($deleteContactPerson->update(['effective_end_date' => $current_date])) {

            return response()->json('Contact Person Deleted Successfully');

        } else {

            return response()->json('Error Deleting the Contact Person');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    // Get ContactPerson List By Company ID
    public function getcontactpersoncompanyid($company_id)
    {
        try{
            return Contactperson::join("companyregistrations",
            "companyregistrations.company_id", "=", "contactpersons.contact_person_company_id")
            ->where("contactpersons.contact_person_company_id", "=", $company_id)
            ->where('contactpersons.registration_type','=','PF')
            ->where('contactpersons.effective_end_date','=',NULL)
            ->get(['contactpersons.id','contactpersons.contact_id','contactpersons.contact_person_company_id',
                'contactpersons.contact_person_name','contactpersons.contact_no','contactpersons.fix_line_no',
                'contactpersons.ext_no','contactpersons.email_id','contactpersons.designation',
                'contactpersons.department','contactpersons.address','contactpersons.department',
                'contactpersons.address','contactpersons.effective_start_date','contactpersons.effective_end_date'
            ]);
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

}
