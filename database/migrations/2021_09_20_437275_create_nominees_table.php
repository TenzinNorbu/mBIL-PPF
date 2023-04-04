<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNomineesTable extends Migration
{
    public function up()
    {
        Schema::create('nominees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('nominee_id');
            $table->bigInteger('nominee_employee_id');
            $table->foreign('nominee_employee_id')
                ->references('pf_employee_id')
                ->on('pfemployeeregistrations')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('relationship');
            $table->string('identification_no');
            $table->date('date_of_birth');
            $table->string('contact_no')->nullable();
            $table->string('email_id')->unique();
            $table->string('address');
            $table->string('percentage_share');
            $table->string('remarks');
            $table->string('registration_type')->nullable();
            $table->date('effective_start_date')->default(\Carbon\Carbon::now()->format('Y-m-d'));
            $table->date('effective_end_date')->nullable();
            $table->timestamps();
            $table->unique('nominee_id', 'effective_end_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('nominees');
    }
}
