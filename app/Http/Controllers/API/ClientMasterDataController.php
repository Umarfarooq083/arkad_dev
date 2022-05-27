<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\MCRList;
use App\Models\Equipment;
use App\Models\ClientMasterData;
use App\Models\HireType;
use App\Models\RentalQuotation;
use App\Models\EquipmentCategory;
use App\Models\Orders;
use App\Models\QuotationsRentalEquipment;
use App\Models\QuotationRental;
use Illuminate\Support\Facades\DB;

class ClientMasterDataController extends Controller
{
    public function GetClientMsaterData(Request $request)
    {
         $request->all();
         $client_record = Client::select('id','name','created_at')->withSum('TotalEquipment', 'total_equipments')
         ->withSum('QRTotalPrice','total_price')->get();
        ClientMasterData::truncate();
         foreach($client_record as $list){
            $client_master_data = new ClientMasterData();
            $client_master_data->client_id = $list->id; 
            $client_master_data->name = $list->name;
            $client_master_data->client_created_date = $list->created_at;
            $client_master_data->total_equipment_sum = $list->total_equipment_sum_total_equipments;
            $client_master_data->q_r_total_price_sum = $list->q_r_total_price_sum_total_price;
            $client_master_data->save();
         }

         $per_page = isset($request->per_page) ? $request->per_page : 20;
         $user_list = Client::select(
             "clients.id",
             "clients.name",
             "client_master_data.*"
         )
             ->join("client_master_data", "client_master_data.client_id", "=", "clients.id");
         if (isset($request->s) && !empty($request->s)) {
             $user_list->orWhere('name', 'like', '%' . $request->s . '%');
         }
         if ((isset($request->start_date) && isset($request->end_date)) && (!empty($request->start_date) && !empty($request->end_date))) {
             $user_list->whereBetween('clients.created_at', [$request->start_date, $request->end_date]);
         }
         return response()->json($user_list->paginate($per_page));
    }

    public function EditClientMaster(Request $request)
    {
        $request->all();
        $data['client_id'] = $request->client_id;
        $data['client_record'] = Client::where('id','=',$request->client_id)->first();
        return $data;
    }

    public function GetClientMasterEquipment(Request $request)
    {
        $data['client_id'] = $request->client_id;
        $quotation_rental = QuotationRental::where('client_id','=',$request->client_id)->pluck('rental_quotation_id');
        $data['quotations_rental_equipment_record'] = QuotationsRentalEquipment::select('id','quotations_rental_id','rental_quotations_id','equipment_id','price','total_period_base_amount','created_at')->whereIn('rental_quotations_id',$quotation_rental)
        ->with(['EquipmentMasterData'=>function($query){
            $query->select('id','payment');
        }])
        ->get();
        return $data;
    }

    public function GetClientOrderList(Request $request)
    {
        $request->all();
        $data['client_id'] = $request->client_id;
        $data['quotations_rental_id'] = $request->quotations_rental_id;
        $mcr_record = MCRList::select('id','quotation_rental_id')->where('quotation_rental_id','=',$request->quotations_rental_id)->first();
        if($mcr_record){
          $total_insurabce_amount = DB::table("equip_maintenance_selected")->where('off_hire_id','=',$mcr_record->id)->where('equipment_id','=',$request->equipment_id)->sum('price');
          $data['total_insurabce_amount'] = $total_insurabce_amount;
        }else{
          $data['total_insurabce_amount'] = 0;
        }
        $data['client_data'] = Client::select('id','name','phone','email','address')->where('id','=',$request->client_id)->first();
        $data['quotations_rental_equipment_record'] = QuotationsRentalEquipment::select('id','quotations_rental_id','rental_quotations_id','equipment_id','price','total_period_base_amount','created_at')->where('rental_quotations_id','=',$request->quotations_rental_id)
        ->with(['EquipmentMasterData'=>function($query){
            $query->select('id','Equipment','final_status','payment');
        }])
        ->get();
        return $data;
    }

    public function ClientMasterEquipmentDetail(Request $request)
    {
        $request->all();
        $data['total_price'] = QuotationRental::select('id','total_price')->where('id','=',$request->quotations_rental_id)->first();
        $mcr_record = MCRList::select('id','quotation_rental_id')->where('quotation_rental_id','=',$request->quotations_rental_id)->first();
        if($mcr_record){
            $total_insurabce_amount = DB::table("equip_maintenance_selected")->where('off_hire_id','=',$mcr_record->id)
            ->where('equipment_id','=',$request->equipment_id)->sum('price');
            $data['total_insurabce_amount'] = $total_insurabce_amount;
        }else{
            $data['total_insurabce_amount'] = 0;
        }
        $data['Equipment_records'] = Equipment::select('id','Equipment','GroupNumberID')
        ->with(['EquipmentGroupNumberRecord'=> function($query){
            $query->select('id','GroupNumber','Description');
        }])
        ->where('id','=',$request->equipment_id)->first();
        return $data;
    }

    public function EquipmentMasterDataListing(Request $request)
    {
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Equipment::select(
            "equipment.id",
            "equipment.Equipment",
            "equipment.final_status",
            "equipment.EquipmentStatusID",
            "equipment.PlantNumber",
            
        );
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('Equipment', 'like', '%' . $request->s . '%');
        }
        // // search by status
        if (isset($request->status) && !empty(isset($request->status))) {
            $user_list->orWhere('equipment.final_status', '=', $request->status);
        }
        return response()->json($user_list->paginate($per_page));
    }

    public function EditEquipmentMasterData(Request $request)
    {
        $request->all();
        $data['equipment_master_data'] = Equipment::select('id','ReceivedDate','PurchaseOrderID','PurchaseOrderSupplierID','GroupNumberID','PlantNumber','PlateNumber','EquipmentCategoryID','Equipment',
        'MachineMakerID','SupplierPlantNumber','MachineModel','Capacity','SerialNumber','YOM','EngineNumber','HourlyRate','DailyRate','WeeklyRate','MonthlyRate','CurrencyID','NormalHour',
        'BreakHour','OwnershipTypeID','HireTypeID','EquipmentStatusID','MeterTypeID','FuelTypeID','FuelCapacity')
        ->with(['EquipmentCategoryList'=>function($query1){
            $query1->select('id','equipmentCategory');
        }])
        ->with(['MachineMakerRecord'=>function($query2){
            $query2->select('id','machineMaker');
        }])
        ->with(['EquipmentHireType'=>function($query3){
            $query3->select('id','type');
        }])
        ->with(['EquipmentStatusRecord'=>function($query4){
            $query4->select('id','name');
        }])
        ->with(['EquipmentMeterType'=>function($query5){
            $query5->select('id','metertype');
        }])
        ->with(['EquipmentFuelType'=>function($query6){
            $query6->select('id','fuletype');
        }])
        
        ->where('id','=',$request->equipment_id)
        ->first();
        return $data;
    }

}
