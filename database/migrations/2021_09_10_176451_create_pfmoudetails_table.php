<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePfmoudetailsTable extends Migration
{
    public function up()
    {
        Schema::create('pfmoudetails', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pfmou_company_id');
            $table->foreign('pfmou_company_id')
                ->references('company_id')
                ->on('companyregistrations')
                ->onDelete('cascade');
            $table->string('mou_ref_no')->nullable();
            $table->date('mou_date');
            $table->date('mou_expiry_date');
            $table->float('interest_rate')->nullable();
            $table->string('registration_type')->nullable();
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pfmoudetails');
    }
}
