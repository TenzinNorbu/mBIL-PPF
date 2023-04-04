<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForgotpasswordsTable extends Migration
{
    public function up()
    {
        Schema::create('forgotpasswords', function (Blueprint $table) {
            $table->id();
            $table->string('user_email');
            $table->string('user_cid');
            $table->bigInteger('reset_otp');
        });
    }

    public function down()
    {
        Schema::dropIfExists('forgotpasswords');
    }
}
