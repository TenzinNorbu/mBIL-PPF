<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Payment extends Model implements Auditable
{
    use HasFactory,AuditableTrait;

    protected $primaryKey = 'id';
    protected $table = 'payments';

    protected $fillable = [
        'payment_advise_no',
        'payment_company_id',
        'total_payable_amount',
        'payment_status',
        'payment_process_date',
        'registration_type',
    ];
// chmage to this
    public function pfcompanyData()
    {
        return $this->hasOne('App\Models\Companyregistration', 'company_id', 'payment_company_id')
            ->where('effective_end_date', '=', NULL);
    }

    public function paymentdetails()
    {
        return $this->hasMany('App\Models\Paymentdetail', 'payment_advise_ref_no', 'payment_advise_no');
    }

    public function refundApprovalDoc()
    {
        return $this->hasMany('App\Models\Document', 'doc_ref_no', 'payment_advise_no');
    }


}
