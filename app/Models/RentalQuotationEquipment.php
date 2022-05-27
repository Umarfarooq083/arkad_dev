<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalQuotationEquipment extends Model
{
    use HasFactory;
    protected $table = 'rental_quotations_equipment';

    public function equipment_data()
    {
        return $this->hasOne(Equipment::class,'id','equipment_id');
    }

    public function QuotationsRentalRquipmentPrice(){
        return $this->hasOne(QuotationsRentalEquipment::class,'equipment_id','equipment_id');  
    }

    public function quotations_rental_equipment_data()
    {
        return $this->hasOne(QuotationsRentalEquipment::class,'equipment_id','equipment_id');
    }

    public function quotations_rental_equipment_price()
    {
        return $this->hasOne(QuotationsRentalEquipment::class,'equipment_id','equipment_id');
    }

    public function rental_delivery_equpment_data()
    {
        return $this->hasOne(RentalDeliveryOrderEqupment::class,'equipment_id','equipment_id');
    }
    
    public function off_hire_equpment_data()
    {
        return $this->hasOne(OffHireEquipment::class,'equipment_id','equipment_id');
    }
    
    // RentalDeliveryOrderEqupment
}
