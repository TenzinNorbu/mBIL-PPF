<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStakeholdersTable extends Migration
{
    public function up()
    {
        Schema::create('stakeholders', function (Blueprint $table) {
            $table->id();
            $table->string('stakeholder_name');
            $table->string('stakeholder_party_type');
            $table->string('employee_id');
            $table->string('tpn_no');
            $table->string('bank_account_no');
            $table->string('bank_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stakeholders');
    }
}
