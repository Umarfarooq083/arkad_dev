<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationRental extends Model
{
    use HasFactory;
    protected $table = 'quotations_rental';

    public function ClientName()
    {
        return $this->hasOne(Client::class,'id','client_id');
    }

    public function QuotationEquipmentList()
    {
        return $this->hasMany(QuotationsRentalEquipment::class,'quotations_rental_id','id');
    }

    public function RentalQuotationEquipmentListRecord()
    {
        return $this->hasMany(RentalQuotationEquipment::class,'quotation_id','rental_quotation_id');
    }
}
