<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountgroupsTable extends Migration
{
    public function up()
    {
        Schema::create('accountgroups', function (Blueprint $table) {
            $table->id();
            $table->uuid('account_group_id')->primary();
            $table->string('account_group_code');
            $table->string('account_group_name');
            $table->string('branch_wise');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accountgroups');
    }
}
