<?php

namespace App\Http\Controllers\Refunds;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class RefundController extends Controller
{
    public function MonthlyPfRefundContributions() {

        try{
            $currentYear = Carbon::now()->format('Y');
            $monthly_cont_lists =  collect(DB::select("SELECT
                    MONTH(refund_processing_date) as month,
                    YEAR(refund_processing_date) as year,
                    refund_total_disbursed_amount as refund_amount
                    FROM refunds
                    WHERE YEAR(refund_processing_date) = '$currentYear' AND registration_type = 'PF'"))->first();

            if ($monthly_cont_lists == NULL) {
                $monthly_cont_lists = 0;
                return $monthly_cont_lists;
            }
            else {
                return $monthly_cont_lists;
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function MonthlyGfRefundContributions() {

        try{
            $currentYear = Carbon::now()->format('Y');
            $monthly_cont_lists =  collect(DB::select("SELECT
                    MONTH(refund_processing_date) as month,
                    YEAR(refund_processing_date) as year,
                    refund_total_disbursed_amount as refund_amount
                    FROM refunds
                    WHERE YEAR(refund_processing_date) = '$currentYear' AND registration_type = 'GF'"))->first();

            if ($monthly_cont_lists == NULL) {
                $monthly_cont_lists = 0;
                return $monthly_cont_lists;
            }
            else {
                return $monthly_cont_lists;
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }

    }

    public function refundTotalAmount(){
    try{
        return collect(DB::select("SELECT SUM(CAST(refund_total_disbursed_amount as int)) as total_refund_amount
            FROM refunds;"))->first();
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

}
