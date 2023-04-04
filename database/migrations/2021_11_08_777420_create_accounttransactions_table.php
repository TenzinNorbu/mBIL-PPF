<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccounttransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('accounttransactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_transaction_id')->unique();
            $table->string('account_voucher_type');
            $table->string('account_voucher_number');
            $table->date('account_voucher_date');
            $table->float('account_voucher_amount');
            $table->string('account_voucher_narration')->nullable();
            $table->bigInteger('account_payment_id')->nullable();
            $table->bigInteger('account_collection_id')->nullable();
            $table->foreign('account_collection_id')
                ->references('pf_collection_id')
                ->on('pfcollections');
            $table->string('account_reference_no')->nullable();
            $table->string('account_transaction_mode')->nullable();
            $table->string('account_collection_instrument_no')->nullable();
            $table->string('account_collection_bank')->nullable();
            $table->date('account_cheque_date')->nullable();
            $table->date('account_effective_start_date');
            $table->date('account_effective_end_date')->nullable();
            $table->string('registration_type')->nullable();
            $table->string('account_created_by');
            $table->string('account_created_date');
            $table->timestamps();
            $table->unique(['account_transaction_id', 'account_effective_end_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounttransactions');
    }
}
