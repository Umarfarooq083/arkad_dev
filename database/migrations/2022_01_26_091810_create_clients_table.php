<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client', 80);
			$table->string('phone', 80);
            $table->string('email', 80);
            $table->date('date');
            $table->string('fax', 80);
            $table->string('position', 80);
            $table->string('client_rep', 80);
            $table->string('sp_position', 80);
            $table->string('sp_name', 80);
            $table->string('hire_period', 80);
            $table->string('operator', 80);
            $table->string('quantity', 80);
            $table->string('estimated_start_hire_date', 80);
            $table->string('ARAMCO_TUV', 80);
            $table->string('site_location', 80);
            $table->string('detail', 80);
            $table->string('description');
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
        Schema::dropIfExists('clients');
    }
}
