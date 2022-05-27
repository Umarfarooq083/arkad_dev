<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    public function rentalQuotations()
    {
        return $this->belongsToMany(RentalQuotation::class, 'equipment_quotations', 'quotation_id', 'equipment_id');
    }
    
    public function MachineMakerRecord(){
        return $this->hasOne(MachineMaker::class, 'id', 'MachineMakerID','equipment_id');
    }

    public function equip_maintenance_equipments_record(){
        return $this->hasOne(EquipMaintenanceEquipments::class, );
    }
    public function EquipmentStatusRecord(){
        return $this->hasOne(EquipmentStatus::class, 'id','EquipmentStatusID');
    }

    public function EquipmentGroupNumberRecord(){
        return $this->hasOne(GrouNumber::class, 'id','GroupNumberID');
    }

    public function EquipmentCategoryList(){
        return $this->hasOne(EquipmentCategory::class, 'id','EquipmentCategoryID');
    }

    public function EquipmentHireType(){
        return $this->hasOne(HireType::class, 'id','HireTypeID');
    }

    public function EquipmentMeterType(){
        return $this->hasOne(MeterType::class, 'id','MeterTypeID');
    }
    
    public function EquipmentFuelType(){
        return $this->hasOne(FuelType::class, 'id','FuelTypeID');
    }
    
}
