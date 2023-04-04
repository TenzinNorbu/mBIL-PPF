<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounttype extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey = 'account_type_id';
    protected $table = 'accounttypes';

    protected $fillable = [
        'account_group_id',
        'acc_code',
        'acc_name',
        'acc_nature',
        'acc_sub_ledger',
        'acc_branch_id',
        'acc_description',
        'registration_type'
    ];

}
