<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdentificationtypesTable extends Migration
{
    public function up()
    {
        Schema::create('identificationtypes', function (Blueprint $table) {
            $table->id();
            $table->string('id_types_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('identificationtypes');
    }
}
