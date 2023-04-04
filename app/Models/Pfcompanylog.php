<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pfcompanylog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'effective_start_date',
        'effective_end_date',
        'updated_by',
    ];
}
