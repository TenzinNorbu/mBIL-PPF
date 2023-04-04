<?php

namespace App\Http\Controllers\Refunds;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use App\Models\Contactperson;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Paymentdetail;
use App\Models\Pfemployeeregistration;
use App\Models\Proprietordetail;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mail;
use NumberToWords\NumberToWords;

class RefundApproveController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:approve-refunds', ['only' => ['SaveRefundApprovalData']]);
    }

    /** Refund Approval */
    public function SaveRefundApprovalData(Request $request)
    {
        $data = json_decode($request['data'], true);
        if (empty($data)) {

            return response()->json(['error', 'message' => 'Data request is empty! Please check all the verified data! ']);
        }

        $unique_status = array_unique(array_column($data, 'refund_status'));

        if (in_array('Processed', $unique_status)) {
            return response()->json(['error', 'message' => 'Please verify all the records before approving!']);
        }

        $unique_company = array_unique(array_column($data, 'company_id'));
        $anotherArray = [];
        $i = 0;

        foreach ($unique_company as $k => $v) {

            $unique_cmp_id = $unique_company[$k];
            $total_disbursement_amount = 0;

            foreach ($data as $datas) {

                if ($datas['company_id'] == $unique_cmp_id) {

                    $total_disbursement_amount = $datas['refund_total_disbursed_amount'] + $total_disbursement_amount;
                }
            }

            $anotherArray[$i] = ['company_id' => $unique_cmp_id, 'total_payable_amount' => $total_disbursement_amount];
            $i++;
        }

        DB::beginTransaction();
        foreach ($anotherArray as $payment_data) {

            $refundApprovePayment = new Payment();
            $refundApproveId = Carbon::now()->format('YmdH').random_int(6666666, 9999999);
            $refundApprovePayment->payment_advise_no = $refundApproveId;
            $paymentCompanyId = $payment_data['company_id'];
            $refundApprovePayment->payment_company_id = $paymentCompanyId;

            $refundApprovePayment->total_payable_amount = $payment_data['total_payable_amount'];
            $refundApprovePayment->payment_status = 'RNP';
            $refundApprovePayment->payment_process_date = Carbon::now()->format('Y-m-d');
            $refundApprovePayment->registration_type = 'PF';

            //** MAIL FEATURE START */
            foreach ($data as $datas) {

                $employeeID = $datas['pf_employee_id'];
                $emp_data = Pfemployeeregistration::where('pf_employee_id', '=', $employeeID)
                    ->where('effective_end_date','=', NULL)
                    ->where('registration_type','=','PF')->get()
                    ->first();

                $refund_data= Refund::where('refund_employee_id','=',$employeeID)
                    ->where('registration_type','=','PF')
                    ->get()
                    ->first();

                $company_data = Companyregistration::where('company_id', '=', $paymentCompanyId)
                    ->where('registration_type','=','PF')
                    ->where('effective_end_date','=', NULL)->get()->first();

                $contactMailList = Contactperson::where('contact_person_company_id', '=', $paymentCompanyId)
                    ->where('effective_end_date', '=', NULL)
                    ->where('registration_type', '=', 'PF')->get();
                $proprietorMailList = Proprietordetail::where('prop_company_id', '=', $paymentCompanyId)
                    ->where('registration_type', '=', 'PF')
                    ->where('effective_end_date', '=', NULL)->get();

                $employeeName = $emp_data->employee_name;
                $emp_account_no = $emp_data->pf_emp_acc_no;
                $companyName = $company_data->org_name;

                if (count($contactMailList) > 0 || count($proprietorMailList) > 0) {

                    foreach ($contactMailList as $contact_data) {

                        foreach ($proprietorMailList as $proprietor_data) {
                            $proprietor_email = $proprietor_data->email_id;

                            //** Mail */
                            $details = array(
                                'title' => 'Refund Approval Note',
                                'employee_name' => $employeeName,
                                'company_name' => $companyName,
                                'employee_account_no' => $emp_account_no
                            );

                            try {
                                $contactEmail = $contact_data->email_id;
                                Mail::send('emails.refundapprovemail', $details, function ($message) use ($contactEmail, $proprietor_email) {
                                    $message->from('info.bhutaninsurance@gmail.com', 'PF/GF SYSTEM [BIL]');
                                    $message->to($contactEmail);
                                    $message->cc($proprietor_email);
                                    $message->subject('Refund Approval Note');
                                });

                            } catch (\Exception $e) {
                                //never reach
                            }
                            //** Mail end */
                        }

                    }

                } else {

                    $contactEmail = '';
                    $proprietor_email = '';

                    //** Mail */
                    $details = array(
                        'title' => 'Refund Approval Note',
                        'employee_name' => $employeeName,
                        'company_name' => $companyName,
                        'employee_account_no' => $emp_account_no
                    );

                    try {
                        Mail::send('emails.refundapprove', $details, function ($message) use ($contactEmail, $proprietor_email) {
                            $message->from('info.bhutaninsurance@gmail.com', 'PF/GF SYSTEM [BIL]');
                            $message->to($contactEmail);
                            $message->cc($proprietor_email);
                            $message->subject('Refund Process Request');
                        });

                    } catch (\Exception $e) {
                        //never reach
                    }
                    //** Mail end */
                }
                //** MAIL FEATURE END */
            }

            if (!$refundApprovePayment->save()) {

                DB::rollBack();
                return response()->json(['error', 'message' => 'PF Refund Approve Failed!']);

            } else {

                $save_payment_detail = $this->SavePaymentDetails($request, $refundApproveId, $paymentCompanyId);

                if ($save_payment_detail == 'error') {
                    DB::rollBack();
                    return response()->json('error');

                } else {

                    if ($request->hasFile('approve_file_name')) {
                        $save_add_refund_doc = $this->refundApproveFileUpload($request, $refundApproveId);

                        if ($save_add_refund_doc == 'error') {

                            DB::rollBack();
                            return response()->json('File could not be uploaded or created! ');
                        }
                    }

                    $get_data = json_decode($request['data'], true);
                    $this->createRefundApprovalDocument($get_data, $refundApproveId, $paymentCompanyId, $payment_data['total_payable_amount'], $employeeName, $refund_data);
                }
            }
        }
        DB::commit();
        return response()->json('success');
    }

    public function SavePaymentDetails(Request $request, $refundApproveId, $paymentCompanyId)
    {
        $data = json_decode($request['data'], true);

        foreach ($data as $payment_details_data) {

            if ($payment_details_data['company_id'] == $paymentCompanyId) {

                $refundPaymentApproveDetails = new Paymentdetail();
                $refundPaymentApproveDetails->payment_advise_ref_no = $refundApproveId;
                $refundPaymentApproveDetails->payment_dtl_company_id = $payment_details_data['company_id'];
                $refundApproveCompanyId = $payment_details_data['company_id'];
                $refundPaymentApproveDetails->payment_employee_id = $payment_details_data['pf_employee_id'];
                $refundApproveEmployeeId = $payment_details_data['pf_employee_id'];
                $refundPaymentApproveDetails->payment_refund_ref_no = $payment_details_data['refund_ref_no'];

                // Newly Added Fields
                $refundPaymentApproveDetails->payment_contribution_employee = $payment_details_data['refund_employee_contribution'];
                $refundPaymentApproveDetails->payment_contribution_employer = $payment_details_data['refund_employer_contribution'];
                $refundPaymentApproveDetails->payment_interest_employee = $payment_details_data['refund_interest_on_employee_contr'];
                $refundPaymentApproveDetails->payment_interest_employer = $payment_details_data['refund_interest_on_employer_contr'];

                $refundPaymentApproveDetails->payment_contribution_amount = $payment_details_data['refund_total_contr'];
                $refundPaymentApproveDetails->payment_interest_amount = $payment_details_data['refund_total_interest'];
                $refundPaymentApproveDetails->payment_total_amount = $payment_details_data['refund_total_disbursed_amount'];
                $refundPaymentApproveDetails->registration_type = 'PF';

                $refundNetAmount = $payment_details_data['refund_total_disbursed_amount'];

                if (!$refundPaymentApproveDetails->save()) {

                    return 'error';

                } else {

                    $refund_approval = DB::table('refunds')
                        ->where('refund_ref_no', '=', $payment_details_data['refund_ref_no'])
                        ->where('registration_type', '=', 'PF')
                        ->update([
                            'refund_status' => 'Approved',
                            'payment_advise_ref_no' => $refundApproveId,
                            'refund_approval_date' => Carbon::now()->format('Y-m-d'),
                            'refund_approved_by' => auth('api')->user()->name,
                        ]);

                    if (!$refund_approval) {
                        return 'error';
                    }
                }
            }
        }
        return 'success';
    }

    /** Refund Approve Slip */
    public function createRefundApprovalDocument($get_data, $refundApproveId, $paymentCompanyId, $total_payable_amount, $employeeName, $refund_data)
    {
        $currentDate = Carbon::now()->format('d-m-Y');
        $refundRefNo = 'BIL/PF/' . Carbon::now()->year . '/RF';

        $getCompanyId = Companyregistration::where('company_id','=', $paymentCompanyId)
            ->where('registration_type','=','PF')
            ->where('effective_end_date','=',NULL)
            ->get()->first();

        $orgName = $getCompanyId->org_name;

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

        $pdf = App::make('dompdf.wrapper');
        $bladeView = view('refundfiles.refundapprove', compact('get_data', 'refundRefNo', 'paymentCompanyId',
            'currentDate', 'orgName', 'number_to_word', 'total_payable_amount','employeeName', 'refund_data'));
        $pdf->loadHTML($bladeView);
        $genRefundSlipExtensionNo = random_int(6666, 9999);
        $currentDateTime = Carbon::now()->format('YmdHis');
        $fileName = 'pf_refund_payment_advise_note_' . $genRefundSlipExtensionNo . '_' . $currentDateTime . '.pdf';

        if ($pdf->save(Storage::disk('refundslip')->put($fileName, $pdf->output()))) {

            $save_approval_note = DB::table('documents')->insert([
                'doc_type_id' => 500000,
                'doc_ref_no' => $refundApproveId,
                'doc_ref_type' => 'Refund',
                'doc_type' => 'pdf',
                'doc_path' => $fileName,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'registration_type' => 'PF',
                'document_type' => 'RefundApprovedDoc',
                'doc_user_id' => auth('api')->user()->id
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

    /** Refund Approve Upload */
    public function refundApproveFileUpload(Request $request, $refundApproveId)
    {
        $refund_doc_save = new Document();
        $refund_doc_save->doc_type_id = 400000;
        $refund_doc_save->doc_ref_no = $refundApproveId;
        $refund_doc_save->doc_ref_type = 'Refund';

        $original_file_name = $request->file('approve_file_name')->getClientOriginalName();
        $file_extension = $request->file('approve_file_name')->getClientOriginalExtension();

        $filename = 'refund_approval_upload_' . $refundApproveId . '_' . $original_file_name;

        $refund_doc_save->doc_type = $file_extension;
        $refund_doc_save->doc_path = $filename;
        $refund_doc_save->doc_date = Carbon::now()->format('Y-m-d');
        $refund_doc_save->registration_type = 'PF';
        $refund_doc_save->doc_user_id = auth('api')->user()->id;

        if ($refund_doc_save->save()) {

            $request->file('approve_file_name')->storeAs('/', $filename, 'refundslip');
        } else {

            return 'error';
        }

        return 'success';
    }
}
