<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partytype extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_type_id',
        'party_type_code',
        'descriptions',
    ];
}
