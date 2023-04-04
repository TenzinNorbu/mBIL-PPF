<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('dob');
            $table->string('designation');
            $table->string('employee_id');
            $table->string('mobile_number');
            $table->string('phone_number')->nullable();

            $table->unsignedBigInteger('users_branch_id');
            $table->foreign('users_branch_id')
                ->references('id')
                ->on('branches');

            $table->unsignedBigInteger('users_department_id');
            $table->foreign('users_department_id')
                ->references('id')
                ->on('departments');

            $table->string('cid');
            $table->string('gender');
            $table->string('status');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('password_status')->default('isChanged');
            $table->string('password_created_date');
            $table->string('password_change_date');
            $table->string('password_reset_date');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
