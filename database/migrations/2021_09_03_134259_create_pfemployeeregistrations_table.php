<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePfemployeeregistrationsTable extends Migration
{
    public function up()
    {
        Schema::create('pfemployeeregistrations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pf_employee_id')->unique();
            $table->string('company_pf_acc_no');
            $table->bigInteger('pf_employee_company_id');
            $table->foreign('pf_employee_company_id')
                ->references('company_id')
                ->on('companyregistrations')
                ->onDelete('cascade');
            $table->string('employee_name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('identification_types')->nullable();
            $table->string('identification_no');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('employee_id_no')->nullable();
            $table->bigInteger('pf_emp_acc_no')->nullable();
            $table->date('service_joining_date');
            $table->integer('contact_no')->nullable();
            $table->string('email_id')->nullable();
            $table->string('address')->nullable();
            $table->integer('basic_pay')->nullable();
            $table->integer('contribution')->nullable();
            $table->bigInteger('employee_contribution_amount')->nullable();
            $table->bigInteger('employer_contribution_amount')->nullable();
            $table->bigInteger('total_contribution')->nullable();
            $table->string('status');
            $table->date('registration_date');
            $table->date('closing_date')->nullable();
            $table->string('registration_type')->nullable();
            $table->date('effective_start_date')->default(\Carbon\Carbon::now()->format('Y-m-d'));
            $table->date('effective_end_date')->nullable();
            $table->string('emp_updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pfemployeeregistrations');
    }
}
