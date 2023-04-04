<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class User extends Authenticatable implements Auditable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, EncryptedAttribute,
     AuditableTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'dob',
        'designation',
        'employee_id',
        'mobile_number',
        'phone_number',
        'users_branch_id',
        'users_department_id',
        'cid',
        'gender',
        'status',
        'encrypted',
        'password_status',
        'password_created_date',
        'password_reset_date',
        'password_change_date',
    ];

    protected $encryptable = [
        'name','dob','cid','designation','employee_id','mobile_number','phone_number','email','gender'
    ];
  
    protected $hidden = [
        // 'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function guardName()
    {
        return 'api';
    }

    public function userRole() {
        return $this->belongsToMany('Spatie\Permission\Models\Role');
    }
}
