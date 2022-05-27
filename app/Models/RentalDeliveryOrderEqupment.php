<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalDeliveryOrderEqupment extends Model
{
    use HasFactory;

    protected $table = 'rental_delivery_order_equpment';
    
    public function equipment_records()
    {
        return $this->hasOne(Equipment::class,'id','equipment_id');
    }
    public function inspection_records(){
        return $this->hasOne(PriorDeliveryChecked::class, 'equipment_id', 'equipment_id');
    }

    public function DeliveryNoteRecord(){
        return $this->hasOne(DeliveryNoteEquipment::class, 'equipment_id', 'equipment_id');
    }

    public function MCREquipmentAttachment(){
        return $this->hasOne(MCRListEquipment::class, 'equipment_id', 'equipment_id');
    }
}
