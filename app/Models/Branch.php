<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;


class Branch extends Model implements Auditable
{
    use HasFactory, AuditableTrait;
    use softDeletes;

    protected $table = 'branches';

    protected $fillable = [
        'branch_name',
        'branch_code',
        'branch_location',
    ];

    public function PfCollection()
    {
        return $this->hasMany('App\Models\Pfcollection', 'pf_collection_branch_id')->where('pf_collection_effective_end_date','=',NULL);
    }
}
