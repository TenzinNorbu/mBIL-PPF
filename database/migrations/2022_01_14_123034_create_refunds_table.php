<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundsTable extends Migration
{
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_ref_no');
            $table->unsignedBigInteger('refund_company_id');
            $table->foreign('refund_company_id')->references('company_id')->on('companyregistrations');
            $table->unsignedBigInteger('refund_employee_id')->nullable();
            $table->foreign('refund_employee_id')->references('pf_employee_id')->on('pfemployeeregistrations');
            $table->unsignedBigInteger('reg_branch_id');
            $table->foreign('reg_branch_id')->references('id')->on('branches');
            $table->string('refund_employee_cid')->nullable();
            $table->date('refund_processing_date')->nullable();
            $table->string('refund_processed_by')->nullable();
            $table->string('refund_processed_remarks')->nullable();
            $table->date('refund_approval_date')->nullable();
            $table->string('refund_approved_by')->nullable();
            $table->string('refund_approved_remarks')->nullable();
            $table->string('refund_status');
            $table->date('refund_payment_date')->nullable();
            $table->string('refund_payment_processed_by')->nullable();
            $table->string('refund_payment_remarks')->nullable();
            $table->float('refund_employee_contribution');
            $table->float('refund_employer_contribution');
            $table->float('refund_interest_on_employee_contr');
            $table->float('refund_interest_on_employer_contr');
            $table->float('refund_as_on_interest_employee');
            $table->float('refund_as_on_interest_employer');
            $table->float('refund_total_contr');
            $table->float('refund_total_interest');
            $table->float('refund_total_disbursed_amount');
            $table->float('refund_net_refundable');
            $table->string('refund_bank_name');
            $table->string('refund_bank_account_no');
            $table->string('registration_type')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('refunds');
    }
}
