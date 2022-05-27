<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipMaintenanceAssignees extends Model
{
    use HasFactory;
    protected $table = 'equip_maintenance_assignees';

    public function AssigneUserNameEQPM()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }


}
