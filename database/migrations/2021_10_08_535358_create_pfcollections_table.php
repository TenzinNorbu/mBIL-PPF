<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePfcollectionsTable extends Migration
{
    public function up()
    {
        Schema::create('pfcollections', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pf_collection_id')->unique();
            $table->bigInteger('pf_collection_company_account_no_id');
            $table->foreign('pf_collection_company_account_no_id')->references('company_id')->on('companyregistrations');
            $table->unsignedBigInteger('pf_collection_branch_id')->nullable();
            $table->foreign('pf_collection_branch_id')->references('id')->on('branches');
            $table->float('pf_collection_amount')->nullable();
            $table->float('excess_refund_amount')->default(0)->nullable();
            $table->date('pf_collection_date');
            $table->integer('pf_collection_for_the_month')->nullable();
            $table->integer('pf_collection_for_the_year')->nullable();
            $table->string('pf_collection_narration')->nullable();
            $table->string('pf_collection_no');
            $table->string('pf_collection_status');
            $table->integer('pf_version_number')->default(0);
            $table->string('registration_type')->nullable();
            $table->string('excess_refund_ref_no');
            $table->string('pf_collection_created_by');
            $table->date('pf_collection_effective_start_date');
            $table->date('pf_collection_effective_end_date')->nullable();
            $table->timestamps();
            $table->unique(['pf_collection_id', 'pf_collection_effective_end_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pfcollections');
    }
}
