<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;


class UserLog extends Model
{
    use HasFactory,EncryptedAttribute;
    protected $fillable = [
        'user_id','action','login_date',
        'logout_date','client_ip',
        'country_name','region_name','city_name',
        'latitude','longitude','encrypted'
    ];

    protected $encryptable = [
        'client_ip','country_name','region_name','city_name','latitude','longitude',
    ];
  
}
