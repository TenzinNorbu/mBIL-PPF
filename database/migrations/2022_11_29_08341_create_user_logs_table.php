<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLogsTable extends Migration
{
    public function up()
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('action');
            $table->string('login_date')->nullable();
            $table->string('logout_date')->nullable();
            $table->string('client_ip');
            $table->string('country_name');
            $table->string('region_name');
            $table->string('city_name');
            $table->string('latitude');
            $table->string('longitude');
            $table->integer('encrypted');
            $table->timestamps();
         });
    }
    public function down()
    {
        Schema::dropIfExists('user_logs');
    }
}
