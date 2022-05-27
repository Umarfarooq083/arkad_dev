<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function GetRqrFormEquipmentList(Request $request)
    {
        return Equipment::select('id','Equipment')
            ->where('IsDeleted','=',0)
            ->get();
    }
}
