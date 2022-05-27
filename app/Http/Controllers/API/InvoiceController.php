<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\RentalDeliveryOrder;
use App\Models\MCRList;
use App\Models\RentalDeliverNote;
use App\Models\ClientInvoice;
use App\Models\QuotationRental;
use App\Models\QuotationsRentalEquipment;
use App\Models\Equipment;
use App\Models\InvoiceRecords;
use Illuminate\Support\Facades\Validator;


class InvoiceController extends BaseController
{
    public function GetClientInvoiceData(Request $request)
    {
        $get_data_form_rdo = RentalDeliverNote::select('id','rental_quotation_id','quotation_rental_id','rental_delivery_order_id','prior_delivery_inspection_id','client_id','paid_unpaid','updated_at','total_basic_price','total_period_base_amount')
        ->with(['ClientNameForInvoice'=> function($qry){
            $qry->select('id','name');
        }])
        ->with(['RenetalQutationTotalEquipment'=> function($query){
            $query->select('id','total_equipments');
        }])
        ->with(['QutationTotalEquipment'=> function($qrry){
            $qrry->select('id','total_price');
        }])
        ->get();

        foreach ($get_data_form_rdo as $get_data_form_rdo_record) {
            $previous_data = ClientInvoice::where('rental_delivery_note_id', '=', $get_data_form_rdo_record->id)->first();
            if ($previous_data) {
                // return $previous_data;
                if ($previous_data->rental_delivery_note_id === $get_data_form_rdo_record->id) {
                    //data is already added so if condation is just for checking
                } else {
                    $prior_delivery_inspection = new ClientInvoice();
                    $prior_delivery_inspection->rental_quotation_id = $get_data_form_rdo_record->rental_quotation_id;
                    $prior_delivery_inspection->quotation_rental_id = $get_data_form_rdo_record->quotation_rental_id;
                    $prior_delivery_inspection->rental_delivery_order_id = $get_data_form_rdo_record->rental_delivery_order_id;
                    $prior_delivery_inspection->prior_delivery_inspection_id = $get_data_form_rdo_record->prior_delivery_inspection_id;
                    $prior_delivery_inspection->rental_delivery_note_id = $get_data_form_rdo_record->id;
                    $prior_delivery_inspection->client_id = $get_data_form_rdo_record->client_id;
                    $prior_delivery_inspection->paid_unpaid = $get_data_form_rdo_record->paid_unpaid;
                    $prior_delivery_inspection->total_equipments = $get_data_form_rdo_record->RenetalQutationTotalEquipment->total_equipments;
                    $prior_delivery_inspection->created_date = $get_data_form_rdo_record->updated_at;
                    $prior_delivery_inspection->total_basic_price = $get_data_form_rdo_record->total_basic_price;
                    $prior_delivery_inspection->total_period_base_amount = $get_data_form_rdo_record->total_period_base_amount;
                    $prior_delivery_inspection->remaning_price = $get_data_form_rdo_record->total_period_base_amount;
                    $prior_delivery_inspection->status = 1;
                    $prior_delivery_inspection->save();
                }
            } else {
                $prior_delivery_inspection = new ClientInvoice();
                $prior_delivery_inspection->rental_quotation_id = $get_data_form_rdo_record->rental_quotation_id;
                $prior_delivery_inspection->quotation_rental_id = $get_data_form_rdo_record->quotation_rental_id;
                $prior_delivery_inspection->rental_delivery_order_id = $get_data_form_rdo_record->rental_delivery_order_id;
                $prior_delivery_inspection->prior_delivery_inspection_id = $get_data_form_rdo_record->prior_delivery_inspection_id;
                $prior_delivery_inspection->rental_delivery_note_id = $get_data_form_rdo_record->id;
                $prior_delivery_inspection->client_id = $get_data_form_rdo_record->client_id;
                $prior_delivery_inspection->paid_unpaid = $get_data_form_rdo_record->paid_unpaid;
                $prior_delivery_inspection->total_equipments = $get_data_form_rdo_record->RenetalQutationTotalEquipment->total_equipments;
                $prior_delivery_inspection->created_date = $get_data_form_rdo_record->updated_at;
                $prior_delivery_inspection->total_basic_price = $get_data_form_rdo_record->total_basic_price;
                $prior_delivery_inspection->total_period_base_amount = $get_data_form_rdo_record->total_period_base_amount;
                $prior_delivery_inspection->remaning_price = $get_data_form_rdo_record->total_period_base_amount;
                $prior_delivery_inspection->status = 1;
                $prior_delivery_inspection->save();
            }
        }
        // $data['rental_quotations_approved_data'] = $rental_quotations_data;
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "client_invoice.*"
        )
            ->join("client_invoice", "client_invoice.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status)) {
            $user_list->orWhere('client_invoice.status', '=', $request->status);
        }
        // search by status
        if (isset($request->start_date) && $request->end_date) {
            $user_list->whereBetween('client_invoice.created_at', [$request->start_date, $request->end_date]);
        }
        return response()->json($user_list->paginate((int)$per_page,), 200);

    }

    public function EditInvoiceData(Request $request)
    {
        $request->all();
        $ldate = date('Y-m-d');
        $date = $ldate;
        $date = strtotime($date);
        $date = strtotime("+10 day", $date);
        $due_date = date('Y-m-d', $date);
        $data['date_issue'] = $ldate;
        $data['due_date'] = $due_date; 
        $rdn_id = $request->rdn_id;
        $data['rdn_edit_id'] = $rdn_id;
        $rental_deliver_note_record = RentalDeliverNote::select('id','client_id','quotation_rental_id','total_basic_price')->where('id','=',$rdn_id)->first();
        $mcr_record = MCRList::select('id','quotation_rental_id')->where('quotation_rental_id','=',$rental_deliver_note_record->quotation_rental_id)->first();
        $total_insurabce_amount = DB::table("equip_maintenance_selected")->where('off_hire_id','=',$mcr_record->id)->sum('price');
        $data['total_insurabce_amount'] = $total_insurabce_amount;
        $client_record = Client::select('id','name','address','phone','email')->where('id','=',$rental_deliver_note_record->client_id)->first();
        $data['client_record'] = $client_record;
        $quotations_rental_equipment_record = QuotationsRentalEquipment::where('quotations_rental_id','=',$rental_deliver_note_record->quotation_rental_id)
        ->with(['EquipmentMasterData'=> function($query){
            $query->select('id','Equipment','EquipmentStatusID','FileName','payment');
            $query->with(['EquipmentStatusRecord'=>function($qry){
                $qry->select('id','name');
            }]);
        }])
        ->get();
        $data['total_period_base_amount'] = ClientInvoice::select('id','total_period_base_amount')->where('rental_delivery_note_id','=',$rdn_id)->first();
        $data['invioce_number'] = $rental_deliver_note_record->quotation_rental_id;
        $invoice_total_price = QuotationRental::select('id','total_price')->where('id','=',$rental_deliver_note_record->quotation_rental_id)->first();
        $data['quotations_rental_equipment_record'] = $quotations_rental_equipment_record;
        $data['invoice_total_price'] = $invoice_total_price;
        return $data;
    }

    public function InvoiceDetail(Request $request)
    {
        $request->all();
        $rdn_id = $request->rdn_id;
        $equipment_id = $request->equipment_id; 
        $data['aomunt_period'] = InvoiceRecords::select('aomunt_period')->where('rdn_id','=',$rdn_id)->where('equipment_id','=',$equipment_id)->first();
        $quotation_rental_idd = RentalDeliverNote::select('id','quotation_rental_id')->where('id','=',$rdn_id)->first();
        $mcr_record = MCRList::select('id','quotation_rental_id')->where('quotation_rental_id','=',$quotation_rental_idd->quotation_rental_id)->first();
        $total_insurabce_amount = DB::table("equip_maintenance_selected")->where('off_hire_id','=',$mcr_record->id)->where('equipment_id','=',$equipment_id)->sum('price');
        $data['total_insurabce_amount'] = $total_insurabce_amount;
        $single_price = QuotationsRentalEquipment::select('id','price','duration_rate')->where('quotations_rental_id','=',$quotation_rental_idd->quotation_rental_id)
        ->where('equipment_id','=',$request->equipment_id)
        ->first();

        $equipment_record_single_invoice = Equipment::select('id','Equipment','GroupNumberID')
        ->with(['EquipmentGroupNumberRecord'=> function($query){
            $query->select('id','GroupNumber','Description');
        }])
        ->where('id','=',$equipment_id)->first();
        $data['rdn_id'] = $rdn_id;
        $data['equipment_id'] = $equipment_id;
        $data['single_price'] = $single_price;
        $data['equipment_record_single_invoice'] = $equipment_record_single_invoice;
        return $data;

    }

    public function UpdateInvoice(Request $request)
    {
        InvoiceRecords::where('rdn_id','=',$request->rdn_id)->where('equipment_id','=',$request->equipment_id)->delete();
        $invoice_records = new InvoiceRecords();
        $invoice_records->rdn_id = $request->rdn_id;
        $invoice_records->equipment_id = $request->equipment_id;
        $invoice_records->agreed_rate = $request->agreed_rate;
        $invoice_records->aomunt_period = $request->aomunt_period;
        $invoice_records->save();
        return $this->sendResponse([], 'Record Updated');
    }

    public function ChangePaidStatus(Request $request)
    {
        $rules = array(
            'rdn_id'=>'required | numeric',
            'paid_unpaid'=>'required | numeric',
            'paid_by_client'=>'required | numeric',
        );
        $validation = Validator::make($request->all(),$rules);
        if(!empty($validation->errors()->first()))
        {
            return $this->sendError($validation->errors()->first());
        }
   
        $change_to_paid = RentalDeliverNote::select('id','paid_unpaid','quotation_rental_id')->where('id','=',$request->rdn_id)->first();
        $rental_delivery_order_record = RentalDeliveryOrder::where('quotation_rental_id','=',$change_to_paid->quotation_rental_id)->first();
        $rental_delivery_order_record->paid_unpaid = $request->paid_unpaid;
        $rental_delivery_order_record->save();
        // paid_unpaid
        $change_to_paid->paid_unpaid = $request->paid_unpaid;
        $change_to_paid->save();
        $client_change_to_paid = ClientInvoice::where('rental_delivery_note_id','=',$request->rdn_id)->first();
        $client_change_to_paid->paid_unpaid = $request->paid_unpaid;
        $new_remaning_price = $client_change_to_paid->remaning_price - $request->paid_by_client;
        $client_change_to_paid->remaning_price = $new_remaning_price; 
        $client_change_to_paid->save();
        $quotations_rental_equipment_update_master_status = QuotationsRentalEquipment::select('equipment_id')->where('quotations_rental_id','=',$change_to_paid->quotation_rental_id)->get()->pluck('equipment_id');
        $update_record = Equipment::select('id','final_status')->whereIn('id',$quotations_rental_equipment_update_master_status)->get();
        foreach($update_record as $changed){
            $changed->payment = $request->paid_unpaid; 
            $changed->save();
        }
        return $this->sendResponse([], 'Status Update To Paid ');

    }

    public function ViewInvoiceStepOne(Request $request)
    {
        $request->all();
        $ldate = date('Y-m-d');
        $date = $ldate;
        $date = strtotime($date);
        $date = strtotime("+10 day", $date);
        $due_date = date('Y-m-d', $date);
        $data['date_issue'] = $ldate;
        $data['due_date'] = $due_date; 
        $rdn_id = $request->rdn_id;
        $data['rdn_edit_id'] = $rdn_id;
        $rental_deliver_note_record = RentalDeliverNote::select('id','client_id','quotation_rental_id','total_basic_price')->where('id','=',$rdn_id)->first();
        $client_record = Client::select('id','name','address','phone','email')->where('id','=',$rental_deliver_note_record->client_id)->first();
        $data['client_record'] = $client_record;
        $quotations_rental_equipment_record = QuotationsRentalEquipment::where('quotations_rental_id','=',$rental_deliver_note_record->quotation_rental_id)
        ->with(['EquipmentMasterData'=> function($query){
            $query->select('id','Equipment','EquipmentStatusID','FileName','payment');
            $query->with(['EquipmentStatusRecord'=>function($qry){
                $qry->select('id','name');
            }]);
        }])
        ->get();
        $data['invioce_number'] = $rental_deliver_note_record->quotation_rental_id;
        $invoice_total_price = QuotationRental::select('id','total_price')->where('id','=',$rental_deliver_note_record->quotation_rental_id)->first();
        $data['quotations_rental_equipment_record'] = $quotations_rental_equipment_record;
        $data['invoice_total_price'] = $invoice_total_price;
        return $data;
    }


    public function ViewInvoiceStepTwo(Request $request)
    {
        $request->all();
        $rdn_id = $request->rdn_id;
        $equipment_id = $request->equipment_id; 
        $quotation_rental_idd = RentalDeliverNote::select('id','quotation_rental_id')->where('id','=',$rdn_id)->first();
        $single_price = QuotationsRentalEquipment::select('id','price','duration_rate')->where('quotations_rental_id','=',$quotation_rental_idd->quotation_rental_id)
        ->where('equipment_id','=',$request->equipment_id)
        ->first();

        $equipment_record_single_invoice = Equipment::select('id','Equipment','GroupNumberID')
        ->with(['EquipmentGroupNumberRecord'=> function($query){
            $query->select('id','GroupNumber','Description');
        }])
        ->where('id','=',$equipment_id)->first();
        $data['rdn_id'] = $rdn_id;
        $data['equipment_id'] = $equipment_id;
        $data['single_price'] = $single_price;
        $data['equipment_record_single_invoice'] = $equipment_record_single_invoice;
        return $data;

    }
}
