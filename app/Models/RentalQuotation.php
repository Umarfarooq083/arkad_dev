<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalQuotation extends Model
{
    use HasFactory;

    protected $table = 'rental_quotations';

    public function equipments()
    {
        return $this->belongsToMany(Equipment::class, 'equipment_quotations', 'equipment_id', 'quotation_id');
    }

    public function user_data()
    {
        return $this->hasOne(User::class, );
    }

    public function ClientData()
    {
        return $this->hasOne(Client::class,'id','client_id' );
    }

    public function RentalQuotationequipmentList()
    {
        return $this->hasMany(RentalQuotationEquipment::class,'quotation_id','id' );
    }

    

}
