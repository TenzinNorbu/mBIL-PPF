<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Contactperson extends Model implements Auditable
{
    use HasFactory,EncryptedAttribute, AuditableTrait;

    protected $table = 'contactpersons';

    protected $fillable = [
        'contact_id',
        'contact_person_company_id',
        'contact_person_name',
        'contact_no',
        'fix_line_no',
        'ext_no',
        'email_id',
        'designation',
        'department',
        'address',
        'effective_start_date',
        'effective_end_date',
        'registration_type',
        'encrypted',
    ];

    protected $encryptable = [
        'contact_person_name','contact_no','fix_line_no','ext_no','email_id','designation','department'
    ];

    public function pfcntCompany()
    {
        return $this->belongsTo('App\Models\Companyregistration')
            ->where('effective_end_date','=',NULL);
    }
}
