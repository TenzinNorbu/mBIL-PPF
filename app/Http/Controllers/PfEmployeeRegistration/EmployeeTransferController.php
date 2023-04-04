<?php

namespace App\Http\Controllers\PfEmployeeRegistration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pfemployeeregistration;
use Exception;


class EmployeeTransferController extends Controller
{
    // function __construct()
    // {
    //     $this->middleware('permission:employee-transfer', ['only' => ['employeeTransfer']]);
    // }

    public function employeeTransfer(Request $request)
    {
        try{
            $registrationType = $request->registration_type;
        $previous_company_id = $request->company_id;
        $employeeId = $request->employee_id;
        $transfer_company_id = $request->transfer_company_id;
        $transfer_date = $request->transfer_date;

        DB::beginTransaction();
       
        $employeeUpdate = DB::table('pfemployeeregistrations')
           ->where('registration_type', '=', $registrationType)
            ->where('pf_employee_id', '=', $employeeId)
            ->where('pf_employee_company_id', '=', $previous_company_id)
            
            ->update(['pf_employee_company_id' => $transfer_company_id]);
            // return $employeeUpdate;
            

        $depositUpdate = DB::table('pfstatements')
            ->where('registration_type', '=', $registrationType)
            ->where('company_ref_id', '=', $previous_company_id)
            ->where('employee_ref_id', '=', $employeeId)
            ->update(['company_ref_id' => $transfer_company_id]);
           
        $refundUpdate = DB::table('refunds')
            ->where('registration_type', '=', $registrationType)
            ->where('refund_company_id', '=', $previous_company_id)
            ->where('refund_employee_id', '=', $employeeId)
            ->update(['refund_company_id' => $transfer_company_id]);
            
        if ($employeeUpdate !=0 && $depositUpdate!=0 && ($refundUpdate !=0 || $refundUpdate == 0)) {

            DB::commit();
            return response()->json(['success', 'message' => 'Employee Transferred Successfully']);

        } else {

            DB::rollBack();
            return response()->json(['error', 'message' => 'Unable to Transfer Employee']);
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }
}
