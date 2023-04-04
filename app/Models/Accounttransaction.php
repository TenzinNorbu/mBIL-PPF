<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounttransaction extends Model
{
    use HasFactory;

    protected $table = 'accounttransactions';
    protected $fillable = [
        'account_transaction_id',
        'account_voucher_type',
        'account_voucher_number',
        'account_voucher_date',
        'account_voucher_amount',
        'account_voucher_narration',
        'account_payment_id',
        'account_collection_id',
        'account_reference_no',
        'account_transaction_mode',
        'account_collection_instrument_no',
        'account_collection_bank',
        'account_cheque_date',
        'account_effective_start_date',
        'account_effective_end_date',
        'account_created_by',
        'account_created_date',
        'registration_type',
    ];

    public function pfColectionData()
    {
        return $this->hasOne('App\Models\Pfcollection', 'pf_collection_id', 'account_collection_id')
            ->where('pf_collection_effective_end_date','=',NULL);
    }

}
