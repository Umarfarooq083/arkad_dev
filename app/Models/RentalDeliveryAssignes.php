<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalDeliveryAssignes extends Model
{
    use HasFactory;
    protected $table = 'rental_delivery_assignes';

    public function AssigneUserNameRDO()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
