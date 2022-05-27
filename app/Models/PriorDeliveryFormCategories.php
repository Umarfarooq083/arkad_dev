<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriorDeliveryFormCategories extends Model
{
    use HasFactory;
    protected $table = 'inspection_cat_list';
    public function InspectionList()
    {
        return $this->hasMany(InspectionLlist::class, 'cat_id', 'id');
    }
    
}
