<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\EquipMaintenanceSelected;
use App\Models\Equipment;
use App\Models\MCRList;
use App\Models\QuotationRental;
use App\Models\QuotationsRentalEquipment;

class OrderController extends Controller
{
    public function GetOrderData(Request $request){
        // return $request->all();
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "orders.*"
        )
            ->join("orders", "orders.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status)) {
            $user_list->orWhere('orders.status', '=', $request->status);
        }
        // search by status
        if (isset($request->start_date) && $request->end_date) {
            $user_list->whereBetween('orders.created_at', [$request->start_date, $request->end_date]);
        }
        return response()->json($user_list->paginate((int)$per_page,), 200);


    }

    public function ViewOrder(Request $request)
    {
        $request->all();
        $data['quotation_rental_id'] = $request->quotation_rental_id;
         $mcr_record = MCRList::select('id','quotation_rental_id')->where('quotation_rental_id','=',$request->quotation_rental_id)->first();
         if($mcr_record){
            $total_insurabce_amount = DB::table("equip_maintenance_selected")->where('off_hire_id','=',$mcr_record->id)->sum('price');
            $data['total_insurabce_amount'] = $total_insurabce_amount;
        }else{
            $data['total_insurabce_amount'] = 0;
        }
        
        $quotation_rental_data = QuotationRental::where('id','=',$request->quotation_rental_id)->first();
        $quotation_rental_data->client_id;
        
        $data['client_record'] = Client::where('id','=',$quotation_rental_data->client_id)->first();
        $data['total_price'] = $quotation_rental_data->total_price;
        $data['equipment_records'] = QuotationsRentalEquipment::where('quotations_rental_id','=',$quotation_rental_data->id)
        ->with(['EquipmentMasterData'=>function($query){
            $query->select('id','Equipment','EquipmentStatusID','FileName','payment','FileData');
            $query->with(['EquipmentStatusRecord'=>function($qry){
                $qry->select('id','name');
            }]);
        }])
        ->get();
        return $data;
    }

    public function ViewOrderDetail(Request $request)
    {
         $request->all();
        $mcr_record = MCRList::select('id','quotation_rental_id')->where('quotation_rental_id','=',$request->quotation_rental_id)->first();
      if($mcr_record){
        $total_insurabce_amount = DB::table("equip_maintenance_selected")->where('off_hire_id','=',$mcr_record->id)->where('equipment_id','=',$request->equipment_id)->sum('price');
        $data['total_insurabce_amount'] = $total_insurabce_amount;
      }else{
        $data['total_insurabce_amount'] = 0;
      }
        
        $data['current_month'] = date('F');
        $data['quotations_rental_equipment_record'] = QuotationsRentalEquipment::select('id','price','duration_rate')->where('quotations_rental_id','=',$request->quotation_rental_id)
        ->where('equipment_id','=',$request->equipment_id)
        ->first();
        $data['Equipment_records'] = Equipment::select('id','Equipment','GroupNumberID')
        ->with(['EquipmentGroupNumberRecord'=> function($query){
            $query->select('id','GroupNumber','Description');
        }])
        ->where('id','=',$request->equipment_id)->first();
        return $data;
    }
}
