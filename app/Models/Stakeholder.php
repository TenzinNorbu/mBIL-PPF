<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Stakeholder extends Model implements Auditable
{
    use HasFactory,EncryptedAttribute, AuditableTrait;

    protected $fillable = [
        'stakeholder_name',
        'stakeholder_party_type',
        'employee_id',
        'tpn_no',
        'bank_account_no',
        'bank_name',
        'encrypted',
    ];
    protected $encryptable = [
        'stakeholder_name','stakeholder_party_type','employee_id','tpn_no','bank_account_no','bank_name'
    ];
}
