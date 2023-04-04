<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyregistrationsTable extends Migration
{
    public function up()
    {
        Schema::create('companyregistrations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unique();
            $table->string('org_name');
            $table->string('license_no');
            $table->date('license_validity')->nullable();
            $table->string('bit_cit_no')->nullable();
            $table->string('org_type');
            $table->string('address');
            //** New Added Foreign Key From Dzongkhag Table */
            $table->bigInteger('cmp_dzongkhag_id');
            $table->foreign('cmp_dzongkhag_id')
                ->references('dzongkhag_id')
                ->on('dzongkhags');
            //** Newly Added Foreign Key From Branches Table */
            $table->bigInteger('reg_branch_id');
            $table->foreign('reg_branch_id')
                ->references('id')
                ->on('branches');
            $table->string('company_account_no');
            $table->string('phone_no')->nullable();
            $table->string('email_id')->nullable();
            $table->string('website')->nullable();
            $table->string('post_box_no')->nullable();
            $table->date('closing_date')->nullable();
            $table->string('sector_type')->nullable();
            $table->string('registration_type')->nullable();
            $table->string('registered_as')->nullable();
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'effective_end_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('companyregistrations');
    }
}
