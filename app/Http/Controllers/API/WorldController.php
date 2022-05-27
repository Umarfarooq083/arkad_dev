<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class WorldController extends BaseController
{
    public function getCountry(Request $request)
    {

    }

    public function getCityByCountry(Request $request)
    {
        $request->validate([
            'country_id'=>'required'
        ]);
        return $this->sendResponse(City::where('country_id','=',$request->country_id)->get(), 'Success');
    }
}
