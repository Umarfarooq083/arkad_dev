<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalDeliverNote extends Model
{
    use HasFactory;
    protected $table = 'rental_delivery_note';

    public function RenetalQutationTotalEquipment()
    {
        return $this->hasOne(RentalQuotation::class, 'id', 'rental_quotation_id');
    }

    public function QutationTotalEquipment()
    {
        return $this->hasOne(QuotationRental::class, 'id', 'quotation_rental_id');
    }

    public function ClientNameForInvoice()
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }
}
