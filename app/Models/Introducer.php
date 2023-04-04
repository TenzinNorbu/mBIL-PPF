<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Introducer extends Model implements Auditable
{
    use HasFactory,AuditableTrait;

    protected $table = 'introducers';

    protected $fillable = [
        'introducer_id',
        'introducer_company_id',
        'introducer_business_code',
        'percentage_share',
        'introducer_branch',
        'introducer_department',
        'registration_type',
        'effective_start_date',
        'effective_end_date',
        'registration_type'
    ];

    public function pfintCompany()
    {
        return $this->belongsTo('App\Models\Companyregistration')
            ->where('effective_end_date', '=', NULL);
    }

    public function introducerBranch()
    {
        return $this->hasMany('App\Models\Branch', 'id', 'introducer_branch');
    }
}
