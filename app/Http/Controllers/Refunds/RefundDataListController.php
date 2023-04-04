<?php

namespace App\Http\Controllers\Refunds;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use App\Models\Payment;
use App\Models\Paymentdetail;
use App\Models\Document;
use App\Models\Pfemployeeregistration;
use App\Models\Refund;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NumberToWords\NumberToWords;
use App\Models\User;
use Spatie\Permission\Traits\HasRoles;
use ESolution\DBEncryption\Encrypter;
use Response;


class RefundDataListController extends Controller
{
    /** Get Active Pf Company List */
    public function getActiveCompanyByCompanyName()
    {
        return Companyregistration::where('closing_date', '=', NULL)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    /** Get Active Employee List by Company ID */
    public function getActiveEmployeeListByCompanyId($companyid)
    {
        $activeEmployeeData = Companyregistration::join('pfemployeeregistrations', 'pfemployeeregistrations.pf_employee_company_id', '=', 'companyregistrations.company_id')
            ->where('pf_employee_company_id','=', $companyid)
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
            ->where('status', '=', 'Active')
            ->get();
          

          $activeEmployeeData->transform(function($employee_data) {
              $employee_data->org_name = Encrypter::decrypt($employee_data->org_name);
              $employee_data->company_account_no = Encrypter::decrypt($employee_data->company_account_no);
              $employee_data->employee_name = Encrypter::decrypt($employee_data->employee_name);
              $employee_data->gender = Encrypter::decrypt($employee_data->gender);
              $employee_data->license_no = Encrypter::decrypt($employee_data->license_no);
              $employee_data->email_id = Encrypter::decrypt($employee_data->email_id);
              $employee_data->date_of_birth = Encrypter::decrypt($employee_data->date_of_birth);
              $employee_data->marital_status = Encrypter::decrypt($employee_data->marital_status);
              $employee_data->identification_types = Encrypter::decrypt($employee_data->identification_types);
              $employee_data->identification_no = Encrypter::decrypt($employee_data->identification_no);
              $employee_data->designation = Encrypter::decrypt($employee_data->designation);
              $employee_data->employee_id_no = Encrypter::decrypt($employee_data->employee_id_no);
              $employee_data->phone_no = Encrypter::decrypt($employee_data->phone_no);
              $employee_data->website = Encrypter::decrypt($employee_data->website);
              $employee_data->post_box_no = Encrypter::decrypt($employee_data->post_box_no);
              $employee_data->bit_cit_no = Encrypter::decrypt($employee_data->bit_cit_no);
              $employee_data->contact_no = Encrypter::decrypt($employee_data->contact_no);
              return  $employee_data;
          });
          return $activeEmployeeData;
    }

    /** End Point Refund Data List for Screen 2 */
    public function getEmployeeDataByEmpCodeId($pf_emp_id)
    {
        return Pfemployeeregistration::where('pfemployeeregistrations.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.pf_employee_id', '=', $pf_emp_id)
            ->get()->first();
    }

    /** End Point Refund Data List for Screen 2 */
    public function getRefundDetailsByEmployeeId($employeeid)
    {
        return Pfemployeeregistration::join('companyregistrations', 'companyregistrations.company_id', '=', 'pfemployeeregistrations.pf_employee_company_id')
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.status', '=', 'Active')
            ->where('pfemployeeregistrations.identification_no', '=', $employeeid)
            ->with('pfcontribution')
            ->with('pfdisbursement')
            ->with('lastTransactionData')
            ->get()->first();
    }

    public function getRefundByEmployeeCidNo($employee_cid)
    {
        return Pfemployeeregistration::join('companyregistrations', 'companyregistrations.company_id', '=', 'pfemployeeregistrations.pf_employee_company_id')
            ->join('pfmoudetails', 'pfmoudetails.pfmou_company_id', '=', 'companyregistrations.company_id')
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
            ->where('pfmoudetails.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.registration_type', '=', 'PF')
            ->where('companyregistrations.registration_type', '=', 'PF')
            ->where('pfemployeeregistrations.status', '=', 'Active')
            ->where('pfemployeeregistrations.identification_no', 'LIKE', "%$employee_cid%") 
            ->with('pfcontribution')
            ->with('pfdisbursement')
            ->with('lastTransactionData')
            ->get()->first();
    }

    public function getRefundByEmployeeIdNo($employee_id)
    {
        $companyname=Companyregistration::join('pfemployeeregistrations', 'pfemployeeregistrations.pf_employee_company_id','=','companyregistrations.company_id')
        ->join('pfmoudetails', 'pfmoudetails.pfmou_company_id', '=', 'companyregistrations.company_id')
        ->select('companyregistrations.org_name','companyregistrations.company_account_no')
        ->where('companyregistrations.effective_end_date', '=', NULL)
        ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
        ->where('pfmoudetails.effective_end_date', '=', NULL)
        ->where('pfemployeeregistrations.registration_type', '=', 'PF')
        ->where('companyregistrations.registration_type', '=', 'PF')
        ->where('pfemployeeregistrations.status', '=', 'Active')
        ->where('pfemployeeregistrations.pf_employee_id', '=', $employee_id)->get();
       

        $refundEmployeeData = Pfemployeeregistration::join('companyregistrations', 'companyregistrations.company_id', '=', 'pfemployeeregistrations.pf_employee_company_id')
            ->join('pfmoudetails', 'pfmoudetails.pfmou_company_id', '=', 'companyregistrations.company_id')
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
            ->where('pfmoudetails.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.registration_type', '=', 'PF')
            ->where('companyregistrations.registration_type', '=', 'PF')
            ->where('pfemployeeregistrations.status', '=', 'Active')
            ->where('pfemployeeregistrations.pf_employee_id', '=', $employee_id)
            ->with('pfcontribution')
            ->with('pfdisbursement')
            ->with('lastTransactionData')
            ->get()->first();
        
        $empName = Pfemployeeregistration::where('pf_employee_id', '=', $employee_id)->get()->first()->employee_name;

        if (empty($refundEmployeeData)) {
            return 'The refund against the employee ' . $empName . ' is closed';
        } else {
            // return $refundEmployeeData;
            return response()->json(['companyname'=>$companyname, 'pfemployee'=>$refundEmployeeData]);

        }
    }

    /** Refund Processed Lists Endpoint */
    public function getRefundProcessDataList()
    {
        $user_id = auth('api')->user()->id;
        $hasAdminRole = User::where('id', '=', $user_id)
            ->whereHas("roles", function ($q) {
                $q->whereIn("name", ['Admin', 'PF-Department']);
            })->get()->first();

        $current_user_branch = auth('api')->user()->users_branch_id;

        if ($hasAdminRole != null) {

            $refund_process_list_data = Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
                    ->join('pfemployeeregistrations', 'refunds.refund_employee_id', '=', 'pfemployeeregistrations.pf_employee_id')
                    ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
                    ->whereIn('refunds.refund_status', ['Processed'])
                    ->orderBy('refunds.created_at', 'DESC')
                    ->get();

            $refund_process_list_data->transform(function($employee_data) {
                $employee_data->org_name = Encrypter::decrypt($employee_data->org_name);
                $employee_data->company_account_no = Encrypter::decrypt($employee_data->company_account_no);
                $employee_data->employee_name = Encrypter::decrypt($employee_data->employee_name);
                $employee_data->license_no = Encrypter::decrypt($employee_data->license_no);
                $employee_data->email_id = Encrypter::decrypt($employee_data->email_id);
                $employee_data->date_of_birth = Encrypter::decrypt($employee_data->date_of_birth);
                $employee_data->marital_status = Encrypter::decrypt($employee_data->marital_status);
                $employee_data->identification_types = Encrypter::decrypt($employee_data->identification_types);
                $employee_data->identification_no = Encrypter::decrypt($employee_data->identification_no);
                $employee_data->employee_id_no = Encrypter::decrypt($employee_data->employee_id_no);
                return  $employee_data;
            });
            return $refund_process_list_data;

        } else {

            $refund_branch = DB::select("SELECT * FROM refunds WHERE reg_branch_id = '$current_user_branch'");
            if (count($refund_branch) == 0) {
                return response()->json(['success', 'message' => 'No Data Available']);
            }

            $refund_process_list_data = Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
                    ->join('pfemployeeregistrations', 'refunds.refund_employee_id', '=', 'pfemployeeregistrations.pf_employee_id')
                    ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
                    ->whereIn('refunds.refund_status', ['Processed'])
                    ->orderBy('refunds.created_at', 'DESC')
                    ->get();

            $refund_process_list_data->transform(function($employee_data) {
                $employee_data->org_name = Encrypter::decrypt($employee_data->org_name);
                $employee_data->employee_name = Encrypter::decrypt($employee_data->employee_name);
                $employee_data->company_account_no = Encrypter::decrypt($employee_data->company_account_no);
                $employee_data->license_no = Encrypter::decrypt($employee_data->license_no);
                $employee_data->email_id = Encrypter::decrypt($employee_data->email_id);
                $employee_data->date_of_birth = Encrypter::decrypt($employee_data->date_of_birth);
                $employee_data->marital_status = Encrypter::decrypt($employee_data->marital_status);
                $employee_data->identification_types = Encrypter::decrypt($employee_data->identification_types);
                $employee_data->identification_no = Encrypter::decrypt($employee_data->identification_no);
                $employee_data->employee_id_no = Encrypter::decrypt($employee_data->employee_id_no);
                return  $employee_data;
            });
            return $refund_process_list_data;
        }
    }

    public function getRefundPendingVerifiedDataList()
    {
        $user_id = auth('api')->user()->id;
        $hasAdminRole = User::where('id', '=', $user_id)
            ->whereHas("roles", function ($q) {
                $q->whereIn("name", ['Admin', 'PF-Department']);
            })->get()->first();

        $current_user_branch = auth('api')->user()->users_branch_id;

        if ($hasAdminRole != null) {

            $refund_pending_approval = Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
                ->join('pfemployeeregistrations', 'refunds.refund_employee_id', '=', 'pfemployeeregistrations.pf_employee_id')
                ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
                ->whereIn('refunds.refund_status', ['Verified'])
                ->orderBy('refunds.created_at', 'DESC')
                ->get();

            $refund_pending_approval->transform(function($refund_data) {
                $refund_data->org_name = Encrypter::decrypt($refund_data->org_name);
                $refund_data->employee_name = Encrypter::decrypt($refund_data->employee_name);
                $refund_data->license_no = Encrypter::decrypt($refund_data->license_no);
                $refund_data->company_account_no = Encrypter::decrypt($refund_data->company_account_no);
                $refund_data->email_id = Encrypter::decrypt($refund_data->email_id);
                $refund_data->company_pf_acc_no = Encrypter::decrypt($refund_data->company_pf_acc_no);
                $refund_data->date_of_birth = Encrypter::decrypt($refund_data->date_of_birth);
                $refund_data->marital_status = Encrypter::decrypt($refund_data->marital_status);
                $refund_data->identification_types = Encrypter::decrypt($refund_data->identification_types);
                $refund_data->identification_no = Encrypter::decrypt($refund_data->identification_no);
                $refund_data->designation = Encrypter::decrypt($refund_data->designation);
                $refund_data->employee_id_no = Encrypter::decrypt($refund_data->employee_id_no);
                $refund_data->contact_no = Encrypter::decrypt($refund_data->contact_no);
                return  $refund_data;
            });
            return $refund_pending_approval;

        } else {

            $refund_branch = DB::select("SELECT * FROM refunds WHERE reg_branch_id = '$current_user_branch'");
            if (count($refund_branch) == 0) {
                return response()->json(['success', 'message' => 'No Data Available']);
            }

            $refund_pending_approval = Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
                ->join('pfemployeeregistrations', 'refunds.refund_employee_id', '=', 'pfemployeeregistrations.pf_employee_id')
                ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
                ->whereIn('refunds.refund_status', ['Verified'])
                ->orderBy('refunds.created_at', 'DESC')
                ->get();

            $refund_pending_approval->transform(function($refund_data) {
                $refund_data->org_name = Encrypter::decrypt($refund_data->org_name);
                $refund_data->employee_name = Encrypter::decrypt($refund_data->employee_name);
                $refund_data->license_no = Encrypter::decrypt($refund_data->license_no);
                $refund_data->company_account_no = Encrypter::decrypt($refund_data->company_account_no);
                $refund_data->email_id = Encrypter::decrypt($refund_data->email_id);
                $refund_data->company_pf_acc_no = Encrypter::decrypt($refund_data->company_pf_acc_no);
                $refund_data->date_of_birth = Encrypter::decrypt($refund_data->date_of_birth);
                $refund_data->marital_status = Encrypter::decrypt($refund_data->marital_status);
                $refund_data->identification_types = Encrypter::decrypt($refund_data->identification_types);
                $refund_data->identification_no = Encrypter::decrypt($refund_data->identification_no);
                $refund_data->designation = Encrypter::decrypt($refund_data->designation);
                $refund_data->employee_id_no = Encrypter::decrypt($refund_data->employee_id_no);
                $refund_data->contact_no = Encrypter::decrypt($refund_data->contact_no);
                return  $refund_data;
            });
            return $refund_pending_approval;
        }
    }

    /** Refund Approved Data List */
    public function getRefundApprovedDataList()
    {
        $user_id = auth('api')->user()->id;
        $hasAdminRole = User::where('id', '=', $user_id)
            ->whereHas("roles", function ($q) {
                $q->whereIn("name", ['Admin', 'PF-Department']);
            })->get()->first();
        $current_user_branch = auth('api')->user()->users_branch_id;

        if ($hasAdminRole != null) {

            $refund_approve_list = Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
                ->join('pfemployeeregistrations', 'refunds.refund_employee_id', '=', 'pfemployeeregistrations.pf_employee_id')
                ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
                ->where('refunds.refund_status', '=', 'Approved')
                ->orderBy('refunds.created_at', 'DESC')
                ->get();

            $refund_approve_list->transform(function($refund_data) {
                $refund_data->org_name = Encrypter::decrypt($refund_data->org_name);
                $refund_data->employee_name = Encrypter::decrypt($refund_data->employee_name);
                $refund_data->license_no = Encrypter::decrypt($refund_data->license_no);
                $refund_data->company_account_no = Encrypter::decrypt($refund_data->company_account_no);
                $refund_data->phone_no = Encrypter::decrypt($refund_data->phone_no);
                $refund_data->email_id = Encrypter::decrypt($refund_data->email_id);
                $refund_data->date_of_birth = Encrypter::decrypt($refund_data->date_of_birth);
                $refund_data->marital_status = Encrypter::decrypt($refund_data->marital_status);
                $refund_data->identification_types = Encrypter::decrypt($refund_data->identification_types);
                $refund_data->identification_no = Encrypter::decrypt($refund_data->identification_no);
                $refund_data->designation = Encrypter::decrypt($refund_data->designation);
                $refund_data->employee_id_no = Encrypter::decrypt($refund_data->employee_id_no);
                $refund_data->contact_no = Encrypter::decrypt($refund_data->contact_no);
                return  $refund_data;
            });
            return $refund_approve_list;

        } else {

            $refund_approve_list = Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
                ->join('pfemployeeregistrations', 'refunds.refund_employee_id', '=', 'pfemployeeregistrations.pf_employee_id')
                ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
                ->where('refunds.refund_status', '=', 'Approved')
                ->orderBy('refunds.created_at', 'DESC')
                ->get();

            $refund_approve_list->transform(function($refund_data) {
                $refund_data->org_name = Encrypter::decrypt($refund_data->org_name);
                $refund_data->employee_name = Encrypter::decrypt($refund_data->employee_name);
                $refund_data->license_no = Encrypter::decrypt($refund_data->license_no);
                $refund_data->company_account_no = Encrypter::decrypt($refund_data->company_account_no);
                $refund_data->phone_no = Encrypter::decrypt($refund_data->phone_no);
                $refund_data->email_id = Encrypter::decrypt($refund_data->email_id);
                $refund_data->date_of_birth = Encrypter::decrypt($refund_data->date_of_birth);
                $refund_data->marital_status = Encrypter::decrypt($refund_data->marital_status);
                $refund_data->identification_types = Encrypter::decrypt($refund_data->identification_types);
                $refund_data->identification_no = Encrypter::decrypt($refund_data->identification_no);
                $refund_data->designation = Encrypter::decrypt($refund_data->designation);
                $refund_data->employee_id_no = Encrypter::decrypt($refund_data->employee_id_no);
                $refund_data->contact_no = Encrypter::decrypt($refund_data->contact_no);
                return  $refund_data;
            });
            return $refund_approve_list;
        }
    }

    /** Refund Completed Data List */
    public function getRefundCompletedDataList()
    {
        $refund_completed_list = Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
            ->join('pfemployeeregistrations', 'refunds.refund_employee_id', '=', 'pfemployeeregistrations.pf_employee_id')
            ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
            ->where('refunds.refund_status', '=', 'Completed')
            ->orderBy('refunds.created_at', 'DESC')
            ->get();

        $refund_completed_list->transform(function($refund_data) {
            $refund_data->org_name = Encrypter::decrypt($refund_data->org_name);
            $refund_data->employee_name = Encrypter::decrypt($refund_data->employee_name);
            $refund_data->license_no = Encrypter::decrypt($refund_data->license_no);
            $refund_data->company_account_no = Encrypter::decrypt($refund_data->company_account_no);
            $refund_data->phone_no = Encrypter::decrypt($refund_data->phone_no);
            $refund_data->email_id = Encrypter::decrypt($refund_data->email_id);
            $refund_data->date_of_birth = Encrypter::decrypt($refund_data->date_of_birth);
            $refund_data->marital_status = Encrypter::decrypt($refund_data->marital_status);
            $refund_data->identification_types = Encrypter::decrypt($refund_data->identification_types);
            $refund_data->identification_no = Encrypter::decrypt($refund_data->identification_no);
            $refund_data->designation = Encrypter::decrypt($refund_data->designation);
            $refund_data->employee_id_no = Encrypter::decrypt($refund_data->employee_id_no);
            $refund_data->contact_no = Encrypter::decrypt($refund_data->contact_no);
            return  $refund_data;
        });
        return $refund_completed_list;
    }

    /** Get Individual Refund Data List by Refund Ref Number */
    public function getRefundDataByRefundRefNo($refund_ref_no)
    {
        return Refund::with('refundProcessedUploadDocs')
            ->with('pfRefundEmployee')
            ->with(['refundTransactionData' => function ($query) use ($refund_ref_no) {
                return $query->where('transaction_ref_no', '=', $refund_ref_no)
                    ->get()->first();
            }])
            ->with('companyDetails')
            ->where('refunds.refund_ref_no', '=', $refund_ref_no)
            ->get();
    }

    /** Get Approved Refund Data List by Refund Ref Number */
    public function getApprovedRefundList($refund_ref_no)
    {
        $user_id = auth('api')->user()->id;
        $hasAdminRole = User::where('id', '=', $user_id)
            ->whereHas("roles", function ($q) {
                $q->whereIn("name", ['Admin', 'PF-Department']);
            })->get()->first();
        $current_user_branch = auth('api')->user()->users_branch_id;

        if ($hasAdminRole != NULL) {

            return Refund::with(['refundTransactionData' => function ($query) use ($refund_ref_no) {
                return $query->where('transaction_ref_no', '=', $refund_ref_no)
                    ->get()->first();
            }])
                ->with('refundProcessedUploadDocs')
                ->with('pfRefundEmployee')
                ->with('companyDetails')
                ->with(['refundPaymentsData' => function ($refundApprovalDoc) {
                    return $refundApprovalDoc->with('refundApprovalDoc')
                        ->get();
                }])
                ->with('refundPaymentAdviseDocs')
                ->where('refunds.refund_ref_no', '=', $refund_ref_no)
                ->get();
        } else {

            return Refund::with(['refundTransactionData' => function ($query) use ($refund_ref_no) {
                return $query->where('transaction_ref_no', '=', $refund_ref_no)
                    ->get()->first();
            }])
                ->with('refundProcessedUploadDocs')
                ->with('pfRefundEmployee')
                ->with('companyDetails')
                ->with(['refundPaymentsData' => function ($refundApprovalDoc) {
                    return $refundApprovalDoc->with('refundApprovalDoc')
                        ->get();
                }])
                ->with('refundPaymentAdviseDocs')
                ->where('refunds.refund_ref_no', '=', $refund_ref_no)
                ->where('refunds.reg_branch_id', '=', $current_user_branch)
                ->get();
        }
    }

    /** Get Refund Uploaded File */
    public function getRefundProcessUploadedfile($docpath)
    {
        if ($docpath == null || $docpath == 'undefined') {

            return response()->json(['error', 'message' => 'Refund is already verified']);
        }

        $refundArrayLists = storage_path('app/refundfiles/' . $docpath);
        
        
        $headers = [
            'Content-Type' => 'application/pdf/doc/docx/xls/csv/txt/html/zip/jpg/jpeg/png',
            'Content-Disposition' => 'attachment; filename="' . $refundArrayLists . '"',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
            'Cache-Control'      => 'nocache, no-store, max-age=0, must-revalidate',
            'Pragma'     => 'no-cache',
        ];

        $filename = basename($refundArrayLists);

        return response()->download($refundArrayLists, $filename, $headers);

    }

    /** Refund Pending Payment List Endpoint */
    public function refundPaymentPendingLists()
    {
        $user_id = auth('api')->user()->id;
        $hasAdminRole = User::where('id', '=', $user_id)
            ->whereHas("roles", function ($q) {
                $q->whereIn("name", ['Admin', 'PF-Department', 'Finance Users']);
            })->get()->first();
        $current_user_branch = auth('api')->user()->users_branch_id;

        if ($hasAdminRole != null) {

            $payment_pending_data = DB::table('companyregistrations')
                ->join('payments', 'companyregistrations.company_id', '=', 'payments.payment_company_id')
                ->join('branches', 'branches.id', '=', 'companyregistrations.reg_branch_id')
                ->where('payment_status', '=', 'RNP')
                ->orWhere('payment_status', '=', '')
                ->orderBy('payments.created_at', 'DESC')
                ->get();

            $payment_pending_data->transform(function($collection_data) {
                $collection_data->org_name = Encrypter::decrypt($collection_data->org_name);
                $collection_data->company_account_no = Encrypter::decrypt($collection_data->company_account_no);
                return  $collection_data;
            });
            return $payment_pending_data;

        } else {

            $refund_branch = DB::select("SELECT * FROM companyregistrations WHERE reg_branch_id = '$current_user_branch'");
            if (count($refund_branch) == 0) {
                return response()->json(['success', 'message' => 'No Data Available']);
            }

            $payment_pending_data = DB::table('companyregistrations')
                ->join('payments', 'companyregistrations.company_id', '=', 'payments.payment_company_id')
                ->join('branches', 'branches.id', '=', 'companyregistrations.reg_branch_id')
                ->where('payment_status', '=', 'RNP')
                ->orWhere('payment_status', '=', '')
                ->orderBy('payments.created_at', 'DESC')
                ->get();

            $payment_pending_data->transform(function($collection_data) {
                $collection_data->org_name = Encrypter::decrypt($collection_data->org_name);
                $collection_data->company_account_no = Encrypter::decrypt($collection_data->company_account_no);
                return  $collection_data;
            });
            return $payment_pending_data;
        }
    }

    /** Refund Payment Completed List Endpoint For PPF AND GF */
    public function refundPaymentCompletedLists()
    {
        return Companyregistration::with(['companyPayments' => function ($query) {
            return $query->with(['paymentdetails' => function ($pdtl_document) {
                return $pdtl_document->with(['paymentDocument' => function ($document_validate) {
                    return $document_validate->orWhere('document_type', '=', 'RefundPaymentDoc')
                    ->orWhere('document_type','=','ExcessPaymentVoucher')
                    ->get();
                }])
                    ->get();
            }])
                ->where('payments.payment_status','=', 'RP')
                ->orderBy('payments.created_at', 'DESC')
                ->get();
        }])
            ->join('branches', 'branches.id', '=', 'companyregistrations.reg_branch_id')
            ->get();
    }

    public function getPaymentVoucherByPaymentAdviseNo($payment_advise_no){

        $document_type = Document::where('doc_ref_no','=',$payment_advise_no)
        ->where('doc_path','Like','%payment_voucher%')
        ->get()->first()->document_type;

        $getCompanyId = Payment::where('payment_advise_no','=',$payment_advise_no)->get()->first()->payment_company_id;

        return Companyregistration::with(['companyPayments' => function ($query) use($document_type) {
            return $query->with(['paymentdetails' => function ($pdtl_document) use($document_type) {
                return $pdtl_document->with(['paymentDocument' => function ($document_validate) use ($document_type) {
                    return $document_validate->where('document_type', '=', $document_type)
                    ->get();
                }])
                    ->get();
            }])
                ->where('payments.payment_status','=', 'RP')
                ->orderBy('payments.created_at', 'DESC')
                ->get();
        }])
            ->join('branches', 'branches.id', '=', 'companyregistrations.reg_branch_id')
            ->where('companyregistrations.company_id','=',$getCompanyId)
            ->get();
    }

    //** get Refund Payment Completed List By PaymentRefNo */
    public function listsRefundPaymentCompletedDetails($refund_ref_no)
    {
        $get_refund_by_id_data = Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
            ->with(['getemployeedata' => function ($query) {
                return $query->get();
            }])
            ->with(['refundPaymentsData' => function ($refundApprovalDoc) {
                return $refundApprovalDoc->with('refundApprovalDoc')
                    ->get();
            }])
            ->with('refundPaymentAdviseDocs')
            ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
            ->where('refund_ref_no', $refund_ref_no)
            ->where('refund_status', '=', 'Completed')
            ->get();

        $get_refund_by_id_data->transform(function($refund_data) {
            $refund_data->org_name = Encrypter::decrypt($refund_data->org_name);
            $refund_data->license_no = Encrypter::decrypt($refund_data->license_no);
            $refund_data->company_account_no = Encrypter::decrypt($refund_data->company_account_no);
            $refund_data->phone_no = Encrypter::decrypt($refund_data->phone_no);
            $refund_data->email_id = Encrypter::decrypt($refund_data->email_id);
            $refund_data->date_of_birth = Encrypter::decrypt($refund_data->date_of_birth);
            $refund_data->marital_status = Encrypter::decrypt($refund_data->marital_status);
            $refund_data->identification_types = Encrypter::decrypt($refund_data->identification_types);
            $refund_data->identification_no = Encrypter::decrypt($refund_data->identification_no);
            $refund_data->designation = Encrypter::decrypt($refund_data->designation);
            $refund_data->employee_id_no = Encrypter::decrypt($refund_data->employee_id_no);
            $refund_data->contact_no = Encrypter::decrypt($refund_data->contact_no);
            return $refund_data;
        });
        return $get_refund_by_id_data->first();
    }

    /** Get Refund PaymentDetails List by PaymentRefNo */
    public function listsRefundPaymentDetails($paymentrefno)
    {
        $payment_emp_data = Payment::with('pfcompanyData')
            ->with(['paymentdetails' => function ($query) {
                return $query->with(['paymentRefundDetails' => function ($refund_dtl_query) {
                    return $refund_dtl_query->where('refund_status','=','Approved')
                        ->get();
                }])
                ->join('pfemployeeregistrations', 'pfemployeeregistrations.pf_employee_id', '=', 'paymentdetails.payment_employee_id')
                ->get();
            }])
            ->with('refundApprovalDoc')
            ->where('payment_advise_no', '=', $paymentrefno)
            ->get();

        $payment_emp_data->transform(function($collection_data) {

           $collection_data->paymentdetails->transform(function($data) use($collection_data){
                $data->employee_name = Encrypter::decrypt($data->employee_name);
                $data->employee_id_no = Encrypter::decrypt($data->employee_id_no);
                return $data;
           });
           return $collection_data;
        });
        return $payment_emp_data;
    }

    public function verifyRefund($refund_ref_no)
    {
        DB::beginTransaction();
        $get_refundStatus = Refund::where('refund_ref_no', $refund_ref_no)
            ->where('registration_type', '=', 'PF')
            ->get()->first();

        $refundStatus = $get_refundStatus->refund_status;
        $employee_id = $get_refundStatus->refund_employee_id;
        $employee_name = Pfemployeeregistration::where('pf_employee_id', '=', $employee_id)
            ->where('registration_type', '=', 'PF')
            ->where('effective_end_date', '=', NULL)
            ->get()->first()->employee_name;

        $company_id = $get_refundStatus->refund_company_id;

        $company_name = Companyregistration::where('company_id', '=', $company_id)
            ->where('effective_end_date', '=', NULL)
            ->where('registration_type', '=', 'PF')
            ->get()->first()->org_name;

        if ($refundStatus == 'Verified') {
            return response()->json(['error', 'message' => 'Refund is already verified']);
        }

        if ($refundStatus == 'Processed') {

            $refund_data = Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
                ->join('pfemployeeregistrations', 'pfemployeeregistrations.pf_employee_id', '=', 'refunds.refund_employee_id')
                ->where('refund_ref_no', $refund_ref_no)
                ->where('refund_status', '=', 'Processed')
                ->get()->first();

//            return $refund_data;

            $verifyRefund = Refund::where('refund_ref_no', $refund_ref_no)
                ->where('registration_type', '=', 'PF')
                ->where('refund_status', '=', 'Processed')
                ->update([
                    'refund_status' => 'Verified',
                    'refund_verified_by' => auth('api')->user()->name,
                    'refund_verified_remarks' => 'Refund Verified by ' . auth('api')->user()->name . ' against the employee ' . $employee_name . ' of company : [' . $company_name . ']',
                    'refund_verified_date' => Carbon::now()->format('Y-m-d'),
                ]);

            $generate_approval_note = $this->createRefundVerifiedDocument($refund_ref_no, $refund_data);

            if ($verifyRefund && $generate_approval_note == 'success') {

                DB::commit();
                return response()->json(['success', 'message' => 'Refund is verified']);

            } else {

                DB::rollBack();
                return response()->json(['error', 'message' => 'Error verifying the refund']);
            }
        }
    }

    public function createRefundVerifiedDocument($refund_ref_no, $refund_data)
    {

        $currentDate = Carbon::now()->format('d/m/Y');
        $total_payable_amount = $refund_data->refund_total_disbursed_amount;
        $whole = intval($total_payable_amount);
        $decimal1 = $total_payable_amount - $whole;
        $decimal2 = round($decimal1, 2);
        $get_substring_value = substr($decimal2, 2);
        $convert_to_int = intval($get_substring_value);
        $f = new \NumberFormatter(locale_get_default(), \NumberFormatter::SPELLOUT);
        $word = $f->format($convert_to_int);

        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('en');
        $numWord = $numberTransformer->toWords($total_payable_amount);
        $number_to_word = $numWord . ' and chhetrum ' . $word;

        $verified_by = auth('api')->user()->name;

        $pdf = App::make('dompdf.wrapper');
        $bladeView = view('refundfiles.refund_committee_approval_note', compact('refund_ref_no', 'number_to_word', 'currentDate', 'refund_data', 'verified_by'));
        $pdf->loadHTML($bladeView);
        $fileName = 'refund_committee_approval_note_' . Carbon::now()->format('YmdHis') . '.pdf';

        if ($pdf->save(Storage::disk('refundslip')->put($fileName, $pdf->output()))) {

            $save_approval_note = DB::table('documents')->insert([
                'doc_type_id' => 768000,
                'doc_ref_no' => $refund_ref_no,
                'doc_ref_type' => 'Refund',
                'doc_type' => 'pdf',
                'doc_path' => $fileName,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'registration_type' => 'PF',
                'document_type' => 'RefundCommNoteDoc',
            ]);

            if (!$save_approval_note) {

                return 'error';

            } else {

                return 'success';
            }

        } else {

            return 'error';
        }
    }
}