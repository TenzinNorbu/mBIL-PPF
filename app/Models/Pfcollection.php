<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Pfcollection extends Model implements Auditable
{
    use HasFactory, AuditableTrait;
    protected $table = 'pfcollections';

    protected $fillable = [
        'pf_collection_id',
        'pf_collection_company_account_no_id',
        'pf_collection_branch_id',
        'pf_collection_amount',
        'pf_collection_date',
        'pf_collection_for_the_month',
        'pf_collection_for_the_year',
        'pf_collection_narration',
        'pf_collection_no',
        'pf_collection_status',
        'pf_version_number',
        'pf_collection_created_by',
        'pf_collection_effective_start_date',
        'pf_collection_effective_end_date',
        'registration_type',
        'excess_refund_ref_no'
    ];

    public function employeeData() {
        return $this->hasMany('App\Models\Pfemployeeregistration','pf_employee_company_id','pf_collection_company_account_no_id');
    }

    public function pfCollectionCompany()
    {
        return $this->belongsTo('App\Models\Companyregistration', 'pf_collection_company_account_no_id')
            ->where('effective_end_date', '=', NULL);
    }

    public function collectionCompany() {
        return $this->hasOne('App\Models\Companyregistration','company_id','pf_collection_company_account_no_id');
    }

    public function pfCollectionBranch()
    {
        return $this->belongsTo('App\Models\Branch', 'id');
    }

    public function pfGfCollectionBranches()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'pf_collection_branch_id');
    }

    public function getDocumentData()
    {
        return $this->hasMany('App\Models\Document', 'doc_ref_no', 'pf_collection_id');
    }

    public function getCompanyData()
    {
        return $this->hasOne('App\Models\Companyregistration', 'company_id', 'pf_collection_company_account_no_id')
            ->where('effective_end_date', '=', NULL);
    }

    public function getBranchData()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'pf_collection_branch_id');
    }

    public function getAllStatementData()
    {
        return $this->hasMany('App\Models\Pfstatement', 'transaction_ref_no', 'pf_collection_no');
    }

    public function statementAccountTransaction()
    {
        return $this->hasMany('App\Models\Accounttransaction', 'account_reference_no', 'pf_collection_no')
            ->selectRaw("account_reference_no,STRING_AGG(account_voucher_number,'\n') as voucher_number")
            ->where('account_effective_end_date', '=', NULL)
            ->where('account_voucher_type', '=', 'RV')
            ->groupBy('account_reference_no');
    }

    public function colAcctTrsReverseData()
    {
        return $this->hasMany('App\Models\Accounttransaction','account_collection_id','pf_collection_id');
    }

    public function collectionReceiptNo()
    {
        return $this->hasMany('App\Models\Accounttransaction', 'account_reference_no', 'pf_collection_no')
            ->selectRaw("account_reference_no,STRING_AGG(account_voucher_number,'\n') as receipt_no")
            ->where('account_effective_end_date', '=', NULL)
            ->groupBy('account_reference_no');
    }

}
