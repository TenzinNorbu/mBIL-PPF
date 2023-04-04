<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Identificationtype extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_types_name',
    ];
}
