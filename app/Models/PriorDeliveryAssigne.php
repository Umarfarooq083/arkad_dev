<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriorDeliveryAssigne extends Model
{
    use HasFactory;
    protected $table = 'prior_delivery_assigne';

    public function AssigneUserNamePDI()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

}
