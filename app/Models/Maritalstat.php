<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maritalstat extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_type',
    ];
}
