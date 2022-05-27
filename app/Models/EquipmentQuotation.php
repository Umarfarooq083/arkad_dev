<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentQuotation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'equipment_id',
        'quotation_id',
    ];

    protected $table = 'equipment_quotations';

    public function equipmentQuotations()
    {
        return $this->belongsTo(Equipment::class);
    }
}
