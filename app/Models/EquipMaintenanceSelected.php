<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipMaintenanceSelected extends Model
{
    use HasFactory;
    protected $table = 'equip_maintenance_selected';
    
    
    public function equipment_masterdate_list_name()
    {
        return $this->hasOne(InspectionLlist::class,'id','inspection_list_id');
    }

    public function equipment_masterdate_list()
    {
        return $this->hasOne(Equipment::class,'id','equipment_id');
    }
}
