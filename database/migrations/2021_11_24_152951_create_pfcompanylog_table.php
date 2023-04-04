<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePfcompanylogTable extends Migration
{
    public function up()
    {
        Schema::create('pfcompanylogs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id');
            $table->date('effective_start_date');
            $table->date('effective_end_date');
            $table->string('updated_by');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pfcompanylog');
    }
}
