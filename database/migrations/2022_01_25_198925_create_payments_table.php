<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment_advise_no')->unique();
            $table->bigInteger('payment_company_id')->nullable();
            $table->foreign('payment_company_id')
                ->references('company_id')
                ->on('companyregistrations');
            $table->float('total_payable_amount')->nullable();
            $table->string('payment_status');
            $table->date('payment_process_date');
            $table->string('registration_type')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
