<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orgtype extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_name',
    ];
}
