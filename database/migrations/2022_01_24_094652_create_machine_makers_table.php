<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineMakersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_makers', function (Blueprint $table) {
            $table->id();
			$table->string('machineMaker', 80);
			$table->bigInteger('orderNo');
			$table->tinyInteger('isActive')->comment('1 = Active, 2= Non Active');
			$table->tinyInteger('isDeleted')->comment('1 = Deleted, 2 = Maintain');
			$table->string('lastUpdatedBy', 80);
			$table->string('createdBy', 80);
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
        Schema::dropIfExists('machine_makers');
    }
}
