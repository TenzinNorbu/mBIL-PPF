<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Sectortype extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $table = 'sector_type';

    protected $fillable = [
        'sector_id',
        'sector_name'
    ];

}
