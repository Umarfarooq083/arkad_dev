<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// InspectionLlist
// PriorDeliveryCondationsList
class InspectionLlist extends Model
{
    use HasFactory;
    protected $table = 'inspection_list';
    // protected $hidden = ['timestamps'];
   
    public function selected()
    {
        // return $this->hasOne(PriorDeliveryChecked::class, 'inspection_list_id','id');
        return $this->hasOne(PriorDeliveryChecked::class, 'inspection_list_id','id','category_id','cat_id');
    }

    public function MCRSelected()
    {
        // return $this->hasOne(PriorDeliveryChecked::class, 'inspection_list_id','id');
        return $this->hasOne(MCRListChecked::class, 'inspection_list_id','id','category_id','cat_id');
    }
}
