<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RenewalListReportController extends Controller
{
    public function RenewalListReport(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $regType = $request->registration_type;
        $branchId = $request->branch_id;

        $renewalLists = "SELECT
            cmp.company_account_no,
            cmp.org_name as company_name,

            (select SUM(total_contribution) from pfemployeeregistrations
            WHERE pfemployeeregistrations.pf_employee_company_id = cmp.company_id
            and pfemployeeregistrations.status = 'Active'
            group by pfemployeeregistrations.pf_employee_company_id) as monthly_cont_amount,

            pfmoudetails.mou_date as mou_date,
            pfmoudetails.mou_expiry_date as mou_expiry_date,
            pfmoudetails.interest_rate as interest_rate,
            (SELECT STRING_AGG (CONCAT('Name:',contact_person_name,';', 'Designation:', designation,';','Contact No:',contact_no),';')
            FROM contactpersons
            where contactpersons.contact_person_company_id = cmp.company_id) as contact_person_details,

            (SELECT STRING_AGG (CONCAT('Name:',proprietor_name,';', 'Designation:', designation,';','Contact No:',contact_number),';')
            FROM proprietordetails
            where proprietordetails.prop_company_id = cmp.company_id) as proprietor_details

            FROM companyregistrations cmp
            INNER JOIN pfmoudetails ON pfmoudetails.pfmou_company_id = cmp.company_id";

        $condition = " WHERE cmp.effective_end_date IS NULL
            AND pfmoudetails.effective_end_date IS NULL
            AND pfmoudetails.mou_expiry_date
            BETWEEN '$fromDate' AND '$toDate'";

        if ($regType != NULL && $regType != '') {

            $condition = $condition . " AND cmp.registration_type = '$regType'";
        }

        if ($branchId != NULL || $branchId != '') {

            $condition = $condition . " AND reg_branch_id = '$branchId'";
            $getBranch = Branch::where('id', '=', $branchId)
                ->get();
            $branchName = $getBranch->first()->branch_name;

        } else {

            $branchName = 'ALL';
        }

        $select_query = $renewalLists . '' . $condition;
        $renewal_lists = DB::select($select_query);

        if ($this->generateMouRenewalReport($renewal_lists, $fromDate, $toDate, $regType, $branchName) == 'success') {

            return response()->json(['success', 'message' => 'MOU Renewal List Report Generated Successfully']);
        } else {

            return response()->json(['error', 'message' => 'Unable to Generate MOU Renewal List Report']);
        }

    }

    public function generateMouRenewalReport($renewal_lists, $fromDate, $toDate, $regType, $branchName)
    {

        $pdf = App::make('dompdf.wrapper');
        $bladeView = view('reports.mourenewallist', compact('renewal_lists', 'fromDate', 'toDate',
            'regType', 'branchName'));
        $pdf->loadHTML($bladeView)->setPaper('a4', 'portrait');
        $fileName = 'mou_renewal_list' . random_int(666666, 999999) . '_' . Carbon::now()->format('YmdHis') . '.pdf'; // $genRandomExtension

        if ($pdf->save(Storage::disk('reports')->put($fileName, $pdf->output()))) {

            DB::table('documents')->insert([
                'doc_type_id' => 310000,
                'doc_ref_no' => date('YmdH') . random_int(6666, 9999),
                'doc_ref_type' => 'MouRenewalList',
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
