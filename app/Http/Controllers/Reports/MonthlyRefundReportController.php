<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Companyregistration;
use App\Models\Pfemployeeregistration;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MonthlyRefundReportController extends Controller
{
    public function MonthlyRefundReport(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $regType = $request->registration_type;
        $companyId = $request->company_id;
        $employeeId = $request->employee_id;

        $refund_type = $request->Excess_Payment_Refund;

        if ($refund_type == 'Excess Refund') {

          $monthly_refund_data = "SELECT
                  refunds.refund_processing_date as refund_date,
                  a.org_name as company_name,
                  '' as employee_name,
                  0 as contributions_refunded,
                  0 as interest_refunded,
                  refunds.refund_total_disbursed_amount  as total_refunded_amount,
                  (SELECT FORMAT(pfmoudetails.mou_date,'dd-MM-yyyy') FROM pfmoudetails WHERE pfmoudetails.pfmou_company_id = a.company_id
                  AND pfmoudetails.effective_end_date IS NULL) as mou_date

                  FROM refunds INNER JOIN companyregistrations a ON a.company_id = refunds.refund_company_id
				          WHERE refund_processed_remarks ='Excess Payment Refund'";

             $condition = " AND refund_processing_date BETWEEN '$fromDate' AND '$toDate'";

        } else {
          $monthly_refund_data = "SELECT
                  refunds.refund_processing_date as refund_date,
                  a.org_name as company_name,
                  pfemployeeregistrations.employee_name,
                  refunds.refund_total_contr as contributions_refunded,
                  refunds.refund_total_interest as interest_refunded,
                  refunds.refund_total_disbursed_amount as total_refunded_amount,
                  (SELECT FORMAT(pfmoudetails.mou_date,'dd-MM-yyyy') FROM pfmoudetails WHERE pfmoudetails.pfmou_company_id = a.company_id
                  AND pfmoudetails.effective_end_date IS NULL) as mou_date

                  FROM refunds INNER JOIN companyregistrations a ON a.company_id = refunds.refund_company_id
                  INNER JOIN pfemployeeregistrations ON pfemployeeregistrations.pf_employee_id = refunds.refund_employee_id";

                  $condition = " WHERE refund_processing_date BETWEEN '$fromDate' AND '$toDate'";
          }

          if ($regType != NULL && $regType != '') {
              $condition = $condition . " AND refunds.registration_type = '$regType'";
          }else{
              $regType = 'All';
          }

          if ($companyId != NULL && $companyId != '') {
              $condition = $condition . " AND refunds.refund_company_id = '$companyId'";
              $company_data = Companyregistration::where('company_id','=',$companyId)->get()->first();
              $companyName = $company_data->org_name;

          }else{
              $companyName = 'All';
          }

          if ($employeeId != NULL && $employeeId != '') {
              $condition = $condition . " AND refunds.refund_employee_id = '$employeeId'";
              $emp_data = Pfemployeeregistration::where('pf_employee_id','=',$employeeId)->get()->first();
              $employeeName = $emp_data->employee_name;

          }else{
              $employeeName= 'All';
          }

          $select_query = $monthly_refund_data.' '.$condition;
          $monthly_refund_data_lists = DB::select($select_query);

          if ($monthly_refund_data_lists) {

              if ($this->generateMonthlyRefundReport($monthly_refund_data_lists, $fromDate, $toDate,
                      $regType, $companyId, $employeeId,$companyName,$employeeName,$refund_type) == 'success') {

                  return response()->json(['success', 'message' => 'Monthly Refund Report Generated Successfully']);
              } else {

                  return response()->json(['error', 'message' => 'Unable to Generate Monthly Refund Report']);
              }

          } else {
              return response()->json(['error','message'=>'No Refund against the employee '. $employeeName]);
          }
    }

    public function generateMonthlyRefundReport($monthly_refund_data_lists, $fromDate, $toDate, $regType, $companyId, $employeeId,$companyName,$employeeName,$refund_type)
    {
        $pdf = App::make('dompdf.wrapper');
        $bladeView = view('reports.monthlyrefundlist', compact('monthly_refund_data_lists', 'fromDate', 'toDate',
            'regType','companyName','employeeName','refund_type'));
        $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
        $fileName = 'monthly_refund_report' . random_int(666666, 999999) . '_' . Carbon::now()->format('YmdHis') . '.pdf'; // $genRandomExtension

        if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {

            DB::table('documents')->insert([
                'doc_type_id' => 330000,
                'doc_ref_no' => date('YmdH') . random_int(2222, 9999),
                'doc_ref_type' => 'MonthlyRefundReport',
                'doc_type' => 'pdf',
                'doc_path' => $fileName,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'registration_type' => $regType,
                'doc_user_id' => auth('api')->user()->id
            ]);

            return 'success';
        } else {

            return 'error';
        }
    }
}
