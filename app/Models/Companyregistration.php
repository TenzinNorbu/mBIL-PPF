<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Companyregistration extends Model implements Auditable
{
    use HasFactory,EncryptedAttribute,AuditableTrait;
    protected $primaryKey = 'id';
    protected $table = 'companyregistrations';

    protected $fillable = [
        'company_id',
        'org_name',
        'license_no',
        'license_validity',
        'bit_cit_no',
        'org_type',
        'address',
        'cmp_dzongkhag_id',
        'phone_no',
        'email_id',
        'website',
        'post_box_no',
        'reg_branch_id',
        'effective_start_date',
        'effective_end_date',
        'company_account_no',
        'closing_date',
        'registration_type',
        'registered_as',
        'encrypted',
    ];
    protected $encryptable = [
        'org_name','license_no','bit_cit_no','company_account_no','phone_no','email_id','website','post_box_no'
    ];

    /**
     *
     * PF Companies has Many Proprietors
     * has one Introducers
     * has one Contact Person
     * has one PF MOU
     *
     */

    public function proprietorDetails()
    {
        return $this->hasMany('App\Models\Proprietordetail', 'prop_company_id', 'company_id')
            ->where('registration_type','=','PF')
            ->where('effective_end_date','=',NULL);
    }

    public function gfProprietorDetails()
    {
        return $this->hasMany('App\Models\Proprietordetail', 'prop_company_id', 'company_id')
            ->where('registration_type','=','GF')
            ->where('effective_end_date','=',NULL);
    }

    public function introducerList()
    {
        return $this->hasMany('App\Models\Introducer', 'introducer_company_id', 'company_id')
            ->where('registration_type','=','PF')
            ->where('effective_end_date','=',NULL);
    }

    public function gfIntroducerList()
    {
        return $this->hasMany('App\Models\Introducer', 'introducer_company_id', 'company_id')
            ->where('registration_type','=','GF')
            ->where('effective_end_date','=',NULL);
    }

    public function contactPersonList()
    {
        return $this->hasMany('App\Models\Contactperson', 'contact_person_company_id', 'company_id')
            ->where('registration_type','=','PF')
            ->where('effective_end_date','=',NULL);
    }

    public function gfContactPersonList()
    {
        return $this->hasMany('App\Models\Contactperson', 'contact_person_company_id', 'company_id')
            ->where('registration_type','=','GF')
            ->where('effective_end_date','=',NULL);
    }

    public function getPfMouDetails()
    {
        return $this->hasOne('App\Models\Pfmoudetail', 'pfmou_company_id', 'company_id')
            ->where('effective_end_date','=',NULL);
    }

    public function getGfMouDetails()
    {
        return $this->hasOne('App\Models\Pfmoudetail', 'pfmou_company_id', 'company_id')
            ->where('registration_type','=','GF')
            ->where('effective_end_date','=',NULL);
    }

    public function pfEmployees()
    {
        return $this->hasMany('App\Models\Pfemployeeregistration', 'pf_employee_company_id', 'company_id')
            ->where('registration_type','=','PF')
            ->where('effective_end_date','=',NULL);
    }

    public function getEmployeeDetails()
    {
        return $this->hasMany('App\Models\Pfemployeeregistration', 'pf_employee_company_id', 'company_id')
            ->where('effective_end_date','=',NULL);
    }

    public function gfEmployees()
    {
        return $this->hasMany('App\Models\Pfemployeeregistration', 'pf_employee_company_id', 'company_id')
            ->where('registration_type','=','GF')
            ->where('effective_end_date','=',NULL);
    }

    public function getPfCollection()
    {
        return $this->hasMany('App\Models\Pfcollection', 'pf_collection_company_account_no_id','company_id')
            ->where('pf_collection_effective_end_date','=',NULL);
    }

    public function refunds()
    {
        return $this->hasMany('App\Models\Refund', 'refund_company_id', 'company_id');
    }

    public function refundPayments()
    {
        return $this->belongsTo('App\Models\Payment');
    }

    public function cmpOrganization()
    {
        return $this->hasMany('App\Models\Pfemployeeregistration', 'pf_employee_company_id', 'company_id')
            ->where('effective_end_date','=',NULL);
    }

    public function getCompanyWiseStatement()
    {
        return $this->hasMany('App\Models\Pfstatement', 'company_ref_id', 'company_id')
            ->selectRaw('company_ref_id,for_the_month,for_the_year,transaction_ref_no,transaction_date,transaction_type,
                    sum(employee_contribution) as total_employee_contribution,
                    sum(employer_contribution) as total_employer_contribution,
                    sum(interest_accrued_employee_contribution) as total_interest_employee,
                    sum(interest_accrued_employer_contribution) as total_interest_employer,
                    sum(prev_total_disbursed_amount) as disbursed_amount')
            ->groupBy('company_ref_id', 'transaction_date', 'transaction_type',
                'transaction_ref_no','for_the_month','for_the_year','transaction_ref_no');
    }

    public function companyCollectionData() {
        return $this->hasMany('App\Models\Pfcollection','pf_collection_company_account_no_id','company_id')
            ->where('pf_collection_effective_end_date','=',NULL);
    }

    public function companyPayments() {
        return $this->hasOne('App\Models\Payment','payment_company_id','company_id');
    }

}
