<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Nominee extends Model implements Auditable
{
    use HasFactory,EncryptedAttribute, AuditableTrait;

    protected $table = 'nominees';

    protected $fillable = [
        'nominee_id',
        'nominee_employee_id',
        'name',
        'relationship',
        'identification_no',
        'date_of_birth',
        'contact_no',
        'email_id',
        'address',
        'percentage_share',
        'remarks',
        'registration_type',
        'effective_start_date',
        'effective_end_date',
        'encrypted',
    ];

    protected $encryptable = [
        'name','identification_no','date_of_birth','contact_no','email_id','email_id','percentage_share'
    ];

    public function pfCompany()
    {
        $this->belongsTo('App\Models\Companyregistration')
            ->where('effective_end_date', '=', NULL);
    }
}
