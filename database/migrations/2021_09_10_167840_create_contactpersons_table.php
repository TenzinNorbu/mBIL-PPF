<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactpersonsTable extends Migration
{
    public function up()
    {
        Schema::create('contactpersons', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('contact_id');
            $table->bigInteger('contact_person_company_id');
            $table->foreign('contact_person_company_id')
                ->references('company_id')
                ->on('companyregistrations')
                ->onDelete('cascade');
            $table->string('contact_person_name');
            $table->string('contact_no');
            $table->string('fix_line_no')->nullable();
            $table->string('ext_no')->nullable();
            $table->string('email_id')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('address')->nullable();
            $table->string('registration_type')->nullable();
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contactpersons');
    }
}
