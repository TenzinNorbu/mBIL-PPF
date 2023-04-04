<?php

namespace App\Http\Controllers\GF\GF_Refund;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use App\Models\Payment;
use App\Models\Pfemployeeregistration;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NumberToWords\NumberToWords;
Use App\Models\User;
use Spatie\Permission\Traits\HasRoles;
use ESolution\DBEncryption\Encrypter;

class GfRefundDataListController extends Controller
{
    /** Get Active GF Company List */
    public function getActiveCompanyByCompanyName()
    {
        return Companyregistration::where('closing_date', '=', NULL)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get();
    }

    /** Get Active Employee List by Company ID */
    public function getActiveEmployeeListByCompanyId($companyid)
    {
        return Pfemployeeregistration::join('companyregistrations', 'pfemployeeregistrations.pf_employee_company_id', '=', 'companyregistrations.company_id')
            ->where('pf_employee_company_id','=', $companyid)
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
            ->where('status', '=', 'Active')
            ->get();
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
            ->where('companyregistrations.registration_type', '=', 'GF')
            ->where('pfemployeeregistrations.registration_type', '=', 'GF')
            ->where('pfemployeeregistrations.status', '=', 'Active')
            ->where('pfemployeeregistrations.identification_no', '=', $employeeid)
            ->with('gfcontribution')
            ->with('gfdisbursement')
            ->with('gfLastTransactionData')
            ->get()->first();
    }

    public function getRefundByEmployeeCidNo($employee_cid)
    {
        return Pfemployeeregistration::join('companyregistrations', 'companyregistrations.company_id', '=', 'pfemployeeregistrations.pf_employee_company_id')
            ->join('pfmoudetails','pfmoudetails.pfmou_company_id','=','pfemployeeregistrations.pf_employee_company_id')
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
            ->where('pfmoudetails.effective_end_date', '=', NULL)
            ->where('companyregistrations.registration_type', '=', 'GF')
            ->where('pfemployeeregistrations.registration_type', '=', 'GF')
            ->where('pfemployeeregistrations.status', '=', 'Active')
            ->where('pfemployeeregistrations.identification_no', '=', $employee_cid)
            ->with('gfcontribution')
            ->with('gfdisbursement')
            ->with('gfLastTransactionData')
            ->get()->first();
    }

    public function getRefundByEmployeeIdNo($employee_id)
    {
        $refund_process_employeee_data = Pfemployeeregistration::join('companyregistrations', 'companyregistrations.company_id', '=', 'pfemployeeregistrations.pf_employee_company_id')
            ->join('pfmoudetails','pfmoudetails.pfmou_company_id','=','pfemployeeregistrations.pf_employee_company_id')
            ->where('companyregistrations.effective_end_date', '=', NULL)
            ->where('pfemployeeregistrations.effective_end_date', '=', NULL)
            ->where('pfmoudetails.effective_end_date', '=', NULL)
            ->where('companyregistrations.registration_type', '=', 'GF')
            ->where('pfemployeeregistrations.registration_type', '=', 'GF')
            ->where('pfemployeeregistrations.status', '=', 'Active')
            ->where('pfemployeeregistrations.pf_employee_id', '=', $employee_id)
            ->with('gfcontribution')
            ->with('gfdisbursement')
            ->with('gfLastTransactionData')
            ->get();

        $refund_process_employeee_data->transform(function($employee_data) {
            $employee_data->org_name = Encrypter::decrypt($employee_data->org_name);
            $employee_data->license_no = Encrypter::decrypt($employee_data->license_no);
            $employee_data->bit_cit_no = Encrypter::decrypt($employee_data->bit_cit_no);
            $employee_data->company_account_no = Encrypter::decrypt($employee_data->company_account_no);
            $employee_data->phone_no = Encrypter::decrypt($employee_data->phone_no);
            $employee_data->website = Encrypter::decrypt($employee_data->website);
            $employee_data->post_box_no = Encrypter::decrypt($employee_data->post_box_no);
            return  $employee_data;
        });
        return $refund_process_employeee_data;
    }

    /** Refund Processed Lists Endpoint */
    public function getRefundProcessDataList()
    {
        return Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
            ->join('pfemployeeregistrations', 'refunds.refund_employee_id', '=', 'pfemployeeregistrations.pf_employee_id')
            ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
            ->where('refunds.refund_status', '=', 'Processed')
            ->orWhere('refunds.refund_status', '=', 'Verified')
            ->get();
    }

    /** Refund Approved Data List */
    public function getRefundApprovedDataList()
    {
        return Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
            ->join('pfemployeeregistrations', 'refunds.refund_employee_id', '=', 'pfemployeeregistrations.pf_employee_id')
            ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
            ->where('refunds.refund_status', '=', 'Approved')
            ->get();
    }

