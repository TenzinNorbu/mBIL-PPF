<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paymentdetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'paymentdetails';

    protected $fillable = [
        'payment_advise_ref_no',
        'payment_dtl_company_id',
        'payment_employee_id',
        'payment_refund_ref_no',
        'payment_contribution_amount',
        'payment_interest_amount',
        'payment_total_amount',

        // Newly Added Fields
        'payment_contribution_employee',
        'payment_contribution_employer',
        'payment_interest_employee',
        'payment_interest_employer',

        'registration_type',
    ];

    public function paymentData()
    {
        return $this->hasOne('App\Models\Payment', 'payment_advise_no', 'payment_advise_ref_no');
    }

    public function paymentRefundDetails()
    {
        return $this->hasMany('App\Models\Refund', 'refund_employee_id', 'payment_employee_id');
    }

    public function paymentDocument() {
        return $this->hasMany('App\Models\Document','doc_ref_no','payment_advise_ref_no');
    }

}
