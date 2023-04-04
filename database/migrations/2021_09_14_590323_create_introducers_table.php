<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntroducersTable extends Migration
{
    public function up()
    {
        Schema::create('introducers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('introducer_id');
            $table->bigInteger('introducer_company_id');
            $table->foreign('introducer_company_id')
                ->references('company_id')
                ->on('companyregistrations')
                ->onDelete('cascade');
            $table->string('introducer_business_code');
            $table->string('percentage_share');
            //** New Added Branch and Department */
            $table->string('introducer_branch');
            $table->string('introducer_department');
            $table->string('registration_type')->nullable();
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('introducers');
    }
}