    /** Refund Completed Data List */
    public function getRefundCompletedDataList()
    {
        return Refund::join('companyregistrations', 'companyregistrations.company_id', '=', 'refunds.refund_company_id')
            ->join('pfemployeeregistrations', 'refunds.refund_employee_id', '=', 'pfemployeeregistrations.pf_employee_id')
            ->join("branches", "branches.id", "=", "refunds.reg_branch_id")
            ->where('refunds.refund_status', '=', 'Completed')
            ->get();
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
        return Refund::with('pfRefundEmployee')
            ->with(['refundTransactionData' => function ($query) use ($refund_ref_no) {
                return $query->where('transaction_ref_no', '=', $refund_ref_no)
                    ->get()->first();
            }])
            ->with('companyDetails')
            ->with(['refundPaymentsData' => function ($refundApprovalDoc) {
                return $refundApprovalDoc->with('refundApprovalDoc')
                    ->get();
            }])
            ->where('refunds.refund_ref_no', '=', $refund_ref_no)
            ->get();
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
            'Access-Control-Expose-Headers' => 'Content-Disposition'
        ];

        $filename = basename($refundArrayLists);

        return response()->download($refundArrayLists, $filename, $headers);
    }

    /** Refund Pending Payment List Endpoint */
    public function refundPaymentPendingLists()
    {
        return Companyregistration::join('payments', 'companyregistrations.company_id', '=', 'payments.payment_company_id')
            ->join('branches', 'branches.id', '=', 'companyregistrations.reg_branch_id')
            ->where('payment_status', '=', 'RNP')
            ->get();
    }

    /** Refund Payment Completed List Endpoint */
    public function refundPaymentCompletedLists()
    {
        return Companyregistration::join('payments', 'companyregistrations.company_id', '=', 'payments.payment_company_id')
            ->join('branches', 'branches.id', '=', 'companyregistrations.reg_branch_id')
            ->where('payment_status', '=', 'RP')
            ->get();
    }

    /** Get Refund PaymentDetails List by PaymentRefNo */
    public function listsRefundPaymentDetails($paymentrefno)
    {
        return Payment::with('pfcompanyData')
            ->with(['paymentdetails' => function ($query) {
                return $query->with(['paymentRefundDetails' => function ($refund_dtl_query) {
                    return $refund_dtl_query->where('refund_status', '=', 'Approved')
                        ->get();
                }])
                    ->join('pfemployeeregistrations', 'pfemployeeregistrations.pf_employee_id', '=', 'paymentdetails.payment_employee_id')
                    ->get();
            }])
            ->with('refundApprovalDoc')
            ->where('payment_advise_no', '=', $paymentrefno)
            ->get();
    }

    public function verifyRefund($refund_ref_no)
    {
        DB::beginTransaction();
        $get_refundStatus = Refund::where('refund_ref_no', $refund_ref_no)
            ->where('registration_type', '=', 'GF')
            ->get()->first();

        $refundStatus = $get_refundStatus->refund_status;
        $employee_id = $get_refundStatus->refund_employee_id;
        $employee_name = Pfemployeeregistration::where('pf_employee_id', '=', $employee_id)
            ->where('registration_type', '=', 'GF')
            ->where('effective_end_date', '=', NULL)
            ->get()->first()->employee_name;

        $company_id = $get_refundStatus->refund_company_id;

        $company_name = Companyregistration::where('company_id', '=', $company_id)
            ->where('effective_end_date', '=', NULL)
            ->where('registration_type', '=', 'GF')
            ->get()->first()->org_name;

        if ($refundStatus == 'Verified') {
            return response()->json(['error', 'message' => 'Refund is already verified']);
        }

        if ($refundStatus == 'Processed') {

            $refund_data = Refund::join('companyregistrations','companyregistrations.company_id','=','refunds.refund_company_id')
                ->join('pfemployeeregistrations','pfemployeeregistrations.pf_employee_id','=','refunds.refund_employee_id')
                ->where('refund_ref_no', $refund_ref_no)
                ->where('refund_status','=','Processed')
                ->get()->first();

//            return $refund_data;

            $verifyRefund = Refund::where('refund_ref_no', $refund_ref_no)
                ->where('registration_type', '=', 'GF')
                ->where('refund_status','=','Processed')
                ->update([
                    'refund_status' => 'Verified',
                    'refund_verified_by' => auth('api')->user()->name,
                    'refund_verified_remarks' => 'Refund Verified by ' . auth('api')->user()->name . ' against the employee ' . $employee_name . ' of company : [' . $company_name . ']',
                    'refund_verified_date' => Carbon::now()->format('Y-m-d'),
                ]);

            $verify_document = $this->createRefundVerifiedDocument($refund_ref_no, $refund_data);

            if ($verifyRefund && $verify_document == 'success') {

                DB::commit();
                return response()->json(['success', 'message' => 'Refund is verified']);
            } else {

                DB::rollBack();
                return response()->json(['error', 'message' => 'Error verifying the refund']);
            }
        }
    }

    public function createRefundVerifiedDocument($refund_ref_no, $refund_data) {

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
        $bladeView = view('refundfiles.gf_refund_committee_approval_note', compact('refund_ref_no','number_to_word','currentDate','refund_data','verified_by'));
        $pdf->loadHTML($bladeView);
        $fileName = 'refund_committee_approval_note_' . Carbon::now()->format('YmdHis') . '.pdf';

        if ($pdf->save(Storage::disk('refundslip')->put($fileName, $pdf->output()))) {

            $save_approval_note = DB::table('documents')->insert([
                'doc_type_id' => 978000,
                'doc_ref_no' => $refund_ref_no,
                'doc_ref_type' => 'Refund',
                'doc_type' => 'pdf',
                'doc_path' => $fileName,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'registration_type' => 'GF',
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
