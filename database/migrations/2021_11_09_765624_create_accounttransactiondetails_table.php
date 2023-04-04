<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccounttransactiondetailsTable extends Migration
{
    public function up()
    {
        Schema::create('accounttransactiondetails', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('acc_transaction_detail_id');
            //** From Account-Transaction Table */
            $table->bigInteger('acc_transaction_type_id');
            $table->foreign('acc_transaction_type_id')
                ->references('account_transaction_id')
                ->on('accounttransactions');
            //** From Account-Type Table */
            $table->uuid('acc_account_type_id');
            $table->foreign('acc_account_type_id')
                ->references('account_type_id')
                ->on('accounttypes');
            //** From Account-Group Table */
            $table->uuid('acc_account_group_id');
            $table->foreign('acc_account_group_id')
                ->references('account_group_id')
                ->on('accountgroups');
            //** From Account Types Table, account_type_id */
            $table->uuid('acc_sub_ledger_id')->nullable();
            $table->string('acc_narration');
            $table->float('acc_debit_amount');
            $table->float('acc_credit_amount');
            $table->string('acc_reference_no')->nullable();
            //** PF-Company Table */
            $table->bigInteger('acc_company_id')->nullable();
            $table->foreign('acc_company_id')
                ->references('company_id')
                ->on('companyregistrations');
            //** PF-Employee Table */
            $table->bigInteger('acc_employee_id')->nullable();
            $table->foreign('acc_employee_id')
                ->references('pf_employee_id')
                ->on('pfemployeeregistrations');
            $table->string('registration_type')->nullable();
            $table->bigInteger('acc_td_branch_id')->nullable();
            $table->date('acc_effective_start_date');
            $table->date('acc_effective_end_date')->nullable();
            $table->timestamps();
            $table->unique(['acc_transaction_detail_id', 'acc_effective_end_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounttransactiondetails');
    }
}
