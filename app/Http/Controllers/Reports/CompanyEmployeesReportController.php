<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companyregistration;
use App\Models\Pfemployeeregistration;
use App\Models\Pfstatement;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

class CompanyEmployeesReportController extends Controller
{
    public function employeesReport(Request $request) {
          $fromDate = $request->from_date;
          $toDate = $request->to_date;
          $registrationType = $request->registration_type;
          $organization_id = $request->company_id;

          $company_data = Companyregistration::where('company_id', '=', $organization_id)->first();
          $organization_name = $company_data->org_name;

          $employeeWiseData =  Pfemployeeregistration::where('pf_employee_company_id', '=', $organization_id)
              ->where('status','=','Active')
              ->get();
          
          $pdf = App::make('dompdf.wrapper');
          $bladeView = view('reports.company_employees_report', compact('employeeWiseData','registrationType',
            'fromDate','toDate','organization_name','request'));

          $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
          $genRandomExtension = random_int(333333333, 999999999);
          $currentDateTime = Carbon::now()->format('YmdHis');
          $fileName = 'company_employees_report' . $genRandomExtension . '_' . $currentDateTime . '.pdf';
       
          if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {
                DB::table('documents')->insert([
                'doc_type_id' => 67543678,
                'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                'doc_ref_type' => 'CompanyEmployeesReport',
                'doc_type' => 'pdf',
                'doc_path' => $fileName,
                'doc_date' => Carbon::now()->format('Y-m-d'),
                'registration_type' => $request->registration_type,
                ]);
                return response()->json(['success','message'=>'Company Employees Report Generated Successfully']);
            } else {
                return response()->json(['error','message'=>'Error Generating Company Employees Report']);
            }
            return response()->json(['success','message'=>'Company Employees Report Generated Successfully']);
    }
}
