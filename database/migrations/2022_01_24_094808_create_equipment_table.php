<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
			$table->tinyInteger('isERPPurchaseOrder')->comment('1 = Yes, 2= No')->nullable();
			$table->bigInteger('purchaseOrderProjectID')->nullable();
			$table->bigInteger('purchaseOrderID')->nullable();
			$table->bigInteger('purchaseOrderSupplierID')->nullable();
			$table->bigInteger('purchaseOrderDetailID')->nullable();
			$table->string('purchaseOrderNumber', 80)->nullable();
			$table->date('receivedDate')->nullable();
			$table->bigInteger('groupNumberID');
			$table->bigInteger('equipmentCategoryID');
			$table->string('plantNumber', 80);
			$table->string('supplierPlantNumber', 80);
			$table->string('equipment', 80);
			$table->bigInteger('machineMakerID');
			$table->string('machineModel', 80)->nullable();
			$table->string('capacity', 80)->nullable();
			$table->string('YOM', 80)->nullable();
			$table->string('serialNumber', 80)->nullable();
			$table->string('engineNumber', 80)->nullable();
			$table->string('plateNumber', 80)->nullable();
			$table->date('plateExpiry')->nullable();
			$table->string('remarks', 500)->nullable();
			$table->decimal('hourlyRate', 20, 2)->nullable();
			$table->decimal('dailyRate', 20, 2)->nullable();
			$table->decimal('weeklyRate', 20, 2)->nullable();
			$table->decimal('monthlyRate', 20, 2)->nullable();
			$table->bigInteger('currencyID')->nullable();
			$table->bigInteger('normalHour')->nullable();
			$table->bigInteger('breakHour')->nullable();
			$table->bigInteger('ownershipTypeID');
			$table->bigInteger('hireTypeID');
			$table->bigInteger('equipmentStatusID');
			$table->string('SlNumber', 80)->nullable();
			$table->string('fileName', 80)->nullable();
			$table->string('fileType', 80)->nullable();
			$table->bigInteger('fileSize')->nullable();
			$table->binary('fileData')->nullable();
			$table->tinyInteger('isDeleted')->comment('1 = Deleted, 2 = Maintain');
			$table->string('purchaseOrderSupplier', 80)->nullable();
			$table->string('QRFileName', 80)->nullable();
			$table->binary('QRFileType', 80)->nullable();
			$table->bigInteger('QRFileSize')->nullable();
			$table->bigInteger('QRFileData')->nullable();
			$table->bigInteger('meterTypeID')->nullable();
			$table->bigInteger('fuelTypeID')->nullable();
			$table->bigInteger('fuelCapacity')->nullable();
			$table->bigInteger('fuelStorageCapacity')->nullable();
			$table->tinyInteger('isFuelConsumingEquipment')->comment('1 = Yes, 2 = No')->nullable();
			$table->tinyInteger('isFuelStorageEquipment')->comment('1 = Yes, 2 = No')->nullable();
			$table->tinyInteger('isTimesheetRequired')->comment('1 = Yes, 2 = No')->nullable();
			$table->date('offHireDate')->nullable();
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
        Schema::dropIfExists('equipment');
    }
}
