<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name',30);
            $table->string('last_name',30);
            $table->integer('role_id');
            $table->integer('department_id');
            $table->dateTime('dob')->comment('date of birth');
            $table->integer('secrat_question_id');
            $table->string('secrate_question_answer',255);
            $table->string('user_name',60)->unique();
            $table->string('email',255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->tinyInteger('status')->default(0)->comment('0 inactive, 1 active, 2 deleted');
            $table->integer('created_by')->comment('Authorized user with permission to add user');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
