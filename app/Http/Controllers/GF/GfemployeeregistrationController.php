<?php

namespace App\Http\Controllers\GF;

use App\Http\Controllers\Controller;
use App\Models\Pfemployeeregistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ESolution\DBEncryption\Encrypter;
use Exception;

class GfemployeeregistrationController extends Controller
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
            $emp_data = Pfemployeeregistration::join('companyregistrations', 'pfemployeeregistrations.pf_employee_company_id', '=', 'companyregistrations.company_id')
            ->where('pfemployeeregistrations.registration_type', '=', 'GF')
            ->where('pfemployeeregistrations.effective_end_date','=',NULL)
            ->orderBy('pfemployeeregistrations.created_at', 'DESC')
            ->select(
                'pfemployeeregistrations.pf_employee_id', 'pfemployeeregistrations.id',
                'org_name', 'company_pf_acc_no', 'pf_employee_company_id', 'employee_name', 'date_of_birth',
                'gender', 'marital_status', 'nationality', 'identification_types',
                'identification_no', 'designation', 'department', 'employee_id_no',
                'service_joining_date', 'contact_no', 'pfemployeeregistrations.email_id',
                'pfemployeeregistrations.address', 'basic_pay', 'contribution',
                'employee_contribution_amount', 'employer_contribution_amount', 'status', 'registration_date',
            )->get();

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

    public function store(Request $request)
    {
        try{
            $user_branch = \Auth::user()->users_branch_id;
            $year = Carbon::now()->year;

            $getPfEmployeeSerialNo = DB::table('masteridholders')
            ->join('branches', 'branches.id', '=', 'masteridholders.branch_id')
            ->where('id_type', '=', 'GF_Employee_Registration')
            ->where('registration_type', '=', 'GF')
            ->where('branch_id', $user_branch)
            ->where('f_year', $year)
            ->get(['serial_no', 'branch_code'])
            ->first();

            $empSerial_no = (int)$getPfEmployeeSerialNo->serial_no + 1;
            $branch_code = $getPfEmployeeSerialNo->branch_code;
            $setPFEmployeeAccountNo = 'BIL/GF/' . $year . '/' . $branch_code . '/' . $empSerial_no;
            DB::beginTransaction();
            $gfPfEmployee = new Pfemployeeregistration();
            $pfEmployeeId = date('YmdH') . random_int(666666, 999999);
            $gfPfEmployee->pf_employee_id = $pfEmployeeId;

            $gfPfEmployee->company_pf_acc_no = $request->company_pf_acc_no;
            $gfPfEmployee->pf_emp_acc_no = $request->pf_emp_acc_no;

            $gfPfEmployee->pf_employee_company_id = $request->pf_employee_company_id;
            $gfPfEmployee->employee_name = $request->employee_name;
            $gfPfEmployee->date_of_birth = $request->date_of_birth;
            $gfPfEmployee->gender = $request->gender;
            $gfPfEmployee->marital_status = $request->marital_status;
            $gfPfEmployee->nationality = $request->nationality;
            $gfPfEmployee->identification_types = $request->identification_types;
            $gfPfEmployee->identification_no = $request->identification_no;
            $gfPfEmployee->designation = $request->designation;
            $gfPfEmployee->department = $request->department;
            $gfPfEmployee->employee_id_no = $setPFEmployeeAccountNo;
            $gfPfEmployee->service_joining_date = $request->service_joining_date;
            $gfPfEmployee->contact_no = $request->contact_no;
            $gfPfEmployee->email_id = $request->email_id;
            $gfPfEmployee->address = $request->address;
            $gfPfEmployee->basic_pay = $request->basic_pay;
            $gfPfEmployee->contribution = $request->contribution;
            $gfPfEmployee->employee_contribution_amount = 0;
            $gfPfEmployee->employer_contribution_amount = (float)$request->employer_contribution_amount;
            $gfPfEmployee->total_contribution = $request->total_contribution;
            $gfPfEmployee->status = $request->status;
            $gfPfEmployee->registration_date = Carbon::now()->format('Y-m-d');
            $gfPfEmployee->closing_date = NULL;
            $gfPfEmployee->registration_type = 'GF';

            if ($gfPfEmployee->save()) {

                if(DB::table('masteridholders')
                ->where('branch_id', '=', $user_branch)
                ->where('f_year', '=', $year)
                ->where('id_type', '=', 'GF_Employee_Registration')
                ->where('registration_type', '=', 'GF')
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
            $pfemployee=Pfemployeeregistration::find($id)
                ->where('registration_type', '=', 'GF')
                ->where('effective_end_date', '=', NULL)
                ->get();
                return $pfemployee ? $this->sendResponse($pfemployee,'Employee Details'):$this->sendError('Employee data not found');
            }catch(Exception $e){
                return $this->errorResponse('Page not found');
            }
    }

    public function edit($id)
    {
        return Pfemployeeregistration::find($id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    // Get GF Employee By Employee Id
    public function getEmployeeByEmployeeId($employee_id)
    {
        return Pfemployeeregistration::where('pf_employee_id','=',$employee_id)
            ->with('depositsData')
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    public function update(Request $request, $id)
    {

        $fund_bal = $this->CheckFundBalance($request->pf_employee_id);
        if ($request->status == 'Closed' && $fund_bal > 0) {

            return response()->json(['error', 'message' => 'Cannot update this account as closed account']);

        } else {

            $gfEmployee = Pfemployeeregistration::find($id);
            $gfEmployee->employee_name = $request->employee_name;
            $gfEmployee->date_of_birth = $request->date_of_birth;
            $gfEmployee->gender = $request->gender;
            $gfEmployee->marital_status = $request->marital_status;
            $gfEmployee->nationality = $request->nationality;
            $gfEmployee->identification_types = $request->identification_types;
            $gfEmployee->identification_no = $request->identification_no;
            $gfEmployee->designation = $request->designation;
            $gfEmployee->department = $request->department;
            $gfEmployee->service_joining_date = $request->service_joining_date;
            $gfEmployee->contact_no = $request->contact_no;
            $gfEmployee->email_id = $request->email_id;
            $gfEmployee->address = $request->address;
            $gfEmployee->basic_pay = $request->basic_pay;
            $gfEmployee->contribution = $request->contribution;
            $gfEmployee->employee_contribution_amount = 0;
            $gfEmployee->employer_contribution_amount = $request->employer_contribution_amount;
            $gfEmployee->total_contribution = $request->total_contribution;
            $gfEmployee->status = $request->status;
            $gfEmployee->registration_date = $request->registration_date;
            $gfEmployee->registration_type = 'GF';

            if ($gfEmployee->save()) {

                DB::commit();
                return response()->json(['success','message'=>'Employee updated successfully']);
            } else {

                DB::rollBack();
                return response()->json(['error', 'message' => 'Unable to update an Employee']);
            }
        }
    }

    public function destroy($id)
    {
        $gfEmployee = Pfemployeeregistration::find($id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();

        $current_date = date('Y-m-d');

        if (empty($gfEmployee)) {
            return "Data Not found to be deleted";
        } else if ($gfEmployee->update(['effective_end_date'=> $current_date])) {
            return "GF-Employee Deleted Successfully!";
        } else {
            return "Error Deleting GF-Employee";
        }
    }

    /** Get GF Employee List by Company ID */
    public function getEmployeeListByCompanyId($company_id)
    {
        try{
            $company= DB::table('pfemployeeregistrations')
            ->join('companyregistrations', 'pfemployeeregistrations.pf_employee_company_id', '=', 'companyregistrations.company_id')
            ->where('pf_employee_company_id', $company_id)
            ->where('pfemployeeregistrations.registration_type', '=', 'GF')
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
            ->get();
            return $company ? $this->sendResponse($company,'Company Details'):$this->sendError('Company not found');
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
}
