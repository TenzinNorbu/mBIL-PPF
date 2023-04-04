<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePfstatementsTable extends Migration
{
    public function up()
    {
        Schema::create('pfstatements', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no');
            $table->bigInteger('company_ref_id');
            $table->bigInteger('employee_ref_id');
            $table->foreign('employee_ref_id')
                ->references('pf_employee_id')
                ->on('pfemployeeregistrations');
            $table->string('transaction_type');
            $table->string('transaction_ref_no')->nullable();
            $table->date('transaction_date');
            $table->integer('for_the_month');
            $table->integer('for_the_year');
            $table->float('employee_contribution');
            $table->float('employer_contribution');
            $table->float('total_employee_contribution');
            $table->float('total_employer_contribution');
            $table->float('interest_accrued_employee_contribution');
            $table->float('interest_accrued_employer_contribution');
            $table->float('total_interest_on_employee_contribution');
            $table->float('total_interest_on_employer_contribution');
            $table->float('interest_chargeable_amount_1');
            $table->float('interest_chargeable_amount_2');
            $table->float('gross_os_balance_employee');
            $table->float('gross_os_balance_employer');
            $table->float('ref_interest_rate');
            $table->integer('transaction_version_no');
            $table->string('registration_type')->nullable();
            $table->float('prev_total_disbursed_amount')->default(0)->nullable();
            $table->date('created_date');
            $table->string('created_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pfstatements');
    }
}
