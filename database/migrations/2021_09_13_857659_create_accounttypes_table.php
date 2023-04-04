<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccounttypesTable extends Migration
{
    public function up()
    {
        Schema::create('accounttypes', function (Blueprint $table) {
            $table->uuid('account_type_id')->primary();
            $table->uuid('account_group_id');
            $table->foreign('account_group_id')
                ->references('account_group_id')
                ->on('accountgroups');
            $table->string('acc_code');
            $table->string('acc_name');
            $table->string('acc_nature');
            $table->string('acc_sub_ledger')->nullable();
            $table->unsignedBigInteger('acc_branch_id')->nullable();
            $table->foreign('acc_branch_id')
                ->references('id')
                ->on('branches');
            $table->string('acc_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounttypes');
    }
}
