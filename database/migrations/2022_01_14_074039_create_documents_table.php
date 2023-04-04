<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->integer('doc_type_id');
            $table->string('doc_ref_no')->nullable();
            $table->string('doc_ref_type');
            $table->string('doc_type');
            $table->string('doc_path');
            $table->date('doc_date')->nullable();
            $table->string('document_type')->nullable();
            $table->string('registration_type')->nullable();
            $table->integer('doc_user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
