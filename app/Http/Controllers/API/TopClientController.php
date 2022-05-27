<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\QuotationRental;
use App\Models\QuotationsRentalEquipment;
use App\Models\Equipment;
use App\Models\RentalQuotation;
use Carbon\Carbon;

class TopClientController extends Controller
{
    public function GetTopClientList(Request $request)
    {
      $client_id = DB::table('quotations_rental')
            ->select(DB::raw('count(*) as client_id, client_id'))
            ->where('status', '>=', 1)
            ->groupBy('client_id')
            ->orderBy('client_id', 'desc')
        ->get();

        $data_total = array();
        foreach($client_id as $client_ids){
            // withCount
             $quotation_renatl_id = QuotationRental::select('id','rental_quotation_id')->where('client_id', '=', $client_ids->client_id)->pluck('id');
            
             $total_order =  count($quotation_renatl_id);
            // return $quotation_renatl_id = QuotationRental::select('id','rental_quotation_id')
            // ->where('client_id', '=', $client_ids->client_id)->pluck('id');
             $quotations_rental_equipment = QuotationsRentalEquipment::select('id','quotations_rental_id','equipment_id')
            ->whereIn('quotations_rental_id',$quotation_renatl_id)
            ->with(['EquipmentMasterData'=>function($query){
                $query->select('id','Equipment');
            }])
            ->get();

             $client_record =  Client::select('id','name')->where('id','=',$client_ids->client_id)
            // ->with('')
            ->first();
            $basic_price = QuotationRental::where('client_id', '=', $client_ids->client_id)
            ->sum('total_price');
            $data['total_order'] = $total_order;
            $data['client_record'] = $client_record;
            $data['basic_price'] = $basic_price;
            $data['quotations_rental_equipment'] = $quotations_rental_equipment;
            array_push($data_total,$data);
        
        }
        return $data_total;
    }

    public function GetDashboardOrderList(Request $request)
    {
        $request->all();
        $total_order = QuotationRental::count(); 
        $total_complete_order = QuotationRental::where('status','=',2)->count(); 
        $total_pending_order = QuotationRental::where('status','=',1)->count();
        
        $total_equipment =  Equipment::count();
        $total_complete_equipment = Equipment::where('equipment_booked','=',2)->count(); 
        $total_remaning_equipment = Equipment::where('equipment_booked','=',1)->count();

        $data['total_order'] = $total_order;
        $data['total_complete_order'] = $total_complete_order;
        $data['total_pending_order'] = $total_pending_order;
        $data['total_equipment'] = $total_equipment;
        $data['total_complete_equipment'] = $total_complete_equipment;
        $data['total_remaning_equipment'] = $total_remaning_equipment;
        return $data;
    }

    public function OrderAndReservation(Request $request)
    {
    //   return  $request->all();
        $start_time = $request->date . ' 01:01:00';
        $end_time  = $request->date . ' 23:59:00';

       return $reservation = RentalQuotation::select('id','client_id','total_equipments')->where('status','=',1)
        ->whereBetween('created_at', [$start_time, $end_time])
        ->with(['ClientData'=>function($queryone){
            $queryone->select('id','name');
        }])
        ->with(['RentalQuotationequipmentList'=>function($queyrtwo){
            $queyrtwo->select('id','equipment_id','quantity','hire_period_start','hire_period_end','quotation_id');
            $queyrtwo->with(['equipment_data'=>function($querythree){
                $querythree->select('id','Equipment');
            }]);
        }])
        ->get();

        $order = QuotationRental::select('id','client_id','rental_quotation_id')->where('status','=',2)
        ->where('created_at','=',$request->date)
        ->with(['ClientName'=>function($queryone){
            $queryone->select('id','name');
        }])
        ->with(['RentalQuotationEquipmentListRecord'=>function($queryab){
            $queryab->select('id','equipment_id','quotation_id','quantity');
        }])
        ->with(['QuotationEquipmentList'=>function($newqr){
            $newqr->select('id','rental_quotations_id','quotations_rental_id','equipment_id','total_hired_period');
                $newqr->with(['EquipmentMasterData'=>function($secquery){
                    $secquery->select('id','Equipment');
                }]);
            }])
        ->get();

        $data['reservation'] = $reservation;
        $data['order'] = $order;
        return $data;
    }

    public function TopEquipmentList(Request $request)
    {
        $top_equipment_list = DB::table('quotations_rental_equipment')
            ->select('quotations_rental_equipment.equipment_id','equipment.Equipment', DB::raw('count(*) as total'))
            ->join('equipment', 'equipment.id', '=', 'quotations_rental_equipment.equipment_id')
            ->orderBy('total', 'desc')
            ->groupBy('quotations_rental_equipment.equipment_id','equipment.Equipment')
        ->get();
        return $top_equipment_list;
    }
}
