<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    public function rqr(){
        return $this->hasMany(RentalQuotation::class, 'client_id','id');
    }

    public function TotalEquipment()
    {
        return $this->hasMany(RentalQuotation::class, 'client_id','id');
    }
    

    public function QRTotalPrice()
    {
        return $this->hasMany(QuotationRental::class, 'client_id','id');
    }

    

}
