<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Pfstatement extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    public $timestamps = false;
    protected $table = 'pfstatements';

    protected $fillable = [
        'transaction_no',
        'company_ref_id',
        'employee_ref_id',
        'transaction_type',
        'transaction_ref_no',
        'transaction_date',
        'for_the_month',
        'for_the_year',
        'employee_contribution',
        'employer_contribution',
        'total_employee_contribution',
        'total_employer_contribution',
        'interest_accrued_employee_contribution',
        'interest_accrued_employer_contribution',
        'total_interest_on_employee_contribution',
        'total_interest_on_employer_contribution',
        'interest_chargeable_amount_1',
        'interest_chargeable_amount_2',
        'gross_os_balance_employee',
        'gross_os_balance_employer',
        'ref_interest_rate',
        'transaction_version_no',
        'prev_total_disbursed_amount',
        'registration_type',
        'created_date',
        'created_by'
    ];

    /**
     * @var float|mixed
     */
    private $employer_contribution;

    public function pfstatementemployee()
    {
        return $this->belongsTo('App\Models\Pfemployeeregistration')
            ->where('effective_end_date', '=', NULL);
    }

    public function pfcompanyRegisteredData()
    {
        return $this->belongsTo('App\Models\Companyregistration')
            ->where('effective_end_date', '=', NULL);
    }

    public function statementCompany()
    {
        return $this->hasOne('App\Models\Companyregistration', 'company_id', 'company_ref_id')
            ->where('effective_end_date', '=', NULL);
    }

    public function pfemployeestatements()
    {

        return $this->hasMany('App\Models\Pfemployeeregistration', 'pf_employee_id', 'employee_ref_id')
            ->where('effective_end_date', '=', NULL);
    }

    public function employeeStatementData()
    {

        return $this->hasMany('App\Models\Pfemployeeregistration', 'pf_employee_company_id', 'company_ref_id')
            ->where('effective_end_date', '=', NULL);
    }


    public function statementCollection()
    {
        return $this->hasOne('App\Models\Pfcollection', 'pf_collection_no', 'transaction_ref_no')
            ->where('pf_collection_effective_end_date', '=', NULL);
    }

    public function refundStatement()
    {
        return $this->hasOne('App\Models\Refund', 'refund_ref_no', 'transaction_ref_no');
    }

    public function gfRefundStatement()
    {
        return $this->hasOne('App\Models\Refund', 'refund_ref_no', 'transaction_ref_no')
            ->where('registration_type', '=', 'GF');
    }

}
