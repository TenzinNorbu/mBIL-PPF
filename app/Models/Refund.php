<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;


class Refund extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $primaryKey = 'id';
    protected $table = 'refunds';

    protected $fillable = [
        'refund_ref_no',
        'refund_company_id',
        'refund_employee_id',

        'reg_branch_id',
        'refund_employee_cid',
        'refund_processing_date',
        'refund_processed_by',
        'refund_processed_remarks',

        'refund_approval_date',
        'refund_approved_by',
        'refund_approved_remarks',
        'refund_status',

        'refund_payment_date',
        'refund_payment_processed_by',
        'refund_payment_remarks',

        'refund_employee_contribution',
        'refund_employer_contribution',

        'refund_interest_on_employee_contr',
        'refund_interest_on_employer_contr',

        'refund_as_on_interest_employee',
        'refund_as_on_interest_employer',

        'refund_total_contr',
        'refund_total_interest',
        'refund_total_disbursed_amnt',
        'refund_net_refundable',
        'refund_processed_remarks',
        'registration_type',

        'refund_verified_by',
        'refund_verified_remarks',
        'refund_verified_date',

        'refund_bank_name',
        'refund_bank_account_no'
    ];

    public function pfrefunddisbursement()
    {
        return $this->belongsTo('App\Models\Pfemployeeregistration')
            ->where('effective_end_date','=',NULL);
    }

    public function companyDetails()
    {
        return $this->hasOne('App\Models\Companyregistration', 'company_id', 'refund_company_id')
            ->where('effective_end_date','=',NULL);
    }

    public function refundProcessedUploadDocs()
    {
        return $this->hasMany('App\Models\Document', 'doc_ref_no', 'refund_ref_no');
    }

    public function pfRefundEmployee()
    {
        return $this->hasOne('App\Models\Pfemployeeregistration', 'pf_employee_id', 'refund_employee_id')
            ->where('effective_end_date','=',NULL);
    }

    public function refundTransactionData()
    {
        return $this->hasOne('App\Models\Pfstatement', 'transaction_ref_no', 'refund_ref_no');
    }

    public function refundPaymentsData() {
        return $this->hasMany('App\Models\Payment','payment_company_id','refund_company_id');
    }

    public function refundPaymentAdviseDocs()
    {
        return $this->hasMany('App\Models\Document', 'doc_ref_no', 'payment_advise_ref_no');
    }
    public function getemployeedata()
    {
        return $this->hasMany('App\Models\Pfemployeeregistration', 'pf_employee_id', 'refund_employee_id')
            ->where('effective_end_date','=',NULL);
    }

}
