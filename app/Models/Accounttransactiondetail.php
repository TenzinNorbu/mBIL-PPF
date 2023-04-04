<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounttransactiondetail extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $keyType = 'string';
    protected $table = 'accounttransactiondetails';

    protected $fillable = [
        'acc_transaction_detail_id',
        'acc_transaction_type_id',
        'acc_account_type_id',
        'acc_account_group_id',
        'acc_sub_ledger_id',
        'acc_narration',
        'acc_debit_amount',
        'acc_credit_amount',
        'acc_reference_no',
        'acc_company_id',
        'acc_employee_id',
        'acc_td_branch_id',
        'acc_effective_start_date',
        'acc_effective_end_date',
        'registration_type',
    ];

    public function getAccountTypeData()
    {
        return $this->hasOne('App\Models\Accounttype', 'account_type_id', 'acc_account_type_id');
    }

}
