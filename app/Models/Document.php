<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'doc_type_id',
        'doc_ref_no',
        'doc_ref_type',
        'doc_type',
        'doc_path',
        'doc_date',
        'document_type',
        'registration_type',
        'doc_user_id'
    ];

    public function refundProcessDcouemnt()
    {
        return $this->belongsTo('App\Models\Refund');
    }

    public function pfCollectionDocData()
    {
        return $this->belongsTo('App\Models\Pfcollection');
    }

}
