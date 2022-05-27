<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalQuotationAssigne extends Model
{
    use HasFactory;
    protected $table = 'rental_quotation_assigne';

    public function AssigneUserName()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
