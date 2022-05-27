<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriorDeliveryChecked extends Model
{
    use HasFactory;
    protected $table = 'prior_delivery_checked';

    public function check_list()
    {
        return $this->hasOne(Equipment::class, 'id', 'equipment_id');
    }

}
