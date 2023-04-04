<?php

namespace App\Http\Controllers\PfEmployeeRegistration;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use App\Models\Pfemployeeregistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ESolution\DBEncryption\Encrypter;
use Exception;

class PfemployeeregistrationController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:employee-list|employee-create|employee-edit|employee-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:employee-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:employee-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:employee-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        try{
            $emp_data =  Pfemployeeregistration::join('companyregistrations', 'pfemployeeregistrations.pf_employee_company_id', '=', 'companyregistrations.company_id')
            ->where('pfemployeeregistrations.registration_type', '=', 'PF')
            ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
            ->orderBy('pfemployeeregistrations.created_at', 'DESC')
            ->Paginate($perPage = 10, $columns = ['*'], $pageName = 'pages');

        $emp_data->transform(function($employee_data) {
            $employee_data->company_account_no = Encrypter::decrypt($employee_data->company_account_no);
            $employee_data->license_no = Encrypter::decrypt($employee_data->license_no);
            $employee_data->phone_no = Encrypter::decrypt($employee_data->phone_no);
            $employee_data->org_name = Encrypter::decrypt($employee_data->org_name);
            return  $employee_data;
        });
        return $emp_data ? $this->sendResponse($emp_data,'Emp data Details'):$this->sendError('Emp data not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function getEmployeeListByFilterSearch($employee_name) //new api not useed in any other places except in employee dashbaord
    {
        try{
            return Pfemployeeregistration::join('companyregistrations', 'pfemployeeregistrations.pf_employee_company_id', '=', 'companyregistrations.company_id')
                ->where('pfemployeeregistrations.registration_type', '=', 'PF')
                ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
                ->where(function($query) use($employee_name){
                    if($employee_name != ''){
                        $query->where('pfemployeeregistrations.employee_name', 'LIKE', '%'.$employee_name.'%');
                    }
                })
                ->orderBy('pfemployeeregistrations.created_at', 'DESC')
                ->Paginate($perPage = 10, $columns = ['*'], $pageName = 'pages');
            }catch(Exception $e){
                return $this->errorResponse('Page not found');
            }
    }

    public function store(Request $request)
    {
        try{
            $user_branch = \Auth::user()->users_branch_id;
        $year = Carbon::now()->year;

        $getPfEmployeeSerialNo = DB::table('masteridholders')
        ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
        ->where('id_type', '=', 'Employee_Registration')
        ->where('registration_type', '=', 'PF')
        ->where('branch_id', $user_branch)
        ->where('f_year', $year)
        ->get(['serial_no', 'branch_code'])
        ->first();

        $empSerial_no = (int)$getPfEmployeeSerialNo->serial_no + 1;
        $branch_code = $getPfEmployeeSerialNo->branch_code;
        $setPFEmployeeAccountNo = 'BIL/PF/' . $year . '/' . $branch_code . '/E' . $empSerial_no;

        DB::beginTransaction();
        $pfEmployee = new Pfemployeeregistration();
        $pfEmployeeId = date('Ymd') . random_int(666666, 999999);
        $pfEmployee->pf_employee_id = $pfEmployeeId;
        $pfEmployee->company_pf_acc_no = $request->company_pf_acc_no;
        $pfEmployee->pf_emp_acc_no = $request->pf_emp_acc_no;
        $pfEmployee->pf_employee_company_id = $request->pf_employee_company_id;
        $pfEmployee->employee_name = $request->employee_name;
        $pfEmployee->date_of_birth = $request->date_of_birth;
        $pfEmployee->gender = $request->gender;
        $pfEmployee->marital_status = $request->marital_status;
        $pfEmployee->nationality = $request->nationality;
        $pfEmployee->identification_types = $request->identification_types;
        $pfEmployee->identification_no = $request->identification_no;
        $pfEmployee->designation = $request->designation;
        $pfEmployee->department = $request->department;
        $pfEmployee->employee_id_no = $setPFEmployeeAccountNo;
        $pfEmployee->service_joining_date = $request->service_joining_date;
        $pfEmployee->contact_no = $request->contact_no;
        $pfEmployee->email_id = $request->email_id;
        $pfEmployee->address = $request->address;
        $pfEmployee->basic_pay = (float)$request->basic_pay;
        $pfEmployee->contribution = (float)$request->contribution;
        $pfEmployee->employee_contribution_amount = (float)$request->employee_contribution_amount;
        $pfEmployee->employer_contribution_amount = (float)$request->employer_contribution_amount;
        $pfEmployee->total_contribution = (float)$request->employee_contribution_amount + (float)$request->employer_contribution_amount;
        $pfEmployee->status = $request->status;
        $pfEmployee->registration_date = Carbon::now()->format('Y-m-d');
        $pfEmployee->effective_start_date = Carbon::now()->format('Y-m-d');
        $pfEmployee->effective_end_date = NULL;
        $pfEmployee->closing_date = NULL;
        $pfEmployee->registration_type = 'PF';
        $pfEmployee->encrypted=1;

        if ($pfEmployee->save()) {

            if(DB::table('masteridholders')
                    ->where('branch_id', '=', $user_branch)
                    ->where('f_year', '=', $year)
                    ->where('id_type', '=', 'Employee_Registration')
                    ->where('registration_type', '=', 'PF')
                    ->update(['serial_no' => $empSerial_no])){

                        DB::commit();
                        return response()->json(['success', 'message' => $pfEmployeeId]);

                    }else{

                        DB::rollBack();
                        return response()->json(['error', 'message' => 'Unable to create an Employee']);

                    }
        } else {

            DB::rollBack();
            return response()->json(['error', 'message' => 'Unable to create an Employee']);
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function show($id)
    {
        try{
            return Pfemployeeregistration::find($id)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function edit($id)
    {
        return Pfemployeeregistration::find($id)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    // Get PF Employee By Employee ID
    public function getEmployeeByEmployeeId($employee_id)
    {
        try{
            $emp_data = Pfemployeeregistration::where('pf_employee_id', '=', $employee_id)
            ->with('depositsData')
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get();
            return  $emp_data;

            // $emp_data->transform(function($employee_data) {
            //     $employee_data->contact_no = Encrypter::decrypt($employee_data->contact_no);
            //     $employee_data->designation = Encrypter::decrypt($employee_data->designation);
            //     $employee_data->email_id  = Encrypter::decrypt($employee_data->email_id);
            //     $employee_data->employee_id_no = Encrypter::decrypt($employee_data->employee_id_no);
            //     $employee_data->employee_name = Encrypter::decrypt($employee_data->employee_name);
            //     $employee_data->gender = Encrypter::decrypt($employee_data->gender);
            //     $employee_data->identification_no = Encrypter::decrypt($employee_data->identification_no);
            //     $employee_data->identification_types = Encrypter::decrypt($employee_data->identification_types);
            //     $employee_data->marital_status = Encrypter::decrypt($employee_data->marital_status);
            //     $employee_data->date_of_birth = Encrypter::decrypt($employee_data->date_of_birth);
            //     return  $employee_data;
            // });
            // return $emp_data;
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function CheckFundBalance($emp_id)
    {
        try{
            $net_fund_balance = 0;
        $chkBalSQl = collect(DB::select("select
        SUM(employee_contribution + employer_contribution + interest_accrued_employee_contribution +  interest_accrued_employer_contribution) AS Total_fund_balance,
        SUM(refunds.refund_total_disbursed_amount) as Total_Refund_amount,
        (SUM(employee_contribution + employer_contribution + interest_accrued_employee_contribution +  interest_accrued_employer_contribution) - SUM(refunds.refund_total_disbursed_amount)) as net_balance
        from pfstatements
        left join refunds on refunds.refund_employee_id = pfstatements.employee_ref_id and pfstatements.transaction_ref_no = refunds.refund_ref_no
        where pfstatements.employee_ref_id = '$emp_id'"))->first();

        if ($chkBalSQl != '' && $chkBalSQl != null) {
            $net_fund_balance = $chkBalSQl->net_balance;
        }

        return $net_fund_balance;
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $fund_bal = $this->CheckFundBalance($request->pf_employee_id);
        if ($request->status == 'Closed' && $fund_bal > 0) {

            return response()->json(['error', 'message' => 'Cannot update this account as closed account']);

        } else {

            DB::beginTransaction();

            $pfEmployee = Pfemployeeregistration::find($id);
            $pfEmployee->employee_name = $request->employee_name;
            $pfEmployee->date_of_birth = $request->date_of_birth;
            $pfEmployee->gender = $request->gender;
            $pfEmployee->marital_status = $request->marital_status;
            $pfEmployee->nationality = $request->nationality;
            $pfEmployee->identification_types = $request->identification_types;
            $pfEmployee->identification_no = $request->identification_no;
            $pfEmployee->designation = $request->designation;
            $pfEmployee->department = $request->department;
            $pfEmployee->service_joining_date = $request->service_joining_date;
            $pfEmployee->contact_no = $request->contact_no;
            $pfEmployee->email_id = $request->email_id;
            $pfEmployee->address = $request->address;
            $pfEmployee->basic_pay = (float)$request->basic_pay;
            $pfEmployee->contribution = (float)$request->contribution;
            $pfEmployee->employee_contribution_amount = (float)$request->employee_contribution_amount;
            $pfEmployee->employer_contribution_amount = (float)$request->employer_contribution_amount;
            $pfEmployee->total_contribution = (float)$request->total_contribution;
            $pfEmployee->status = $request->status;
            $pfEmployee->registration_date = $request->registration_date;
            $pfEmployee->registration_type = 'PF';

            if ($pfEmployee->save()) {

                DB::commit();
                return response()->json(['success', 'message' => 'Employee updated successfully']);

            } else {
                DB::rollBack();
                return response()->json(['error', 'message' => 'Unable to update an Employee']);
            }

        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }

    }

    public function destroy($id)
    {
        try{
            $pfEmployee = Pfemployeeregistration::find($id)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get();
        $current_date = date('Y-m-d');

        if (empty($pfEmployee)) {
            return response()->json(['success', 'message' => 'No Data Found']);

        } else if ($pfEmployee->update(['effective_end_date' => $current_date])) {

            return response()->json(['success', 'message' => 'Employee Deleted Successfully']);
        } else {

            return response()->json(['error', 'message' => 'Unable to Delete Employee']);
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    /** Get Employee List by Company ID */
    public function getEmployeeListByCompanyId($company_id)
    {
        try{
            return Companyregistration::with('getEmployeeDetails')
            ->where('company_id', $company_id)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get();
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }
}
