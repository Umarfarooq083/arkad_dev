<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationRentalAssine extends Model
{
    use HasFactory;
    protected $table = 'quotation_rental_assigne';
    public function AssigneUserNameQR()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class,'id','user_id' );
    }
}
