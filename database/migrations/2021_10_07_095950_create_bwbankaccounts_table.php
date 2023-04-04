<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBwbankaccountsTable extends Migration
{
    public function up()
    {
        Schema::create('bwbankaccounts', function (Blueprint $table) {
            $table->uuid('bwt_id')->primary();
            $table->unsignedBigInteger('bwt_branch');
            $table->foreign('bwt_branch')
                ->references('id')
                ->on('branches');
            $table->string('bank_acc_no');
            $table->string('bank_acc_description');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bwbankaccounts');
    }
}
