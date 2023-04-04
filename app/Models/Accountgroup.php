<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accountgroup extends Model 
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey = 'account_group_id';
    protected $table = 'accountgroups';

    protected $fillable = [
        'account_group_code',
        'account_group_name',
        'branch_wise',
    ];

    public function accountTypeDetails()
    {
        return $this->hasOne('App\Models\Accounttype', 'acc_code', 'account_group_code');
    }
}
