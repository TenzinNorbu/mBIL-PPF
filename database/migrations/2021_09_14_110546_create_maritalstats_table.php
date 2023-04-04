<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaritalstatsTable extends Migration
{
    public function up()
    {
        Schema::create('maritalstats', function (Blueprint $table) {
            $table->id();
            $table->string('status_type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('maritalstats');
    }
}
