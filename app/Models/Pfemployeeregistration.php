<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Pfemployeeregistration extends Model implements Auditable
{
    use HasFactory,EncryptedAttribute, AuditableTrait;
    protected $keyType = 'string';
    protected $table = 'pfemployeeregistrations';

    protected $fillable = [
        'pf_employee_id',
        'company_pf_acc_no',
        'pf_employee_company_id',
        'employee_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'identification_types',
        'identification_no',
        'designation',
        'department',
        'employee_id_no',
        'service_joining_date',
        'contact_no',
        'email_id',
        'address',
        'basic_pay',
        'contribution',
        'employee_contribution_amount',
        'employer_contribution_amount',
        'total_contribution',
        'status',
        'registration_date',
        'closing_date',
        'effective_start_date',
        'effective_end_date',
        'pf_emp_acc_no',
        'registration_type',
        'emp_updated_by',
        'encrypted',
    ];

    protected $encryptable = [
        'date_of_birth','identification_no','contact_no','email_id','marital_status',
        'employee_id_no','identification_types','designation','employee_name','gender'
     ];

    public function pfOrganization()
    {
        return $this->belongsTo('App\Models\Companyregistration', 'company_id')
            ->where('effective_end_date','=',NULL);
    }

    public function pfcontribution()
    {
        return $this->hasMany('App\Models\Pfstatement', 'employee_ref_id', 'pf_employee_id')
            ->where('registration_type','=','PF')
            ->selectRaw('employee_ref_id, sum(employee_contribution) as total_employee_contribution,
             sum(employer_contribution) as total_employer_contribution,
            sum(interest_accrued_employee_contribution) as total_interest_employee,
            sum(interest_accrued_employer_contribution) as total_interest_employer')
            ->whereIn('transaction_type', ['Deposit'])
            ->groupBy('employee_ref_id');
    }

    public function gfcontribution()
    {
        return $this->hasMany('App\Models\Pfstatement', 'employee_ref_id', 'pf_employee_id')
            ->where('registration_type','=','GF')
            ->selectRaw('employee_ref_id, sum(employee_contribution) as total_employee_contribution,
             sum(employer_contribution) as total_employer_contribution,
            sum(interest_accrued_employee_contribution) as total_interest_employee,
            sum(interest_accrued_employer_contribution) as total_interest_employer')
            ->whereIn('transaction_type', ['Deposit'])
            ->groupBy('employee_ref_id');
    }

    public function pfdisbursement()
    {
        return $this->hasMany('App\Models\Refund', 'refund_employee_id', 'pf_employee_id')
            ->where('registration_type','=','PF')
            ->selectRaw('refund_employee_id, sum(refund_total_disbursed_amount) as refund_total_disbursed_amount')
            ->groupBy('refund_employee_id');
    }

    public function gfdisbursement()
    {
        return $this->hasMany('App\Models\Refund', 'refund_employee_id', 'pf_employee_id')
            ->where('registration_type','=','GF')
            ->selectRaw('refund_employee_id, sum(refund_total_disbursed_amount) as refund_total_disbursed_amount')
            ->groupBy('refund_employee_id');
    }

    public function lastTransactionData()
    {
        return $this->hasOne('App\Models\Pfstatement', 'employee_ref_id', 'pf_employee_id')
            ->orderBy('transaction_version_no', 'DESC')
            ->latest('transaction_date');
    }

    public function gfLastTransactionData()
    {
        return $this->hasOne('App\Models\Pfstatement', 'employee_ref_id', 'pf_employee_id')
            ->where('registration_type','=','GF')
            ->orderBy('transaction_version_no', 'DESC')
            ->latest('transaction_date');
    }

    public function employeeWiseContribution()
    {
        return $this->hasMany('App\Models\Pfstatement', 'employee_ref_id', 'pf_employee_id')
            ->orderBy('transaction_version_no', 'ASC');
    }

    public function gfEmployeeWiseContribution()
    {
        return $this->hasMany('App\Models\Pfstatement', 'employee_ref_id', 'pf_employee_id')
            ->where('registration_type','=','GF')
            ->orderBy('transaction_version_no', 'ASC');
    }

    public function empOrganization()
    {
        return $this->hasOne('App\Models\Companyregistration', 'company_id', 'pf_employee_company_id')
            ->where('effective_end_date','=',NULL);
    }

    public function gfEmpOrganization()
    {
        return $this->hasOne('App\Models\Companyregistration', 'company_id', 'pf_employee_company_id')
            ->where('effective_end_date','=',NULL);
    }

    public function openingBalance()
    {
        return $this->hasOne('App\Models\Pfstatement', 'employee_ref_id', 'pf_employee_id')
            ->orderBy('transaction_version_no', 'ASC');
    }

    public function depositsData() {
        return $this->hasMany('App\Models\Pfstatement','employee_ref_id','pf_employee_id')
            ->orderBy('transaction_version_no', 'ASC');
    }



}
