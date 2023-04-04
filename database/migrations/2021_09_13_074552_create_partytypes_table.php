<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartytypesTable extends Migration
{
    public function up()
    {
        Schema::create('partytypes', function (Blueprint $table) {
            $table->integer('party_type_id')->primary();
            $table->string('party_type_code');
            $table->string('descriptions');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partytypes');
    }
}
