<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $incrementing = false;
    protected $primaryKey = 'bank_id';
    protected $table = 'banks';

    protected $fillable = [
        'bank_code',
        'bank_name',
        'bnk_branch',
    ];

}
