<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Pfmoudetail extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $table = 'pfmoudetails';

    protected $fillable = [
        'pfmou_company_id',
        'mou_ref_no',
        'mou_date',
        'mou_expiry_date',
        'interest_rate',
        'effective_start_date',
        'effective_end_date',
        'registration_type',
    ];

    public function belongstoPfCompany()
    {
        return $this->belongsTo('App\Models\Companyregistration')
            ->where('effective_end_date', '=', NULL);
    }
}
