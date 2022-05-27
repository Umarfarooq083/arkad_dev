<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
			$table->string('location', 80);
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
        Schema::dropIfExists('locations');
    }
}
