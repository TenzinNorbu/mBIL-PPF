<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
Use App\Models\User;
use Spatie\Permission\Traits\HasRoles;

class GetReportsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:view-reports', ['only' => ['GetAllReports']]);
    }

    public function GetAllReports(Request $request)
    {
        $report_name = $request->report_name;
        $registration_type = $request->registration_type;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $user_id = auth('api')->user()->id;
        $hasAdminRole = User::where('id','=',$user_id)
        ->whereHas("roles", function($q){
            $q->where("name", "=","Admin");
            })->get()->first();

        if ($hasAdminRole != NULL) {

                $report_sql = "SELECT * FROM documents ";
                $condition = " where doc_ref_type = '$report_name'";

                if($registration_type != NULL || $registration_type != ''){

        //            $condition = $condition. " AND registration_type = '$registration_type'";
                }

                if($from_date != NULL || $from_date != ''){

        //            $condition = $condition. " AND doc_date >= '$from_date'";
                }

                if($to_date != NULL || $to_date != ''){

        //            $condition = $condition. " AND doc_date <= '$to_date'";
                }

                $report_data = DB::select($report_sql.' '.$condition. ' ' ." order by id DESC");

                return $report_data;

            } else {
                
                $report_sql = "SELECT * FROM documents ";
                $condition = " WHERE doc_ref_type = '$report_name' AND doc_user_id = '$user_id'";

                if($registration_type != NULL || $registration_type != ''){

        //            $condition = $condition. " AND registration_type = '$registration_type'";
                }

                if($from_date != NULL || $from_date != ''){

        //            $condition = $condition. " AND doc_date >= '$from_date'";
                }

                if($to_date != NULL || $to_date != ''){

        //            $condition = $condition. " AND doc_date <= '$to_date'";
                }

                $report_data = DB::select($report_sql.' '.$condition. ' ' ." order by id DESC");

                return $report_data;
            }
        }

        public function downloadStatement($docname)
        {
            $doc_path = storage_path('app/reports/' . $docname);
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $doc_path . '"',
                'Access-Control-Expose-Headers' => 'Content-Disposition'
            ];

            $filename = basename($doc_path);
            return response()->download($doc_path, $filename, $headers);
        }
}