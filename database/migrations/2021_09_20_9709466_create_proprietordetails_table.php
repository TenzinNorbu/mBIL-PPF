<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProprietordetailsTable extends Migration
{
    public function up()
    {
        Schema::create('proprietordetails', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('p_id');
            $table->bigInteger('prop_company_id');
            $table->foreign('prop_company_id')
                ->references('company_id')
                ->on('companyregistrations')
                ->onDelete('cascade');
            $table->string('proprietor_name');
            $table->string('contact_number');
            $table->string('email_id')->nullable();
            $table->string('address')->nullable();
            $table->string('employee_detail')->nullable();
            $table->string('designation')->nullable();
            $table->string('registration_type')->nullable();
            $table->string('status');
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['p_id', 'effective_end_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('proprietordetails');
    }
}
