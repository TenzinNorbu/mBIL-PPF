<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrgtypesTable extends Migration
{
    public function up()
    {
        Schema::create('orgtypes', function (Blueprint $table) {
            $table->id();
            $table->string('org_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orgtypes');
    }
}
