<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Proprietordetail extends Model implements Auditable
{
    use HasFactory,EncryptedAttribute, AuditableTrait;

    protected $table = 'proprietordetails';

    protected $fillable = [
        'prop_company_id',
        'proprietor_name',
        'contact_number',
        'email_id',
        'status',
        'address',
        'employee_detail',
        'designation',
        'effective_start_date',
        'effective_end_date',
        'registration_type',
        'encrypted',
    ];

    protected $encryptable = [
        'proprietor_name','contact_number','email_id','designation'
    ];

    public function proprietorBelongsToCompany()
    {
        return $this->belongsTo('App\Models\Companyregistration')
            ->where('effective_end_date','=',NULL);
    }
}
