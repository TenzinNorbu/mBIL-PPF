<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bwbankaccount extends Model
{
    use HasFactory;
    use softDeletes;

    public $incrementing = false;
    protected $primaryKey = 'bwt_id';
    protected $table = 'bwbankaccounts';

    protected $fillable = [
        'bwt_id',
        'bwt_branch',
        'bank_acc_no',
        'bank_acc_description',
    ];
}
