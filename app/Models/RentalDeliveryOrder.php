<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalDeliveryOrder extends Model
{
    use HasFactory;
    protected $table = 'rental_delivery_order';

    public function GetPainOrUnpaidStatus()
    {
        return $this->hasMany(ClientInvoice::class, 'quotation_rental_id', 'quotation_rental_id');
    }
    
    public function ClientRecord()
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }
    
    
}
