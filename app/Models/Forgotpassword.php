<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forgotpassword extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'forgotpasswords';
    public $timestamps = false;

    protected $fillable = [
        'user_email',
        'user_cid',
        'reset_otp',
        'opt_status',
        'user_id',
    ];
}
