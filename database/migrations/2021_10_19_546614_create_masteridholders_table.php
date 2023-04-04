<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasteridholdersTable extends Migration
{
    public function up()
    {
        Schema::create('masteridholders', function (Blueprint $table) {
            $table->id();
            $table->string('id_type')->nullable();
            $table->string('branch_id')->nullable();
            $table->string('f_year')->nullable();
            $table->bigInteger('serial_no')->nullable();
            $table->string('registration_type')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('masteridholders');
    }
}
