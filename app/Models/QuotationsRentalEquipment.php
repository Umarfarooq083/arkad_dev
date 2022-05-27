<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationsRentalEquipment extends Model
{
    use HasFactory;
    protected $table = 'quotations_rental_equipment';

    public function PriorDeliveCheckEquipment()
    {
        return $this->hasMany(PriorDeliveryChecked::class, 'equipment_id', 'equipment_id');
    }

    public function MCRCheckedEquipment()
    {
        return $this->hasMany(MCRListChecked::class, 'equipment_id', 'equipment_id');
    }

    public function EquipmentMasterData()
    {
        return $this->hasMany(Equipment::class, 'id', 'equipment_id');
    }


    public function RenatlDeliverNoteCheckEquipment()
    {
        return $this->hasMany(DeliveryNoteEquipment::class, 'equipment_id', 'equipment_id');
    }


    public function EquipmantanceCheckEquipment()
    {
        return $this->hasMany(EquipMaintenanceEquipments::class, 'equipment_id', 'equipment_id');
    }

    
}
