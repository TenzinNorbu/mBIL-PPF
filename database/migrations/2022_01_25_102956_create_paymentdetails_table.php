<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentdetailsTable extends Migration
{
    public function up()
    {
        Schema::create('paymentdetails', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment_advise_ref_no')->nullable();
            $table->foreign('payment_advise_ref_no')
                ->references('payment_advise_no')
                ->on('payments');
            $table->bigInteger('payment_dtl_company_id')->nullable();
            $table->foreign('payment_dtl_company_id')
                ->references('company_id')
                ->on('companyregistrations');
            $table->bigInteger('payment_employee_id')->nullable();
            $table->foreign('payment_employee_id')
                ->references('pf_employee_id')
                ->on('pfemployeeregistrations');
            $table->string('payment_refund_ref_no')->nullable();
            $table->float('payment_contribution_amount')->nullable();
            $table->float('payment_interest_amount')->nullable();
            $table->float('payment_total_amount')->nullable();
            $table->float('payment_contribution_employee')->nullable();
            $table->float('payment_contribution_employer')->nullable();
            $table->float('payment_interest_employee')->nullable();
            $table->float('payment_interest_employer')->nullable();
            $table->string('registration_type')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paymentdetails');
    }
}
