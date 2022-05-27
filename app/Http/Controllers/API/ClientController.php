<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\AssigneeEmails;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\RentalQuotation;
use App\Models\QuotationRental;
use App\Models\RentalQuotationEquipment;
use App\Models\RentalQuotationAssigne;
use App\Models\RentalDeliveryAssignes;
use App\Models\QuotationRentalAssine;
use App\Models\Operator;
use App\Models\Transport;
use App\Models\AramcoTuv;
use App\Models\InspectionLlist;
use App\Models\OffHireList;
use App\Models\MCRList;
use App\Models\MCRListEquipment;
use App\Models\OffHireAssignes;
use App\Models\MCRListChecked;
use App\Models\MCRListAssignees;
use App\Models\EquipMaintenance;
use App\Models\EquipMaintenanceSelected;
use App\Models\EquipMaintenanceAssignees;
use App\Models\EquipMaintenanceEquipments;
use App\Models\OffHireEquipment;
use App\Models\Orders;
use App\Models\DeliveryNoteAssignes;
use App\Models\RentalDeliveryOrderEqupment;
use App\Models\PriorDeliveryChecked;
use App\Models\PriorDeliveryInspection;
use App\Models\DeliveryNoteEquipment;
use App\Models\MachineMakerRecord;
use App\Models\RentalDeliverNote;
use App\Models\PriorDeliveryAssigne;
use App\Models\PriorDeliveryFormCategories;
use App\Models\RentalDeliveryOrder;
use App\Models\QuotationsRentalEquipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClientController extends BaseController
{

    public function index()
    {
        $records = RentalQuotation::where('client_id', Auth::user()->id)->orderBy('id', 'DESC')->get();
    }

    public function AddClient(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required|unique:clients',
            'fax' => 'required',
            'landline_number' => 'required',
            'bussines_type' => 'required',
            'address' => 'required',
            'contact_person' => 'required',
            'contact_person_email' => 'required',
            'contact_person_phone' => 'required',
            'cr_number' => 'required',
            'vat_number' => 'required',
            'company_name' => 'required',

        ]);

        $new_client = new Client();
        $new_client->name = $request->name;
        $new_client->phone = $request->phone;
        $new_client->email = $request->email;
        $new_client->landline_number = $request->landline_number;
        $new_client->fax = $request->fax;
        $new_client->status = 1;
        $new_client->bussines_type = $request->bussines_type;
        $new_client->address = $request->address;
        $new_client->contact_person = $request->contact_person;
        $new_client->contact_person_email = $request->contact_person_email;
        $new_client->contact_person_phone = $request->contact_person_phone;
        $new_client->cr_number = $request->cr_number;
        $new_client->company_name = $request->company_name;
        $new_client->addtional_id = $request->addtional_id;
        $new_client->vat_number = $request->vat_number;
        $new_client->save();
        return $this->sendResponse($new_client, 'Client added successfully');
        // return $request->name;
    }

    public function Listing(Request $request)
    {
        $request_data = $request->all();
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::with('rqr');
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        if (isset($request->status)) {
            $user_list->where('status', '=', $request->status);
        }
        $user_list->orderBy('id', 'desc');
        return response()->json($user_list->paginate((int)$per_page,), 200);
    }

    public function GetEditRQRData(Request $request)
    {
        // quotation id
        $id = $request->id;
        $rental_quotation_id = RentalQuotation::where('id', '=', $id)->first();
        $rqr_id = $rental_quotation_id->id;
        $data['operators'] = Operator::select('id','name')->get();
        $data['aramco_tuv'] = AramcoTuv::select('id','name')->get();
        $data['edit_id'] = $rental_quotation_id->id;
        $data['rental_quotation_id'] = $rental_quotation_id;
      //client id and client record
        $data['all_clients'] = Client::all();
        $data['client'] = Client::where('id', '=', $rental_quotation_id->client_id)->first();
        //rental quotation equipment complete data
        $data['all_equipments'] = Equipment::select('Equipment')->where('final_status','=',1)->get();
        $data['rentalquotationequipment'] = RentalQuotationEquipment::where('quotation_id', $id)
        ->with(['equipment_data' => function ($query) {
            $query->select('id', 'Equipment');
        }])
        ->get();
        $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
        ->with(['rental_qutation_assignees' => function ($query) use ($rqr_id) {
            $query->select('user_id')->where('quotation_id','=', $rqr_id);
        }])
        ->get();
        return $this->sendResponse($data, 'Success');
    }

    public function UpdateRQRData(Request $request)
    {
        $id = $request->id;
        $client = $request->client;
        $rental_quotation_id = RentalQuotation::where('id', '=', $id)->first();
        $rental_quotation_id->position = $request->position;
        $rental_quotation_id->client_rep = $request->client_rep;
        $rental_quotation_id->sp_name = $request->sp_name;
        $rental_quotation_id->save();
        $client_data = Client::where('id', '=', $rental_quotation_id->client_id)->first();
        $client_data->name = $client['name'];
        $client_data->phone = $client['phone'];
        $client_data->email = $client['email'];
        $client_data->fax = $client['fax'];
        $client_data->save();
        $rentalquotationequipment = RentalQuotationEquipment::where('quotation_id', '=', $id)->get();
        foreach ($rentalquotationequipment as $k => $value) {
            $value->delete();
        }

        $equpment_updated = $request->equipment;
        foreach ($equpment_updated as $key => $value) {
            $rentanl_quotation_update = new RentalQuotationEquipment();
            $rentanl_quotation_update->client_id = $rental_quotation_id->client_id;
            $rentanl_quotation_update->equipment_id = $value['id'];
            $rentanl_quotation_update->quotation_id = $rental_quotation_id->id;
            $rentanl_quotation_update->sp_position = $value['sp_position'];
            // $rentanl_quotation_update->sp_name = $value['sp_name'];
            $rentanl_quotation_update->operator = $value['operator'];
            $rentanl_quotation_update->quantity = $value['quantity'];
            $rentanl_quotation_update->hire_period_start = $value['hire_period_start'];
            $rentanl_quotation_update->hire_period_end = $value['hire_period_end'];
            $rentanl_quotation_update->estimated_start_hire_date = $value['estimated_start_hire_date'];
            $rentanl_quotation_update->ARAMCO_TUV = $value['ARAMCO_TUV'];
            $rentanl_quotation_update->site_location = $value['site_location'];
            $rentanl_quotation_update->detail = $value['detail'];
            $rentanl_quotation_update->description = $value['description'];
            $rentanl_quotation_update->created_by = Auth::user()->id;
            $rentanl_quotation_update->updated_by = Auth::user()->id;
            $rentanl_quotation_update->save();
        }
        
        RentalQuotationAssigne::where('quotation_id', '=', $id)->delete();
        $assignee = $request->assignee;
        foreach ($assignee as $assignes) {
            $rental_quotation_assigne = new RentalQuotationAssigne();
            $rental_quotation_assigne->quotation_id = $id;
            $rental_quotation_assigne->user_id = $assignes;
            $rental_quotation_assigne->status = 1;
            $rental_quotation_assigne->save();
        }
        return $this->sendResponse([], 'Success');
    }

    public function Search(Request $request)
    {
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "rental_quotations.*"
        )
            ->join("rental_quotations", "rental_quotations.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status) && !empty(isset($request->status))) {
            $user_list->orWhere('rental_quotations.status', '=', $request->status);
        }
        // search by start date and end date
        if ((isset($request->start_date) && isset($request->end_date)) && (!empty($request->start_date) && !empty($request->end_date))) {
            $user_list->whereBetween('rental_quotations.created_at', [$request->start_date, $request->end_date]);
        }
        // return $this->sendResponse($user_list->get(), 'Success');
        return response()->json($user_list->paginate($per_page));

    }

    public function SubmitRQR(Request $request)
    {
        // return ($request->all());
        $request->validate([
            'equipments.*' => 'required',
            'client' => 'required',
        ]);
        foreach ($request->equipment as $k => $v) {
        }
        $equipments = $request->equipment;
        $client = $request->client;
        $assignee = $request->assignee;
        $new_client = new Client();
        $new_client->name = $client['name'];
        $new_client->phone = $client['phone'];
        $new_client->email = $client['email'];
        $new_client->fax = $client['fax'];
        $quotation = new RentalQuotation();
        $quotation->client_id = $client['id'];
        $quotation->user_id = Auth::user()->id;
        $quotation->total_equipments = count($equipments);
        $quotation->position = $request->position;
        $quotation->client_rep = $request->client_rep;
        $quotation->sp_name = $request->sp_name;

        $quotation->status = 1;
        $quotation->amount = 0.00;
        $quotation->save();
        $quotation_save = $quotation;
        $equipments_insert_array = array();

        foreach ($equipments as $k => $v) {
            array_push($equipments_insert_array, array(
                'client_id' => $client['id'],
                'equipment_id' => $v['id'],
                'quotation_id' => $quotation_save->id,
                'sp_position' => $v['sp_position'],
                // 'sp_name' => $v['sp_name'],
                'hire_period_start' => $v['hire_period_start'],
                'hire_period_end' => $v['hire_period_end'],
                'operator' => $v['operator'],
                'quantity' => $v['quantity'],
                'estimated_start_hire_date' => $v['estimated_start_hire_date'],
                'ARAMCO_TUV' => $v['ARAMCO_TUV'],
                'site_location' => $v['site_location'],
                'detail' => $v['detail'],
                'description' => $v['description'],
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ));
        }
        RentalQuotationEquipment::insert($equipments_insert_array);
        // add assignes (user_id) in rental quotation assigne table
        foreach ($assignee as $assignes) {
            $rental_quotation_assigne = new RentalQuotationAssigne();
            $rental_quotation_assigne->quotation_id = $quotation->id;
            $rental_quotation_assigne->user_id = $assignes;
            $rental_quotation_assigne->status = 1;
            $rental_quotation_assigne->save();
        }

        return $this->sendResponse(['created'], 'RQR Created successfully');
    }

    public function ViewRQRAssigne(Request $request)
    {
        $id = $request->id;
        $rental_quotation_assigne = RentalQuotationAssigne::where('quotation_id', $id)
        ->with(['AssigneUserName' => function ($query) {
            $query->select('id', 'user_name');
        }])
        ->get();
        return $rental_quotation_assigne;
    }

    public function ChangeRQRAssigneStatus(Request $request)
    {
        $reject_note = $request->reject_note;
        $quotation_id = $request->id;
        $status = $request->status;
        $user_id = Auth::User()->id;
        $rental_quotation_assigne = RentalQuotationAssigne::where('quotation_id', '=', $quotation_id)->where('user_id', '=', $user_id)->first();
        if ($rental_quotation_assigne) {
            $rental_quotation_assigne->status = $status;
            $rental_quotation_assigne->save();
            $statuses =  RentalQuotationAssigne::select('status')->where('quotation_id', '=', $quotation_id)->get()->toArray();
            $statuses_ids = array_column($statuses, 'status');
            $rqr_status_approved = in_array(1, $statuses_ids) ? 1 : 2;
            $rqr_status_cancle = in_array(3, $statuses_ids) ? 3 : $rqr_status_approved;
            if ($rqr_status_approved === 2) {
                $update_status = RentalQuotation::where('id', '=', $quotation_id)->first();
                $update_status->status = 2;
                $update_status->save();
            } else {
                $update_status = RentalQuotation::where('id', '=', $quotation_id)->first();
                $update_status->status = 1;
                $update_status->save();
            }
            if ($rqr_status_cancle === 3) {
                if ($status == 3) {
                    if ($reject_note) {
                        $rental_quotation_assigne->reject_note = $reject_note;
                        $rental_quotation_assigne->save();
                    }
                }
                $update_status = RentalQuotation::where('id', '=', $quotation_id)->first();
                $update_status->status = 3;
                $update_status->save();
            }
            return $this->sendResponse([], 'Status Updated');
        } else {
            return $this->sendError([], 'You Are Not Authorized to Change Status');
        }
    }

    public function GetRqrFormData()
    {
        $data = array();
        $data['operators'] = Operator::select('id','name')->get();
        $data['aramco_tuv'] = AramcoTuv::select('id','name')->get();
        $data['equipments'] = Equipment::select('id', 'Equipment')
        ->where('final_status', '=', 1)
        ->get();
        $data['users'] = User::select('id', 'user_name')->where('status', '1', 0)->get();
        $data['clients'] = Client::select('id', 'name')->get();

        return $this->sendResponse($data, 'Success');
    }

    public function GetSingleclient(Request $request)
    {
        $request->validate([
            'client_id' => 'required'
        ]);
        $request_data = $request->all();
        $data = Client::where('id', '=', $request_data['client_id'])->get();
        return $this->sendResponse($data, 'Success');
    }

    public function ViewRQR(Request $request)
    {
        $id = $request->id;
        // rental quoation id
        $rental_quatation_data = RentalQuotation::select('client_id')->where('id', '=', $id)->first();
        //client data
        $data['client_data'] = Client::where('id', '=', $rental_quatation_data->client_id)->first();
        //rental quataation data with equpment table data
        $data['rentalquotationequipment_data'] = RentalQuotationEquipment::where('quotation_id', '=', $id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment');
            }])->get();
        return $data;
    }

    public function EditQRData(Request $request)
    {
        $id = $request->id;
        $data['edit_id'] = $id;
        $client_id = RentalQuotation::select('client_id','id','user_id')->where('id', '=', $id)->first();
        $data['user_name'] = User::select('first_name','last_name')->where('id','=',$client_id->user_id)->first();
        if (isset($client_id)) {
            $data['client_data'] = Client::where('id', '=', $client_id->client_id)->first();
              $data['quotation_rental'] = QuotationRental::where('rental_quotation_id', '=', $id)->where('client_id', '=', $client_id->client_id)->first();

                 $rental_quotation_equipment = RentalQuotationEquipment::where('quotation_id', '=', $id)
                ->with(['equipment_data' => function ($query) {
                    $query->select('id', 'Equipment', 'MonthlyRate');
                }])
                ->with(['quotations_rental_equipment_data' => function ($query) use ($id) {
                    $query->select('equipment_id', 'price', 'duration_rate')->where('rental_quotations_id', '=', $id);
                }])->get();

            $rental_equipment_array = array();
            foreach ($rental_quotation_equipment as $rental_equipment) {
                array_push($rental_equipment_array, $rental_equipment);
            }
            $data['rental_equipment_array'] = $rental_equipment_array;
            $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
                ->with(['assinged_user_qutation_rental' => function ($query) use ($id) {
                    $query->select('user_id')->where('quotation_id','=', $id);
                }])
                ->get();
            return $this->sendResponse($data, 'Success');
        }
    }

    public function UpdateQRData(Request $request)
    {
        $date = date('Y-m-d H:i:s');
        $id = $request->id;
        $client_id = RentalQuotation::where('id', '=', $id)->first();
        $quotation_rental = QuotationRental::where('rental_quotation_id', '=', $id)->where('client_id', '=', $client_id->client_id)->first();
        $quotation_rental->status = 1;
        $quotation_rental->terms_condations = utf8_encode((string)$request->term_and_condations);

        $quotation_rental->save();
        QuotationsRentalEquipment::where('quotations_rental_id', '=', $quotation_rental->id)->delete();
        $equipment_data = array();
        foreach ($request->equipment as  $k => $v) {
            // return $v;
            $v['rental_quotations_id'] = $id;
            $v['quotations_rental_id'] = $quotation_rental->id;
            $v['duration_rate'] = isset($v['duration_rate']) ? $v['duration_rate'] : null;
            $v['price'] = isset($v['price']) ? $v['price'] : null;
            $v['total_hired_period'] = isset($v['total_hired_period']) ? $v['total_hired_period'] : null;
            $v['created_at'] = isset($v['created_at']) ? $v['created_at'] : $date;
            $v['updated_at'] = isset($v['updated_at']) ? $v['updated_at'] : $date;
            array_push($equipment_data, $v);
        }

        QuotationsRentalEquipment::insert($equipment_data);
        $quotation_rental = QuotationRental::where('rental_quotation_id', '=', $id)->where('client_id', '=', $client_id->client_id)->first();
        $basic_price = QuotationsRentalEquipment::where('rental_quotations_id', '=', $id)->sum('price');
        $quotation_rental->total_price = $basic_price;
        $quotation_rental->save();

        QuotationRentalAssine::where('quotation_id', '=', $id)->delete();
        $assignee = $request->assignee;
        foreach ($assignee as $assignes) {
            $rental_quotation_assigne = new QuotationRentalAssine();
            $rental_quotation_assigne->quotation_id = $id;
            $rental_quotation_assigne->user_id = $assignes;
            $rental_quotation_assigne->status = 1;
            $rental_quotation_assigne->save();
        }
        $calculated_price = DB::select('SELECT id, (price/duration_rate)*total_hired_period  AS total_price FROM quotations_rental_equipment WHERE quotations_rental_id = '.$quotation_rental->id.'');
        foreach($calculated_price as $price_base){
            $qutation_price_update = QuotationsRentalEquipment::where('id',$price_base->id)->first();
            $qutation_price_update->total_period_base_amount = $price_base->total_price;
            $qutation_price_update->save();
        }
        return $this->sendResponse([], 'Updated');
    }

    public function ViewQRData(Request $request)
    {
        $quotation_id = $request->id;
        $data['view_id'] = $quotation_id;
        $client_id = RentalQuotation::where('id', '=', $quotation_id)->first();
        $data['client_data'] = Client::where('id', '=', $client_id->client_id)->first();

        $data['rental_quotation'] = RentalQuotationEquipment::where('quotation_id', '=', $quotation_id)
        ->with(['equipment_data' => function ($query) {
            $query->select('id', 'Equipment', 'MonthlyRate');
        }])
        ->with(['quotations_rental_equipment_data' => function ($query) use ($quotation_id) {
            $query->select('equipment_id', 'price', 'duration_rate')->where('rental_quotations_id', '=', $quotation_id);
        }])->get();

    //    return $data['rental_quotation'] =  RentalQuotationEquipment::where('quotation_id', $quotation_id)
    //         ->with(['equipment_data' => function ($query) {
    //             $query->select('id', 'Equipment', 'EquipmentStatusID');
    //         }])->with(['QuotationsRentalRquipmentPrice' => function ($query) {
    //             $query->select('id', 'equipment_id', 'price');
    //         }])
    //         ->get();
        return $data;
    }

    public function ChangeQRAssigneStatus(Request $request)
    {
        // return $request->all();
        $reject_note = $request->reject_note;
        $quotation_id = $request->id;
        $status = $request->status;
        $user_id = Auth::User()->id;
        $rental_quotation_assigne = QuotationRentalAssine::where('quotation_id', '=', $quotation_id)->where('user_id', '=', $user_id)->first();
        if ($rental_quotation_assigne) {
            $rental_quotation_assigne->status = $status;
            $rental_quotation_assigne->save();
            $statuses =  QuotationRentalAssine::select('status')->where('quotation_id', '=', $quotation_id)->get()->toArray();
            $statuses_ids = array_column($statuses, 'status');
            $rqr_status_approved = in_array(1, $statuses_ids) ? 1 : 2;
            $rqr_status_cancle = in_array(3, $statuses_ids) ? 3 : $rqr_status_approved;
            if ($rqr_status_approved === 2) {
                $update_status = QuotationRental::where('rental_quotation_id', '=', $quotation_id)->first();
                $update_status->status = 2;
                $update_status->final_approve = 2;
                $update_status->save();
            } else {
                $update_status = QuotationRental::where('rental_quotation_id', '=', $quotation_id)->first();
                $update_status->status = 1;
                $update_status->final_approve = 1;
                $update_status->save();
            }
            if ($rqr_status_cancle === 3) {
                if ($status == 3) {
                    if ($reject_note) {
                        $rental_quotation_assigne->reject_note = $reject_note;
                        $rental_quotation_assigne->save();
                    }
                }
                $update_status = QuotationRental::where('rental_quotation_id', '=', $quotation_id)->first();
                $update_status->status = 3;
                $update_status->final_approve = 1;
                $update_status->save();
            }
            return $this->sendResponse([], 'Status Updated');
        } else {
            return $this->sendError([], 'You Are Not Authorized to Change Status');
        }
    }


    public function ChangeFinalApprove(Request $request)
    {
         $request->all();
        // rental_quotations_id
        $equipment_array_for_change_new_status = QuotationsRentalEquipment::where('rental_quotations_id','=',$request->rental_quotation_id)->pluck('equipment_id');
        $total_equipments =  RentalQuotation::where('id','=',$request->rental_quotation_id)->first();
        $final_approve = QuotationRental::where('rental_quotation_id','=',$request->rental_quotation_id)->first();
        // return $final_approve->id;
        $quotations_rental_equipment = QuotationsRentalEquipment::where('rental_quotations_id', '=', $request->rental_quotation_id)->sum('total_period_base_amount');
        $final_approve->final_approve = $request->final_approve;
        $final_approve->save();
        $final_approve;
        if($final_approve->final_approve == 4){
           $rental_delivery_order_record = RentalDeliveryOrder::where('rental_quotation_id','=',$final_approve->rental_quotation_id)->where('quotation_rental_id','=',$final_approve->id)->first();
            if($rental_delivery_order_record){
                return $this->sendResponse([], 'Approved');
                // equipment_booked
            }else{
                $rental_delivery_order = new RentalDeliveryOrder();
                $rental_delivery_order->rental_quotation_id = $final_approve->rental_quotation_id;
                $rental_delivery_order->quotation_rental_id = $final_approve->id;
                $rental_delivery_order->client_id = $final_approve->client_id;
                $rental_delivery_order->total_basic_price = $final_approve->total_price;
                $rental_delivery_order->total_period_base_amount = $quotations_rental_equipment;
                $rental_delivery_order->status = 1;
                $rental_delivery_order->paid_unpaid = 1;
                $rental_delivery_order->save();
    
                Orders::where('quotation_rental_id','=',$final_approve->id)->delete();
                $orders = new Orders();
                $orders->rental_quotation_id = $final_approve->rental_quotation_id;
                $orders->quotation_rental_id = $final_approve->id;
                $orders->client_id = $final_approve->client_id;
                $orders->status = 1;
                $orders->total_basic_price = $final_approve->total_price;
                $orders->todays_date = $final_approve->updated_at;
                $orders->total_equipments = $total_equipments->total_equipments;
                $orders->save();
                $equipment_book = Equipment::select('id','Equipment')->whereIn('id',$equipment_array_for_change_new_status)->get();
                foreach($equipment_book as $booked){
                    // return $booked;
                    $booked->equipment_booked = 2;
                    $booked->save();
                }
                return $this->sendResponse([], 'Approved');
            }
            }else{
                return $this->sendResponse([], 'Rejected');
            }
        }

    public function ViewQRAssigne(Request $request)
    {
        $id = $request->id;
        $rental_quotation_assigne = QuotationRentalAssine::where('quotation_id', $id)
            ->with(['AssigneUserNameQR' => function ($query) {
                $query->select('id', 'user_name');
            }])
            ->get();
        return $rental_quotation_assigne;
    }

    public function GetQRData(Request $request)
    {
        $rental_quotations_data = Client::select(
            "clients.id",
            "clients.name",
            "rental_quotations.*"
        )
            ->join("rental_quotations", "rental_quotations.client_id", "=", "clients.id")
            ->where('rental_quotations.status', '=', 2)->get();
        foreach ($rental_quotations_data as $rental_quotation_list) {
            $previous_data = QuotationRental::where('rental_quotation_id', '=', $rental_quotation_list->id)->first();
            if ($previous_data) {
                if ($previous_data->rental_quotation_id === $rental_quotation_list->id) {
                    //data is already added so if condation is just for checking
                } else {
                    $quotation_rental = new QuotationRental();
                    $quotation_rental->rental_quotation_id = $rental_quotation_list->id;
                    $quotation_rental->client_id = $rental_quotation_list->client_id;
                    $quotation_rental->status = 1;
                    $quotation_rental->final_approve = 1;
                    $quotation_rental->save();
                }
            } else {
                $quotation_rental = new QuotationRental();
                $quotation_rental->rental_quotation_id = $rental_quotation_list->id;
                $quotation_rental->client_id = $rental_quotation_list->client_id;
                $quotation_rental->status = 1;
                $quotation_rental->final_approve = 1;
                $quotation_rental->save();
            }
        }
        // $data['rental_quotations_approved_data'] = $rental_quotations_data;
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "quotations_rental.*"
        )
            ->join("quotations_rental", "quotations_rental.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status)) {
            $user_list->orWhere('quotations_rental.status', '=', $request->status);
        }
        // search by status
        if (isset($request->start_date) && $request->end_date) {
            $user_list->whereBetween('quotations_rental.created_at', [$request->start_date, $request->end_date]);
        }
        // search by quotation rental id base
        if (isset($request->id)) {
            $user_list->orWhere('quotations_rental.rental_quotation_id', '=', $request->id);
        }
        return response()->json($user_list->paginate((int)$per_page,), 200);
    }

    public function QRSearch(Request $request)
    {
        //    return $request->all();
        $per_page = 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "quotations_rental.*"
        )
            ->join("quotations_rental", "quotations_rental.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status)) {
            $user_list->orWhere('quotations_rental.status', '=', $request->status);
        }
        // search by status
        if (isset($request->start_date) && $request->end_date) {
            $user_list->whereBetween('quotations_rental.created_at', [$request->start_date, $request->end_date]);
        }
        // search by quotation rental id base
        if (isset($request->id)) {
            $user_list->orWhere('quotations_rental.rental_quotation_id', '=', $request->id);
        }
        return response()->json($user_list->paginate($per_page));
    }

    public function GetRentalDeliveryData(Request $request)
    {
        
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "rental_delivery_order.*"
        )
            ->join("rental_delivery_order", "rental_delivery_order.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status)) {
            $user_list->orWhere('rental_delivery_order.status', '=', $request->status);
        }
        // search by status
        if (isset($request->start_date) && $request->end_date) {
            $user_list->whereBetween('rental_delivery_order.created_at', [$request->start_date, $request->end_date]);
        }
        return response()->json($user_list->paginate((int)$per_page,), 200);
    }

    public function UpdateRDOForm(Request $request)
    {
        $request->validate([
            'attachment' => 'mimes:png,jpg,jpeg,docx,pdf,txt',
            'assignee.*' => 'required'
        ]);
        $quotation_rental_id = $request->id;
        $rental_delivery_order = RentalDeliveryOrder::where('quotation_rental_id', '=', $quotation_rental_id)->first();
        // $rental_delivery_order->sales_person = $request->sales_person;
        $rental_delivery_order->lpo_number = $request->lpo_number;
        $rental_delivery_order->expected_hire_period_start = $request->expected_hire_period_start;
        $rental_delivery_order->expected_hire_period_end = $request->expected_hire_period_end;
        $rental_delivery_order->operator_accommodation = $request->operator_accommodation;
        // $rental_delivery_order->operator_transport = $request->operator_transport;
        $rental_delivery_order->arked_quotation_ref = $request->arked_quotation_ref;

        if ($request->hasFile('attachment')) {
            $image = $request->file('attachment');
            $ext = $image->extension();
            $image_name = time() . '.' . $ext;

            $imagePath = public_path('/images/' . $rental_delivery_order->attachment);
            if ($rental_delivery_order->attachment) {
                unlink($imagePath);
            }

            $destinationPath = public_path() . '/images';
            $image->move($destinationPath, $image_name);
            $rental_delivery_order->attachment = $image_name;
        }

        $rental_delivery_order->todays_date = $request->todays_date;
        $rental_delivery_order->required_deliver_date_time = $request->required_deliver_date_time;
        // $rental_delivery_order->company = $request->company;
        $rental_delivery_order->main_office_addres = $request->main_office_addres;
        $rental_delivery_order->rdo_contact_number = $request->rdo_contact_number;
        $rental_delivery_order->rdo_contact_person = $request->rdo_contact_person;
        $rental_delivery_order->delivery_location_address = $request->delivery_location_address;
        $rental_delivery_order->note = $request->note;
        $rental_delivery_order->save();
        $rentalquotationequipment = RentalQuotationEquipment::select('equipment_id', 'quantity', 'description')->where('quotation_id', $rental_delivery_order->rental_quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment');
            }])
            ->get();

        RentalDeliveryOrderEqupment::where('quotation_rental_id', '=', $quotation_rental_id)->delete();
        $equipment_data = array();
        foreach ($request->equipment as  $k => $v) {
            $v['rental_quotation_id'] = $rental_delivery_order->rental_quotation_id;
            $v['quotation_rental_id'] = $quotation_rental_id;
            $v['specification'] = isset($v['specification']) ? $v['specification'] : null;
            $v['accessories'] = isset($v['accessories']) ? $v['accessories'] : null;
            $v['agreed_rate'] = isset($v['agreed_rate']) ? $v['agreed_rate'] : null;
            array_push($equipment_data, $v);
        }
        RentalDeliveryOrderEqupment::insert($equipment_data);
        $RentalDeliveryAssignes = RentalDeliveryAssignes::where('quotation_rental_id', '=', $rental_delivery_order->quotation_rental_id)->delete();
        $assignee = $request->assignee;
        foreach ($assignee as $assignes) {
            $rental_quotation_assigne = new RentalDeliveryAssignes();
            $rental_quotation_assigne->quotation_rental_id = $rental_delivery_order->quotation_rental_id;
            $rental_quotation_assigne->user_id = $assignes;
            $rental_quotation_assigne->status = 1;
            $rental_quotation_assigne->save();
        }
        return $this->sendResponse([], 'Updated');
    }

    public function EditRDOData(Request $request)
    {
        $quotation_rental_id = $request->id;
        $data['edit_id'] = $quotation_rental_id;
        $quotationr_ental_rec = QuotationRental::where('id','=',$quotation_rental_id)->first();
        $data['transport'] = Transport::select('id','name')->get();
        $data['sp_name_data'] = RentalQuotation::select('id','sp_name')->where('id','=',$quotationr_ental_rec->rental_quotation_id)->first();
        $rental_delivery_order = RentalDeliveryOrder::where('quotation_rental_id', '=', $quotation_rental_id)->first();
        $client_data = Client::where('id', '=', $rental_delivery_order->client_id)->first();
        $rentalquotationequipment = RentalQuotationEquipment::where('quotation_id', $rental_delivery_order->rental_quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment');
            }])
			->with(['quotations_rental_equipment_data' => function ($query) use ($quotation_rental_id) {
                    $query->select('equipment_id', 'price', 'duration_rate')
					->where('quotations_rental_id', '=', $quotation_rental_id);
                }])
            ->with(['rental_delivery_equpment_data' => function ($querynew) use ($quotation_rental_id) {
                $querynew->where('quotation_rental_id', '=', $quotation_rental_id);
            }])
            ->get();

        $rental_delivery_equpment = RentalDeliveryOrderEqupment::where('quotation_rental_id', '=', $quotation_rental_id)
        ->where('rental_quotation_id', '=', $rental_delivery_order->rental_quotation_id)->first();
    //   return $rental_delivery_order->rental_quotation_id;
        $assined_user = QuotationRentalAssine::select('id', 'user_id', 'status')
       ->where('quotation_id', '=', $rental_delivery_order->rental_quotation_id)->where('status', '=', 2)
            ->with(['users' => function ($query) {
                $query->select('id')->with(['roles' => function ($query) {
                    $query->select('id', 'name');
                }]);
            }])
            ->get();

        $data['rental_delivery_order'] = $rental_delivery_order;
        $data['rentalquotationequipment'] = $rentalquotationequipment;
        $data['client_data'] = $client_data;
        $data['rental_delivery_equpment'] = $rental_delivery_equpment;
        $data['assined_user'] = $assined_user;
        $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
            ->with(['assinged_user_rdo_assigne' => function ($query) use ($quotation_rental_id) {
                $query->select('user_id')->where('quotation_rental_id','=', $quotation_rental_id);
            }])
            ->get();
        return $data;
    }

    public function ViewRDOAssigne(Request $request)
    {
        $id = $request->id;
        $rental_delivery_assigne = RentalDeliveryAssignes::where('quotation_rental_id', $id)
            ->with(['AssigneUserNameRDO' => function ($query) {
                $query->select('id', 'user_name');
            }])
            ->get();
        return $rental_delivery_assigne;
    }

    public function ViewRDOFormData(Request $request)
    {
        $rental_deliver_order_id = $request->id;
        $data['view_record_id'] = $rental_deliver_order_id;
        $rental_deliver_data = RentalDeliveryOrder::where('quotation_rental_id', '=', $rental_deliver_order_id)->first();
        $client_data = Client::where('id', '=', $rental_deliver_data->client_id)->first();
        $quotation_rental_ids = $rental_deliver_data->quotation_rental_id;
        $rental_delivery_equipment_data = RentalQuotationEquipment::where('quotation_id', $rental_deliver_data->rental_quotation_id)
        ->with(['equipment_data' => function ($query) {
            $query->select('id', 'Equipment');
        }])
        ->with(['quotations_rental_equipment_data' => function ($query) use ($quotation_rental_ids) {
                $query->select('equipment_id', 'price', 'duration_rate')
                ->where('quotations_rental_id', '=', $quotation_rental_ids);
            }])
        ->with(['rental_delivery_equpment_data' => function ($querynew) use ($quotation_rental_ids) {
            $querynew->where('quotation_rental_id', '=', $quotation_rental_ids);
        }])
        ->get();
        $data['client_data'] = $client_data;
        $data['rental_delivery_equipment_data'] = $rental_delivery_equipment_data;
        return $data;
    }

    public function ChangeRDOAssigneStatus(Request $request)
    {
        $reject_note = $request->reject_note;
        $quotation_id = $request->id;
        $status = $request->status;
        $user_id = Auth::User()->id;
        $rental_delivery_assigne = RentalDeliveryAssignes::where('quotation_rental_id', '=', $quotation_id)->where('user_id', '=', $user_id)->first();
        if ($rental_delivery_assigne) {
            $rental_delivery_assigne->status = $status;
            $rental_delivery_assigne->save();
            $statuses =  RentalDeliveryAssignes::select('status')->where('quotation_rental_id', '=', $quotation_id)->get()->toArray();
            $statuses_ids = array_column($statuses, 'status');
            $rqr_status_approved = in_array(1, $statuses_ids) ? 1 : 2;
            $rqr_status_cancle = in_array(3, $statuses_ids) ? 3 : $rqr_status_approved;
            if ($rqr_status_approved === 2) {
                $update_status = RentalDeliveryOrder::where('quotation_rental_id', '=', $quotation_id)->first();
                $update_status->status = 2;
                $update_status->save();
            } else {
                $update_status = RentalDeliveryOrder::where('quotation_rental_id', '=', $quotation_id)->first();
                $update_status->status = 1;
                $update_status->save();
            }
            if ($rqr_status_cancle === 3) {
                if ($status == 3) {
                    if ($reject_note) {
                        $rental_delivery_assigne->reject_note = $reject_note;
                        $rental_delivery_assigne->save();
                    }
                }
                $update_status = RentalDeliveryOrder::where('quotation_rental_id', '=', $quotation_id)->first();
                $update_status->status = 3;
                $update_status->save();
            }
            return $this->sendResponse([], 'Status Updated');
        } else {
            return $this->sendError([], 'You Are Not Authorized to Change Status');
        }
    }

    public function DownloadRDOAttachment(Request $request)
    {
        $rental_delivery_order_id = $request->id;
        $rental_delivery_order = RentalDeliveryOrder::where('quotation_rental_id', '=', $rental_delivery_order_id)->first();
        return asset('public/images/' . $rental_delivery_order->attachment);
    }

    public function GetPriorDeliveryInspection(Request $request)
    {
        $rental_quotations_data = Client::select(
            "clients.id",
            "clients.name",
            "rental_delivery_order.*"
        )
            ->join("rental_delivery_order", "rental_delivery_order.client_id", "=", "clients.id")
            ->where('rental_delivery_order.status', '=', 2)->get();
        foreach ($rental_quotations_data as $rental_quotation_list) {

            $previous_data = PriorDeliveryInspection::where('rental_delivery_order_id', '=', $rental_quotation_list->id)->first();
            if ($previous_data) {
                // return $previous_data;
                if ($previous_data->rental_delivery_order_id === $rental_quotation_list->id) {
                    //data is already added so if condation is just for checking
                } else {
                    $prior_delivery_inspection = new PriorDeliveryInspection();
                    $prior_delivery_inspection->rental_quotation_id = $rental_quotation_list->rental_quotation_id;
                    $prior_delivery_inspection->quotation_rental_id = $rental_quotation_list->quotation_rental_id;
                    $prior_delivery_inspection->rental_delivery_order_id = $rental_quotation_list->id;
                    $prior_delivery_inspection->client_id = $rental_quotation_list->client_id;
                    $prior_delivery_inspection->total_basic_price = $rental_quotation_list->total_basic_price;
                    $prior_delivery_inspection->total_period_base_amount = $rental_quotation_list->total_period_base_amount;
                    $prior_delivery_inspection->status = 1;
                    $prior_delivery_inspection->save();
                }
            } else {
                $prior_delivery_inspection = new PriorDeliveryInspection();
                $prior_delivery_inspection->rental_quotation_id = $rental_quotation_list->rental_quotation_id;
                $prior_delivery_inspection->quotation_rental_id = $rental_quotation_list->quotation_rental_id;
                $prior_delivery_inspection->rental_delivery_order_id = $rental_quotation_list->id;
                $prior_delivery_inspection->client_id = $rental_quotation_list->client_id;
                $prior_delivery_inspection->total_basic_price = $rental_quotation_list->total_basic_price;
                $prior_delivery_inspection->total_period_base_amount = $rental_quotation_list->total_period_base_amount;
                $prior_delivery_inspection->status = 1;
                $prior_delivery_inspection->save();
            }
        }
        // $data['rental_quotations_approved_data'] = $rental_quotations_data;
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "prior_delivery_inspection.*"
        )
            ->join("prior_delivery_inspection", "prior_delivery_inspection.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status)) {
            $user_list->orWhere('prior_delivery_inspection.status', '=', $request->status);
        }
        // search by status
        if (isset($request->start_date) && $request->end_date) {
            $user_list->whereBetween('prior_delivery_inspection.created_at', [$request->start_date, $request->end_date]);
        }
        return response()->json($user_list->paginate((int)$per_page,), 200);
    }

    public function EditPDFormStepOne(Request $request)
    {
        $rdo_id = $request->id;
        $data['edit_step_one_id'] = $rdo_id;
        $prior_delivery_inspection = PriorDeliveryInspection::where('rental_delivery_order_id', '=', $rdo_id)->first();

        $equmpment_data = RentalDeliveryOrderEqupment::select('id', 'equipment_id')
            ->where('quotation_rental_id', $prior_delivery_inspection->quotation_rental_id)
            ->with(['equipment_records' => function ($query) {
                $query->select('id', 'Equipment');
            }])
            ->get();
        $data['equmpment_data'] = $equmpment_data;
        return $data;
    }

    public function EditPDFormStepTwo(Request $request)
    {
        $rdo_id = $request->rdo_id;
        $equipment_id = $request->equipment_id;
        $equipment_data = Equipment::select('id', 'YOM', 'Capacity', 'Equipment', 'PurchaseOrderNumber', 'MachineModel', 'SupplierPlantNumber', 'SerialNumber', 'HourlyRate', 'DailyRate', 'WeeklyRate', 'PlantNumber', 'PlateNumber', 'PlateExpiry')->where('id', '=', $equipment_id)->first();
        $inspectionlist_data_categort_one = InspectionLlist::select('id','name')->where('cat_id', '=', 1)
            ->with(['selected' => function ($query) use ($equipment_id, $rdo_id) {
                $query->select('id', 'inspection_list_id', 'arkad', 'owner')->where('rdo_id', '=', $rdo_id)->where('equipment_id', '=', $equipment_id)->where('category_id', '=', 1);
            }])
        ->get();

        $inspectionlist_data_categort_two = InspectionLlist::select('id','name')->where('cat_id', '=', 2)
            ->with(['selected' => function ($query) use ($equipment_id, $rdo_id) {
                $query->select('id', 'inspection_list_id', 'arkad', 'owner')->where('rdo_id', '=', $rdo_id)->where('equipment_id', '=', $equipment_id)->where('category_id', '=', 2);
            }])
        ->get();

        $inspectionlist_data_categort_three = InspectionLlist::select('id','name')->where('cat_id', '=', 3)
            ->with(['selected' => function ($query) use ($equipment_id, $rdo_id) {
                $query->select('id', 'inspection_list_id', 'arkad', 'owner')->where('rdo_id', '=', $rdo_id)->where('equipment_id', '=', $equipment_id)->where('category_id', '=', 3);
            }])
        ->get();
        $prior_delivery_id =  PriorDeliveryInspection::select('id','quotation_rental_id')->where('rental_delivery_order_id','=',$rdo_id)->first();
        $QuotationsRentalEquipment = QuotationsRentalEquipment::select('equipment_id')
         ->where('quotations_rental_id','=',$prior_delivery_id->quotation_rental_id)
         ->withCount(['PriorDeliveCheckEquipment'=>function($query)use($rdo_id){
             $query->where('rdo_id', '=', $rdo_id);
         }])
        ->get()->toArray();
        $counted_records = array_column($QuotationsRentalEquipment,'prior_delive_check_equipment_count');
        if(in_array(0,$counted_records)){
            $data['rdo_id'] = $rdo_id;
            $data['equipment_id'] = $equipment_id;
            $data['equipment_data'] = $equipment_data;
            $data['inspectionlist_data_categort_one'] = $inspectionlist_data_categort_one;
            $data['inspectionlist_data_categort_two'] = $inspectionlist_data_categort_two;
            $data['inspectionlist_data_categort_three'] = $inspectionlist_data_categort_three;
            return $data;
        }else{
            $assigne = User::select('id', 'user_name')->where('status', '!=', 0)
            ->with(['assinged_user_prior_delivery_assigne' => function ($query) use ($rdo_id) {
                $query->select('user_id')->where('prior_delivery_id','=',$rdo_id);
        }])
        ->get();
        
        $data['rdo_id'] = $rdo_id;
        $data['equipment_id'] = $equipment_id;
        $data['equipment_data'] = $equipment_data;
        $data['inspectionlist_data_categort_one'] = $inspectionlist_data_categort_one;
        $data['inspectionlist_data_categort_two'] = $inspectionlist_data_categort_two;
        $data['inspectionlist_data_categort_three'] = $inspectionlist_data_categort_three;
        $data['assigne'] = $assigne;
        return $data;
         }
    }

    public function UpdatePDIForm(Request $request)
    {
        $data = $request->all();
        $rdo_id = $data['data'][0]['rdo_id'] ;
        $PriorDeliveryInspection = PriorDeliveryInspection::where('rental_delivery_order_id','=',$rdo_id)->first();
        $equipment_id = $data['data'][0]['equipment_id'] ;
        $category_id = $data['data'][0]['category_id'] ;
        PriorDeliveryChecked::where('equipment_id','=',$equipment_id)
        ->where('rdo_id','=',$rdo_id)->where('category_id','=',$category_id)->delete();
        PriorDeliveryChecked::insert($data['data']);  
    //     $assignee = $request->assignee;
    //     if($assignee){
    //     PriorDeliveryAssigne::where('prior_delivery_id', '=', $rdo_id)->delete();
    //     foreach ($assignee as $assignes) {
    //         $rental_quotation_assigne = new PriorDeliveryAssigne();
    //         $rental_quotation_assigne->prior_delivery_id = $rdo_id;
    //         $rental_quotation_assigne->user_id = $assignes;
    //         $rental_quotation_assigne->status = 1;
    //         $rental_quotation_assigne->save();
    //     }
    // }

    // return $this->sendResponse([], 'Status Updated');

        $prior_delivery_id =  PriorDeliveryInspection::select('id','quotation_rental_id')
        ->where('rental_delivery_order_id','=',$rdo_id)->first();
        $QuotationsRentalEquipment = QuotationsRentalEquipment::select('equipment_id')
         ->where('quotations_rental_id','=',$prior_delivery_id->quotation_rental_id)
         ->withCount(['PriorDeliveCheckEquipment'=>function($query)use($rdo_id){
             $query->where('rdo_id', '=', $rdo_id);
         }])
         ->get()->toArray();
        
         $counted_records = array_column($QuotationsRentalEquipment,'prior_delive_check_equipment_count');
         if(in_array(0,$counted_records)){
            return $this->sendResponse([], 'Status Updated');
         }else{
            $assigne = User::select('id', 'user_name')->where('status', '!=', 0)
            ->with(['assinged_user_prior_delivery_assigne' => function ($query) use ($rdo_id) {
                $query->select('user_id')->where('prior_delivery_id','=',$rdo_id);
            }])
            ->get();
            $rdo_id = $data['data'][0]['rdo_id'] ;
            $dataa['rdo_id'] = $rdo_id;
            $dataa['assigne'] = $assigne;
             return $dataa;
         }
    }

    public function UpdateAssigneesPDI(Request $request){
        $rdo_id = $request->rdo_id;
        $assignee = $request->assignee;
        if($assignee){
        PriorDeliveryAssigne::where('prior_delivery_id', '=', $rdo_id)->delete();
        foreach ($assignee as $assignes) {
            $rental_quotation_assigne = new PriorDeliveryAssigne();
            $rental_quotation_assigne->prior_delivery_id = $rdo_id;
            $rental_quotation_assigne->user_id = $assignes;
            $rental_quotation_assigne->status = 1;
            $rental_quotation_assigne->save();
        }
    }
    return $this->sendResponse([], 'Assignees Selected ');
    }

    public function ViewPDIData(Request $request)
    {
        $rdo_id = $request->rdo_id;
        $data['view_id'] = $rdo_id;
        $prior_delivery_inspection = PriorDeliveryInspection::where('rental_delivery_order_id', '=', $rdo_id)->first();
        $data['client_data'] = Client::select('id', 'name', 'email')->where('id', '=', $prior_delivery_inspection->client_id)->first();
        $quotation_rental_id_rel = $prior_delivery_inspection->quotation_rental_id;
        $data['rental_quotation'] =  RentalQuotationEquipment::select('id','equipment_id','quantity','site_location')->where('quotation_id', $prior_delivery_inspection->rental_quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment', 'EquipmentStatusID');
            }])
            ->with(['QuotationsRentalRquipmentPrice' => function ($query) use($quotation_rental_id_rel) {
                $query->select('id', 'equipment_id', 'price')->where('quotations_rental_id','=',$quotation_rental_id_rel);
            }])
        ->get();
        return $data;
    }
    public function ChangePDIAssigneStatus(Request $request)
    {
        $reject_note = $request->reject_note;
        $prior_delivery_id = $request->rdo_id;
        $status = $request->status;
        $user_id = Auth::User()->id;
        $prior_delivery_assigne = PriorDeliveryAssigne::where('prior_delivery_id', '=', $prior_delivery_id)->where('user_id', '=', $user_id)->first();
        if ($prior_delivery_assigne) {
            $prior_delivery_assigne->status = $status;
            $prior_delivery_assigne->save();
            $statuses =  PriorDeliveryAssigne::select('status')->where('prior_delivery_id', '=', $prior_delivery_id)->get()->toArray();
            $statuses_ids = array_column($statuses, 'status');
            $rqr_status_approved = in_array(1, $statuses_ids) ? 1 : 2;
            $rqr_status_cancle = in_array(3, $statuses_ids) ? 3 : $rqr_status_approved;
            if ($rqr_status_approved === 2) {
                $update_status = PriorDeliveryInspection::where('rental_delivery_order_id', '=', $prior_delivery_id)->first();
                $update_status->status = 2;
                $update_status->save();
            } else {
                $update_status = PriorDeliveryInspection::where('rental_delivery_order_id', '=', $prior_delivery_id)->first();
                $update_status->status = 1;
                $update_status->save();
            }
            if ($rqr_status_cancle === 3) {
                if ($status == 3) {
                    if ($reject_note) {
                        $prior_delivery_assigne->reject_note = $reject_note;
                        $prior_delivery_assigne->save();
                    }
                }
                $update_status = PriorDeliveryInspection::where('rental_delivery_order_id', '=', $prior_delivery_id)->first();
                $update_status->status = 3;
                $update_status->save();
            }
            return $this->sendResponse([], 'Status Updated');
        } else {
            return $this->sendError([], 'You Are Not Authorized to Change Status');
        }
    }

    public function ViewPDIAssigne(Request $request)
    {
        $id = $request->rdo_id;
        $rental_delivery_assigne = PriorDeliveryAssigne::where('prior_delivery_id', $id)
            ->with(['AssigneUserNamePDI' => function ($query) {
                $query->select('id', 'user_name');
            }])
            ->get();
        return $rental_delivery_assigne;
    }

    public function GetRentalDeliveryNote(Request $request)
    {
        $prior_delivery_inspection = Client::select(
            "clients.id",
            "clients.name",
            "prior_delivery_inspection.*"
        )
            ->join("prior_delivery_inspection", "prior_delivery_inspection.client_id", "=", "clients.id")
            ->where('prior_delivery_inspection.status', '=', 2)->get();
        foreach ($prior_delivery_inspection as $prior_delivery_inspection_list) {
            // RentalDeliverNote
            $previous_data = RentalDeliverNote::where('prior_delivery_inspection_id', '=', $prior_delivery_inspection_list->id)->first();
            if ($previous_data) {
                // return $previous_data;
                if ($previous_data->prior_delivery_inspection_id === $prior_delivery_inspection_list->id) {
                    //data is already added so if condation is just for checking
                } else {
                    $rental_deliver_note = new RentalDeliverNote();
                    $rental_deliver_note->rental_quotation_id = $prior_delivery_inspection_list->rental_quotation_id;
                    $rental_deliver_note->quotation_rental_id = $prior_delivery_inspection_list->quotation_rental_id;
                    $rental_deliver_note->rental_delivery_order_id = $prior_delivery_inspection_list->rental_delivery_order_id;
                    $rental_deliver_note->prior_delivery_inspection_id = $prior_delivery_inspection_list->id;
                    $rental_deliver_note->client_id = $prior_delivery_inspection_list->client_id;
                    $rental_deliver_note->total_basic_price = $prior_delivery_inspection_list->total_basic_price;
                    $rental_deliver_note->total_period_base_amount = $prior_delivery_inspection_list->total_period_base_amount;
                    $rental_deliver_note->status = 1;
                    $rental_deliver_note->paid_unpaid = 1;
                    $rental_deliver_note->save();
                }
            } else {
                $rental_deliver_note = new RentalDeliverNote();
                $rental_deliver_note->rental_quotation_id = $prior_delivery_inspection_list->rental_quotation_id;
                $rental_deliver_note->quotation_rental_id = $prior_delivery_inspection_list->quotation_rental_id;
                $rental_deliver_note->rental_delivery_order_id = $prior_delivery_inspection_list->rental_delivery_order_id;
                $rental_deliver_note->prior_delivery_inspection_id = $prior_delivery_inspection_list->id;
                $rental_deliver_note->client_id = $prior_delivery_inspection_list->client_id;
                $rental_deliver_note->total_basic_price = $prior_delivery_inspection_list->total_basic_price;
                $rental_deliver_note->total_period_base_amount = $prior_delivery_inspection_list->total_period_base_amount;
                $rental_deliver_note->status = 1;
                $rental_deliver_note->paid_unpaid = 1;
                $rental_deliver_note->save();
            }
        }
        // $data['rental_quotations_approved_data'] = $rental_quotations_data;

        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "rental_delivery_note.*"
        )
            ->join("rental_delivery_note", "rental_delivery_note.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status)) {
            $user_list->orWhere('rental_delivery_note.status', '=', $request->status);
        }
        // search by status
        if (isset($request->start_date) && $request->end_date) {
            $user_list->whereBetween('rental_delivery_note.created_at', [$request->start_date, $request->end_date]);
        }
        return response()->json($user_list->paginate((int)$per_page,), 200);
    }

    public function EditRDNForm(Request $request)
    {
        $pdi_id = $request->pdi_id;
        $rental_deliver_note = RentalDeliverNote::where('prior_delivery_inspection_id','=',$pdi_id)->first();
        // $data['rental_deliver_note'] = $rental_deliver_note;
        // $data['rental_quotation_id'] = $rental_deliver_note->rental_quotation_id;
        $data['client_name'] = Client::select('id','name','phone','contact_person')->where('id','=',$rental_deliver_note->client_id)->first();
        $data['rental_delivery_order'] = RentalDeliveryOrder::select('id','lpo_number')
        ->where('id','=',$rental_deliver_note->rental_delivery_order_id)->first();
     
        $data['rental_delivery_equpment'] = RentalDeliveryOrderEqupment::select('equipment_id','accessories')
        ->where('rental_quotation_id','=',$rental_deliver_note->rental_quotation_id)
        // ->with(['DeliveryNoteRecord'=>function($query) use($pdi_id){
        //     $query->select('id','pdi_id','equipment_id','date','hours','services_meter_reading','attachment')
        //     ->where('pdi_id','=',$pdi_id);
        // }])
        ->with(['equipment_records'=>function($q){
            $q->select('id','MachineModel','PlateNumber','PlantNumber','EngineNumber','MachineMakerID','final_status')
            ->with(['MachineMakerRecord'=>function($eqp){
                $eqp->select('id','machineMaker');
            }]);
        }])
        ->get();
        // $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
        // ->with(['assinged_user_rdn_assigne' => function ($query) use ($pdi_id) {
        //     $query->select('user_id')->where('delivery_note_id','=', $pdi_id);
        // }])
        // ->get();
        
        return  $data;
    }


    public function EditRDNFormStepTwo(Request $request)
    {   
        $pdi_id = $request->pdi_id;
        $equipment_id = $request->equipment_id;
        $data['pdi_id'] = $pdi_id;
        $data['equipment_id'] = $equipment_id;
       $leassor_name = DeliveryNoteEquipment::select('id','equipment_id','leassor_name','attachment','date','hours','services_meter_reading')->where('pdi_id','=',$pdi_id)
        ->where('equipment_id','=',$equipment_id)->first();
        if($leassor_name){
            $data['delivery_note_detail'] = $leassor_name;
        }else{
            $data['delivery_note_detail'] = 'null';
        }
        $data['equipment_name'] = Equipment::select('id','Equipment')->where('id','=',$equipment_id)->first();
        $rental_deliver_note = RentalDeliverNote::where('prior_delivery_inspection_id','=',$pdi_id)->first();
        $data['client_name'] = Client::select('id','name','phone')->where('id','=',$rental_deliver_note->client_id)->first();
        $data['position'] = RentalQuotation::select('id','position')->where('id','=',$rental_deliver_note->rental_quotation_id)->first();
        $QuotationsRentalEquipment = QuotationsRentalEquipment::select('equipment_id')
        ->where('quotations_rental_id','=',$rental_deliver_note->quotation_rental_id)
        ->withCount(['RenatlDeliverNoteCheckEquipment'=>function($query)use($pdi_id){
            $query->where('pdi_id', '=', $pdi_id);
        }])
       ->get()->toArray();
       
        $counted_records = array_column($QuotationsRentalEquipment,'renatl_deliver_note_check_equipment_count');
       if(in_array(0,$counted_records)){
        return $data;
        }else{
            $rental_deliver_note_record = RentalDeliverNote::select('id','prior_delivery_inspection_id','signed','id_no','issued_form','on_date','attorney_no','authorized_no','dated','note')
            ->where('prior_delivery_inspection_id','=',$pdi_id)->first();
        $data['rental_deliver_note_record'] = $rental_deliver_note_record;
        $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
        ->with(['assinged_user_rdn_assigne' => function ($query) use ($pdi_id) {
            // $query->select('user_id')->where('quotation_id','=', $pdi_id);
        }])
        ->get();
        return $data;
        }
    }


    public function UpdateRDNForm(Request $request)
    {
         $request->all();
        $pdi_id = $request->pdi_id;
        $equipment_id = $request->equipment_id;
        $data['pdi_id'] = $pdi_id;
        $data['equipment_id'] = $equipment_id; 
        DeliveryNoteEquipment::where('pdi_id', '=', $pdi_id)->where('equipment_id','=',$equipment_id)->delete();
        // return  $update_delivery_note_record = DeliveryNoteEquipment::where('pdi_id', '=', $pdi_id)->where('equipment_id','=',$equipment_id)->first();
        $update_delivery_note_record = new DeliveryNoteEquipment();
        $update_delivery_note_record->pdi_id = $pdi_id;
        $update_delivery_note_record->equipment_id = $equipment_id;
        $update_delivery_note_record->leassor_name = $request->leassor_name;

        if ($request->hasFile('attachment')) {
            $image = $request->file('attachment');
            $ext = $image->extension();
            $image_name = time() . '.' . $ext;
            $imagePath = public_path('/images/' . $update_delivery_note_record->attachment);
            if ($update_delivery_note_record->attachment) {
                unlink($imagePath);
            }
            $destinationPath = public_path() . '/images';
            $image->move($destinationPath, $image_name);
            $update_delivery_note_record->attachment = $image_name;
        }

        $update_delivery_note_record->date = $request->date; 
        $update_delivery_note_record->hours = $request->hours; 
        $update_delivery_note_record->services_meter_reading = $request->services_meter_reading; 
        $update_delivery_note_record->save();

        $rental_deliver_note = RentalDeliverNote::where('prior_delivery_inspection_id','=',$pdi_id)->first();
       $assigne_id = $rental_deliver_note->id;
        $QuotationsRentalEquipment = QuotationsRentalEquipment::select('equipment_id')
        ->where('quotations_rental_id','=',$rental_deliver_note->quotation_rental_id)
        ->withCount(['RenatlDeliverNoteCheckEquipment'=>function($query)use($pdi_id){
            $query->where('pdi_id', '=', $pdi_id);
        }])
       ->get()->toArray();
       
        $counted_records = array_column($QuotationsRentalEquipment,'renatl_deliver_note_check_equipment_count');
       if(in_array(0,$counted_records)){
        return $this->sendResponse([], 'Updated Successfully');
        }else{
       
        $rental_neliver_note_update = RentalDeliverNote::where('prior_delivery_inspection_id','=',$pdi_id)->first();
        $data['rental_neliver_note_update'] = $rental_neliver_note_update;
        $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
         ->with(['assinged_user_rdn_assigne' => function ($query) use ($assigne_id) {
            $query->select('user_id')->where('delivery_note_id','=', $assigne_id);
        }])
        ->get();

            // $rental_neliver_note_update->prior_delivery_inspection_id = $pdi_id;
            // $rental_neliver_note_update->signed = $request->signed;
            // $rental_neliver_note_update->id_no = $request->id_no;
            // $rental_neliver_note_update->issued_form = $request->issued_form;
            // $rental_neliver_note_update->on_date = $request->on_date;
            // $rental_neliver_note_update->attorney_no = $request->attorney_no;
            // $rental_neliver_note_update->authorized_no = $request->authorized_no;
            // $rental_neliver_note_update->dated = $request->dated;
            // $rental_neliver_note_update->todays_date = $request->todays_date;
            // $rental_neliver_note_update->note = $request->note;
            // $rental_neliver_note_update->save();
    
        //     DeliveryNoteAssignes::where('delivery_note_id','=',$pdi_id)->delete();
        //     $assignee = $request->assignee;
        //     if($assignee){
        //     foreach ($assignee as $assignes) {
        //         $delivery_note_assignes = new DeliveryNoteAssignes();
        //         $delivery_note_assignes->delivery_note_id = $pdi_id;
        //         $delivery_note_assignes->user_id = $assignes;
        //         $delivery_note_assignes->status = 1;
        //         $delivery_note_assignes->save();
        //     }
        // }
        // $data['rental_neliver_note_update'] = $rental_neliver_note_update;
        return $data;
    }
    //     return $this->sendResponse([], 'Updated Successfully');
    }

    public function UpdateAssigneesRDN(Request $request){
        $rdn_id = $request->rdn_id;
        $assignee = $request->assignee;
            $assignee = $request->assignee;
            $rental_neliver_note_update = RentalDeliverNote::where('id','=',$rdn_id)->first();
            $rental_neliver_note_update->prior_delivery_inspection_id = $rdn_id;
            $rental_neliver_note_update->signed = $request->signed;
            $rental_neliver_note_update->id_no = $request->id_no;
            $rental_neliver_note_update->issued_form = $request->issued_form;
            $rental_neliver_note_update->on_date = $request->on_date;
            $rental_neliver_note_update->attorney_no = $request->attorney_no;
            $rental_neliver_note_update->authorized_no = $request->authorized_no;
            $rental_neliver_note_update->dated = $request->dated;
            $rental_neliver_note_update->todays_date = $request->todays_date;
            $rental_neliver_note_update->note = $request->note;
            $rental_neliver_note_update->save();
            DeliveryNoteAssignes::where('delivery_note_id','=',$rdn_id)->delete();
            if($assignee){
            foreach ($assignee as $assignes) {
                $delivery_note_assignes = new DeliveryNoteAssignes();
                $delivery_note_assignes->delivery_note_id = $rdn_id;
                $delivery_note_assignes->user_id = $assignes;
                $delivery_note_assignes->status = 1;
                $delivery_note_assignes->save();
            }
        }
        return $this->sendResponse([], 'Updated Successfully');
    }

    public function ViewRDNData(Request $request)
    {
        // $request->all();
        $rdn_id = $request->rdn_id;
        $data['view_id'] = $rdn_id;
        $rental_deliver_note = RentalDeliverNote::where('prior_delivery_inspection_id', '=', $rdn_id)->first();
        $quotation_renal_idd = $rental_deliver_note->quotation_rental_id;
        $data['client_data'] = Client::select('id', 'name', 'email')->where('id', '=', $rental_deliver_note->client_id)->first();
        $data['rental_quotation'] =  RentalQuotationEquipment::select('id','equipment_id','quantity','site_location')->where('quotation_id', $rental_deliver_note->rental_quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment', 'EquipmentStatusID');
            }])
            ->with(['QuotationsRentalRquipmentPrice' => function ($query) use($quotation_renal_idd){
                $query->select('id', 'equipment_id', 'price')->where('quotations_rental_id','=',$quotation_renal_idd);
            }])
        ->get();
        return $data;
    }

    public function ChangeRDNAssigneStatus(Request $request)
    {
		
        $reject_note = $request->reject_note;
        $rdn_id = $request->rdn_id;
        $status = $request->status;
        $user_id = Auth::User()->id;
        $prior_delivery_assigne = DeliveryNoteAssignes::where('delivery_note_id', '=', $rdn_id)->where('user_id', '=', $user_id)->first();
        if ($prior_delivery_assigne) {
            $prior_delivery_assigne->status = $status;
            $prior_delivery_assigne->save();
            $statuses =  DeliveryNoteAssignes::select('status')->where('delivery_note_id', '=', $rdn_id)->get()->toArray();
            $statuses_ids = array_column($statuses, 'status');
            $rqr_status_approved = in_array(1, $statuses_ids) ? 1 : 2;
            $rqr_status_cancle = in_array(3, $statuses_ids) ? 3 : $rqr_status_approved;
            if ($rqr_status_approved === 2) {
                $update_status = RentalDeliverNote::where('prior_delivery_inspection_id', '=', $rdn_id)->first();
                $update_status->status = 2;
                $update_status->save();
            } else {
                $update_status = RentalDeliverNote::where('prior_delivery_inspection_id', '=', $rdn_id)->first();
                $update_status->status = 1;
                $update_status->save();
            }
            if ($rqr_status_cancle === 3) {
                if ($status == 3) {
                    if ($reject_note) {
                        $prior_delivery_assigne->reject_note = $reject_note;
                        $prior_delivery_assigne->save();
                    }
                }
                $update_status = RentalDeliverNote::where('prior_delivery_inspection_id', '=', $rdn_id)->first();
                $update_status->status = 3;
                $update_status->save();
            }
            return $this->sendResponse([], 'Status Updated');
        } else {
            return $this->sendError([], 'You Are Not Authorized to Change Status');
        }
    }

    public function ViewRDNAssigne(Request $request)
    {
        $id = $request->rdn_id;
        $rental_delivery_assigne = DeliveryNoteAssignes::where('delivery_note_id', $id)
            ->with(['AssigneUserNameRDN' => function ($query) {
                $query->select('id', 'user_name');
            }])
            ->get();
        return $rental_delivery_assigne;
    }

    public function GetOffHireData(Request $request)
    {
        $rental_delivery_note = Client::select(
            "clients.id",
            "clients.name",
            "rental_delivery_note.*"
        )
            ->join("rental_delivery_note", "rental_delivery_note.client_id", "=", "clients.id")
            ->where('rental_delivery_note.status', '=', 2)->get();
        foreach ($rental_delivery_note as $rental_delivery_note_list) {
            
            $previous_data = OffHireList::where('rental_delivery_note_id', '=', $rental_delivery_note_list->id)->first();
            if ($previous_data) {
                if ($previous_data->rental_delivery_note_id === $rental_delivery_note_list->id) {
                    //data is already added so if condation is just for checking
                } else {
                    $quotation_rental = new OffHireList();
                    $quotation_rental->rental_quotation_id = $rental_delivery_note_list->rental_quotation_id;
                    $quotation_rental->quotation_rental_id = $rental_delivery_note_list->quotation_rental_id;
                    $quotation_rental->rental_delivery_order_id = $rental_delivery_note_list->rental_delivery_order_id;
                    $quotation_rental->prior_delivery_inspection_id = $rental_delivery_note_list->prior_delivery_inspection_id;
                    $quotation_rental->rental_delivery_note_id = $rental_delivery_note_list->id;
                    // rental_delivery_note_id
                    $quotation_rental->client_id = $rental_delivery_note_list->client_id;
                    $quotation_rental->total_basic_price = $rental_delivery_note_list->total_basic_price;
                    $quotation_rental->status = 1;
                    $quotation_rental->save();
                }
            } else {
                $quotation_rental = new OffHireList();
                    $quotation_rental->rental_quotation_id = $rental_delivery_note_list->rental_quotation_id;
                    $quotation_rental->quotation_rental_id = $rental_delivery_note_list->quotation_rental_id;
                    $quotation_rental->rental_delivery_order_id = $rental_delivery_note_list->rental_delivery_order_id;
                    $quotation_rental->prior_delivery_inspection_id = $rental_delivery_note_list->prior_delivery_inspection_id;
                    $quotation_rental->rental_delivery_note_id = $rental_delivery_note_list->id;
                    $quotation_rental->client_id = $rental_delivery_note_list->client_id;
                    $quotation_rental->total_basic_price = $rental_delivery_note_list->total_basic_price;
                    $quotation_rental->status = 1;
                    $quotation_rental->save();
            }
        }
        // $data['rental_quotations_approved_data'] = $rental_quotations_data;
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "off_hire_list.*"
        )
            ->join("off_hire_list", "off_hire_list.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status)) {
            $user_list->orWhere('off_hire_list.status', '=', $request->status);
        }
        // search by status
        if (isset($request->start_date) && $request->end_date) {
            $user_list->whereBetween('off_hire_list.created_at', [$request->start_date, $request->end_date]);
        }
        // search by quotation rental id base
        if (isset($request->id)) {
            $user_list->orWhere('off_hire_list.rental_quotation_id', '=', $request->id);
        }
        return response()->json($user_list->paginate((int)$per_page,), 200);
    }

    public function EditOFFHIREForm(Request $request)
    {
       $date = date('d-M-Y');
        //  $request->all();
        $rdn_id = $request->rdn_id;
        $data['date'] = $date;
        $data['rdn_id'] = $rdn_id; 
        $off_hire_list = OffHireList::where('rental_delivery_note_id','=',$rdn_id)->first();
        $data['Client'] = Client::select('id','name')->where('id','=',$off_hire_list->client_id)->first();
        // return $off_hire_list->rental_quotation_id;
        $data['rental_quotation'] =  RentalQuotationEquipment::select('id','equipment_id','hire_period_start','hire_period_end')->where('quotation_id', $off_hire_list->rental_quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment', 'PlateNumber');
        }])
        ->with(['off_hire_equpment_data'=>function($qrr) use($rdn_id){
            $qrr->select('equipment_id','remarks','additional_note')->where('rdn_id','=',$rdn_id);
        }])
        ->get();
        // return $off_hire_list->rental_delivery_order_id;
        $data['RentalDeliveryOrder'] = RentalDeliveryOrder::select('id','lpo_number','expected_hire_period_start','todays_date')->where('id','=',$off_hire_list->rental_delivery_order_id)->first();
         
        $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
          ->with(['assinged_user_off_hire_assigne' => function ($query) use ($rdn_id) {
              $query->select('user_id')->where('off_hire_id','=',$rdn_id);
        }])
          ->get();
        return $data;
    }

    public function UpdateOFFHIREForm(Request $request)
    {
        // $request->rdn_id
        OffHireEquipment::where('rdn_id','=',$request->rdn_id)->delete();
        $off_hire_list = OffHireList::where('rental_delivery_note_id','=',$request->rdn_id)->first();      
        foreach($request->equipment as $equpment_data)
        {
            $off_hire_equipment = new OffHireEquipment();
            $off_hire_equipment->off_hire_id = $off_hire_list->id;
            $off_hire_equipment->rdn_id = $request->rdn_id;
            $off_hire_equipment->equipment_id = $equpment_data['equipment_id'];
            $off_hire_equipment->remarks = $equpment_data['remarks'];
            $off_hire_equipment->additional_note = $equpment_data['additional_note'];
            $off_hire_equipment->save();
        }
        OffHireAssignes::where('off_hire_id','=',$request->rdn_id)->delete();
        $assignee = $request->assignee;
        foreach ($assignee as $assignes) {
            $rental_quotation_assigne = new OffHireAssignes();
            $rental_quotation_assigne->off_hire_id =$request->rdn_id;
            $rental_quotation_assigne->user_id = $assignes;
            $rental_quotation_assigne->status = 1;  
            $rental_quotation_assigne->save();
        }
        return $this->sendResponse([], 'Update Successfully');
    }

    public function ViewOFFHIREData(Request $request)
    {
        // $request->all();
        $rdn_id = $request->rdn_id;
        $data['view_id'] = $rdn_id;
        $off_hire_list = OffHireList::where('rental_delivery_note_id','=',$rdn_id)->first();
        $data['client_data'] = Client::select('id', 'name', 'email')->where('id', '=', $off_hire_list->client_id)->first();
        $quotation_rental_id_price = $off_hire_list->quotation_rental_id;
        $data['rental_quotation'] =  RentalQuotationEquipment::select('id','equipment_id','quantity','site_location')->where('quotation_id', $off_hire_list->rental_quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment', 'EquipmentStatusID');
            }])
            ->with(['QuotationsRentalRquipmentPrice' => function ($querysas) use($quotation_rental_id_price) {
                $querysas->select('id', 'equipment_id', 'price')->where('quotations_rental_id','=',$quotation_rental_id_price);
            }])
        ->get();
        return $data;
    }

    public function ViewOffHireAssigne(Request $request)
    {
        $id = $request->rdn_id;
        $rental_quotation_assigne = OffHireAssignes::where('off_hire_id', $id)
            ->with(['AssigneUserNameOffHire' => function ($query) {
                $query->select('id', 'user_name');
            }])
            ->get();
        return $rental_quotation_assigne;
    }

    public function ChangeOFFHIREAssigneStatus(Request $request)
    {
        $request->all();
        $reject_note = $request->reject_note;
        $rdn_id = $request->rdn_id;
        $status = $request->status;
        $user_id = Auth::User()->id;
        $off_hire_assignes = OffHireAssignes::where('off_hire_id', '=', $rdn_id)->where('user_id', '=', $user_id)->first();
        if ($off_hire_assignes) {
            $off_hire_assignes->status = $status;
            $off_hire_assignes->save();
            $statuses =  OffHireAssignes::select('status')->where('off_hire_id', '=', $rdn_id)->get()->toArray();
            $statuses_ids = array_column($statuses, 'status');
            $offhire_status_approved = in_array(1, $statuses_ids) ? 1 : 2;
            $rqr_status_cancle = in_array(3, $statuses_ids) ? 3 : $offhire_status_approved;
            if ($offhire_status_approved === 2) {
                $update_status = OffHireList::where('rental_delivery_note_id', '=', $rdn_id)->first();
                $update_status->status = 2;
                $update_status->final_approve = 2;
                $update_status->save();
            } else {
                $update_status = OffHireList::where('rental_delivery_note_id', '=', $rdn_id)->first();
                $update_status->status = 1;
                $update_status->final_approve = 1;
                $update_status->save();
            }
            if ($rqr_status_cancle === 3) {
                if ($status == 3) {
                    if ($reject_note) {
                        $off_hire_assignes->reject_note = $reject_note;
                        $off_hire_assignes->save();
                    }
                }
                $update_status = OffHireList::where('rental_delivery_note_id', '=', $rdn_id)->first();
                $update_status->status = 3;
                $update_status->final_approve = 4;
                $update_status->save();
            }
            return $this->sendResponse([], 'Status Updated');
        } else {
            return $this->sendError([], 'You Are Not Authorized to Change Status');
        }
    }

    public function ChangeFinalApproveOffHire(Request $request)
    {
       $request->all();

    //    rdo_id
        $equipment_id_array = OffHireEquipment::where('off_hire_id','=',$request->rdo_id)->pluck('equipment_id');
        $final_approve = OffHireList::where('rental_delivery_note_id','=',$request->rdo_id)->where('final_approve','=',$request->final_approve)->first();
        if($final_approve){
            $final_approve->final_approve = 4;
            $final_approve->save();
            MCRList::where('rental_delivery_order_id','=',$final_approve->rental_delivery_order_id)->delete();
            $mcrlist = new MCRList();
            $mcrlist->rental_quotation_id = $final_approve->rental_quotation_id;
            $mcrlist->quotation_rental_id = $final_approve->quotation_rental_id;
            $mcrlist->rental_delivery_order_id = $final_approve->rental_delivery_order_id;
            $mcrlist->prior_delivery_inspection_id = $final_approve->prior_delivery_inspection_id;
            $mcrlist->rental_delivery_note_id = $final_approve->rental_delivery_note_id;
            $mcrlist->off_hire_id = $final_approve->id;
            $mcrlist->client_id = $final_approve->client_id;
            $mcrlist->total_basic_price = $final_approve->total_basic_price;
            $mcrlist->status = 1;
            $mcrlist->save();

            $equipment_booked = Equipment::select('id','Equipment')->whereIn('id',$equipment_id_array)->get();
                foreach($equipment_booked as $bookedee){
                    $bookedee->equipment_booked = 1;
                    $bookedee->save();
                }

            return $this->sendResponse([], 'Status Updated');
        }else{
            return $this->sendError([], 'Status Already Approved');
        }
    }

    public function GetMCRData(Request $request)
    {
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "mcr_list.*"
        )
            ->join("mcr_list", "mcr_list.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status)) {
            $user_list->orWhere('mcr_list.status', '=', $request->status);
        }
        // search by status
        if (isset($request->start_date) && $request->end_date) {
            $user_list->whereBetween('mcr_list.created_at', [$request->start_date, $request->end_date]);
        }
        return response()->json($user_list->paginate((int)$per_page,), 200);
    }

    public function EditMCRFormStepOne(Request $request)
    {
        $request->all();   
        $request->off_hire_id;
        $data['off_hire_id'] = $request->off_hire_id;
        $client_id = MCRList::select('client_id','timee','datee')->where('off_hire_id','=',$request->off_hire_id)->first();
        $data['date_time'] = $client_id;
        $data['client_record'] = Client::select('name','phone')->where('id','=',$client_id->client_id)->first(); //client records
        $mcr_list = MCRList::where('off_hire_id','=',$request->off_hire_id)->first(); //get mcr data and update new records 
        $off_hire_id = $request->off_hire_id;
        // $data['attachment'] = RentalDeliveryOrder::select('id','quotation_rental_id','attachment')
        // ->where('quotation_rental_id','=',$mcr_list->quotation_rental_id)->first();
        $data['Equpment_Records'] = RentalDeliveryOrderEqupment::select('id','accessories','equipment_id')
        ->where('quotation_rental_id','=',$mcr_list->quotation_rental_id)
        ->with(['MCREquipmentAttachment'=>function($qrry) use($off_hire_id){
            $qrry->select('id','off_hire_id','equipment_id','note','attachment')->where('off_hire_id','=',$off_hire_id);
        }])
        ->with(['DeliveryNoteRecord'=>function($query){
            $query->select('id','pdi_id','equipment_id','services_meter_reading');
        }])
        ->with(['equipment_records'=>function($query){
            $query->select('id','Equipment','MachineMakerID','MachineModel','SerialNumber','PlateNumber','PlantNumber')
            ->with(['MachineMakerRecord'=>function($eqp){
                $eqp->select('id','machineMaker');
            }]);
        }])
        ->get();
        return $data;
    }

    public function EditMCRFormStepTwo(Request $request)
    {
        $request->all();
        $off_hire_id = $request->off_hire_id;
        $equipment_id = $request->equipment_id;
        $rental_quotation = MCRList::select('id','quotation_rental_id','rental_quotation_id','prior_delivery_inspection_id','rental_delivery_note_id','client_id')->where('off_hire_id','=',$off_hire_id)->first();
        $data['leassor_name'] = DeliveryNoteEquipment::select('pdi_id','equipment_id','leassor_name')->where('equipment_id','=',$equipment_id)
        ->where('pdi_id','=',$rental_quotation->prior_delivery_inspection_id)->first();
        $data['position'] = RentalQuotation::select('id','position')->where('id','=',$rental_quotation->rental_quotation_id)->first();
        $data['leassor_platenumber'] = Equipment::select('id','PlateNumber')->where('id','=',$equipment_id)->first();
        $data['leassee_name'] = Client::select('id','name')->where('id','=',$rental_quotation->client_id)->first();
        $final_status = Equipment::select('id','final_status',)->where('id', '=', $equipment_id)->first();
        $equipment_data = Equipment::select('id','YOM', 'Capacity', 'Equipment', 'PurchaseOrderNumber', 'MachineModel', 'SupplierPlantNumber', 'SerialNumber', 'HourlyRate', 'DailyRate',
        'WeeklyRate', 'PlantNumber', 'PlateNumber', 'PlateExpiry')->where('id', '=', $equipment_id)->first();
        // $data['leassor_and_leassee_data'] = RentalDeliverNote::select('leassor_name','leassee_name','leassee_position',)->where('quotation_rental_id','=',$rental_quotation->quotation_rental_id)->first();
        // $data['leassor_position'] = RentalQuotation::select('position')->where('id','=',$rental_quotation->rental_quotation_id)->first();
        // $data['leassor_platenumber'] = Equipment::select('PlateNumber')->where('id','=',$equipment_id)->first();
       $data['rental_and_finance_data'] = MCRListEquipment::where('mcr_list_id','=',$rental_quotation->id)
        ->where('off_hire_id','=',$off_hire_id)
        ->where('equipment_id','=',$equipment_id)->first();
        $inspectionlist_data_categort_one = InspectionLlist::select('id','name')->where('cat_id', '=', 1)
            ->with(['MCRSelected' => function ($query) use ($equipment_id, $off_hire_id) {
                $query->select('id', 'inspection_list_id', 'arkad', 'owner')
                ->where('off_hire_id', '=', $off_hire_id)->where('equipment_id', '=', $equipment_id)
                ->where('category_id', '=', 1);
            }])
        ->get();
        $inspectionlist_data_categort_two = InspectionLlist::select('id','name')->where('cat_id', '=', 1)
            ->with(['MCRSelected' => function ($query) use ($equipment_id, $off_hire_id) {
                $query->select('id', 'inspection_list_id', 'arkad', 'owner')
                ->where('off_hire_id', '=', $off_hire_id)->where('equipment_id', '=', $equipment_id)
                ->where('category_id', '=', 2);
            }])
        ->get();
        $inspectionlist_data_categort_three = InspectionLlist::select('id','name')->where('cat_id', '=', 1)
            ->with(['MCRSelected' => function ($query) use ($equipment_id, $off_hire_id) {
                $query->select('id', 'inspection_list_id', 'arkad', 'owner')
                ->where('off_hire_id', '=', $off_hire_id)->where('equipment_id', '=', $equipment_id)
                ->where('category_id', '=', 3);
            }])
        ->get();
        $QuotationsRentalEquipment = QuotationsRentalEquipment::select('equipment_id')
         ->where('quotations_rental_id','=',$rental_quotation->quotation_rental_id)
         ->withCount(['MCRCheckedEquipment'=>function($query)use($off_hire_id){
             $query->where('off_hire_id', '=', $off_hire_id);
         }])
        ->get()->toArray();
        $counted_records = array_column($QuotationsRentalEquipment,'m_c_r_checked_equipment_count');
         if(in_array(0,$counted_records)){
           
        $data['off_hire_id'] = $off_hire_id;
        $data['equipment_id'] = $equipment_id;
        $data['final_status'] =  $final_status;
        $data['equipment_data'] =  $equipment_data;
        $data['inspectionlist_data_categort_one'] =  $inspectionlist_data_categort_one;
        $data['inspectionlist_data_categort_two'] =  $inspectionlist_data_categort_two;
        $data['inspectionlist_data_categort_three'] =  $inspectionlist_data_categort_three;
        return $data;
         }else{
            $assigne = User::select('id', 'user_name')->where('status', '!=', 0)
            ->with(['assinged_user_mcr_list_assignes' => function ($query) use ($off_hire_id) {
                $query->select('user_id')->where('off_hire_id','=',$off_hire_id);
            }])
        ->get();
        $data['off_hire_id'] = $off_hire_id;
        $data['equipment_id'] = $equipment_id;
        $data['final_status'] =  $final_status;
        $data['equipment_data'] =  $equipment_data;
        $data['inspectionlist_data_categort_one'] =  $inspectionlist_data_categort_one;
        $data['inspectionlist_data_categort_two'] =  $inspectionlist_data_categort_two;
        $data['inspectionlist_data_categort_three'] =  $inspectionlist_data_categort_three;
        $data['assigne'] = $assigne;
        return $data;
        }
    }

    public function UpdateMCRForm(Request $request)
    {
        
        // return $request->all();
        $final_status = $request->final_status;
        $off_hire_id = $request->off_hire_id;
        $equipment_id = $request->equipment_id;
        $datee = $request->datee;
        $timee = $request->timee;
        $mcr_id = MCRList::select('id')->where('off_hire_id','=',$off_hire_id)->first();
        $chane_final_status = Equipment::where('id','=',$equipment_id)->first();
        $chane_final_status->final_status = $final_status;
        $chane_final_status->save();
        MCRListEquipment::where('mcr_list_id','=',$mcr_id->id)
        ->where('off_hire_id','=',$off_hire_id)
        ->where('equipment_id','=',$equipment_id)->delete();
        $mcr_equipment_list =  new MCRListEquipment();
        $mcr_equipment_list->mcr_list_id = $mcr_id->id;
        $mcr_equipment_list->off_hire_id = $off_hire_id;
        $mcr_equipment_list->equipment_id = $equipment_id;
        $mcr_equipment_list->equipment_condation = $final_status;
        $mcr_equipment_list->note = $request->note;
        $mcr_equipment_list->rental_dept = $request->rental_dept;
        $mcr_equipment_list->rental_dept_name = $request->rental_dept_name;

        if ($request->hasFile('attachment')) {
            $image = $request->file('attachment');
            $ext = $image->extension();
            $image_name = time() . '.' . $ext;
            $imagePath = public_path('/images/' . $mcr_equipment_list->attachment);
            if ($mcr_equipment_list->attachment) {
                unlink($imagePath);
            }
            $destinationPath = public_path() . '/images';
            $image->move($destinationPath, $image_name);
            $mcr_equipment_list->attachment = $image_name;
        }

        $mcr_equipment_list->rental_dept_position = $request->rental_dept_position;
        $mcr_equipment_list->rental_dept_singature = $request->rental_dept_singature;
        $mcr_equipment_list->finance_dept = $request->finance_dept;
        $mcr_equipment_list->finance_dept_name = $request->finance_dept_name;
        $mcr_equipment_list->finance_dept_position = $request->finance_dept_position;
        $mcr_equipment_list->finance_dept_signature = $request->finance_dept_signature;
        $mcr_equipment_list->save();
        $data = $request->all();
        $off_hire_id_array = $data['data'][0]['off_hire_id'] ;
        $equipment_id_array = $data['data'][0]['equipment_id'] ;
        $category_id_array = $data['data'][0]['category_id'] ;
        MCRListChecked::where('equipment_id','=',$equipment_id)
        ->where('off_hire_id','=',$off_hire_id_array)->where('category_id','=',$category_id_array)->delete();
        MCRListChecked::insert($data['data']); 
        
        $assignee = $request->assignee;
        if($assignee){
            MCRListAssignees::where('off_hire_id', '=', $off_hire_id)->delete();
        foreach ($assignee as $assignes) {
            $rental_quotation_assigne = new MCRListAssignees();
            $rental_quotation_assigne->off_hire_id = $off_hire_id;
            $rental_quotation_assigne->user_id = $assignes;
            $rental_quotation_assigne->status = 1;
            $rental_quotation_assigne->save();
        }
    }
        
    $arr = array(2,3,4);
    $mcr_list_equipment_status = MCRListEquipment::select('equipment_condation')->where('off_hire_id','=',$off_hire_id)->get()->Toarray();
    $statuses_ids = array_column($mcr_list_equipment_status, 'equipment_condation');
    
    $condition_array = array();
    foreach($arr as $statuss){
        $rqr_status_approved = in_array($statuss, $statuses_ids) ? 2 : 1;
        array_push($condition_array,$rqr_status_approved);
    }
    $final =  in_array(2, $condition_array) ? 2 : 1;
    if($final == 1){
      $update_condition = MCRList::where('off_hire_id','=',$off_hire_id)->first();
      $update_condition->final_equp_condition = $final;
      $update_condition->save();
    }else{
        $update_condition = MCRList::where('off_hire_id','=',$off_hire_id)->first();
        $update_condition->final_equp_condition = $final;
        $update_condition->save(); 
    }
    
        // return $this->sendResponse([], 'Record Updated');

          $rental_quotation = MCRList::select('id','quotation_rental_id',)->where('off_hire_id','=',$off_hire_id)->first();
             $QuotationsRentalEquipment = QuotationsRentalEquipment::select('equipment_id')
        ->where('quotations_rental_id','=',$rental_quotation->quotation_rental_id)
        ->withCount(['MCRCheckedEquipment'=>function($query)use($off_hire_id){
            $query->where('off_hire_id', '=', $off_hire_id);
        }])
       ->get()->toArray();
       
       $counted_records = array_column($QuotationsRentalEquipment,'m_c_r_checked_equipment_count');
        if(in_array(0,$counted_records)){
            return $this->sendResponse([], 'Record Updated');
        }else{
           $assigne = User::select('id', 'user_name')->where('status', '!=', 0)
           ->with(['assinged_user_mcr_list_assignes' => function ($query) use ($off_hire_id) {
               $query->select('user_id')->where('off_hire_id','=',$off_hire_id);
           }])
       ->get();
       $data['assigne'] = $assigne;
       return $data;
       }

    }

    public function UpdateAssigneesMCR(Request $request){
        $rdo_id = $request->off_hire_id;
        $assignee = $request->assignee;
        if($assignee){
        MCRListAssignees::where('off_hire_id', '=', $rdo_id)->delete();
        foreach ($assignee as $assignes) {
            $rental_quotation_assigne = new MCRListAssignees();
            $rental_quotation_assigne->off_hire_id = $rdo_id;
            $rental_quotation_assigne->user_id = $assignes;
            $rental_quotation_assigne->status = 1;
            $rental_quotation_assigne->save();
        }
     }
        return $this->sendResponse([], 'Assignees Selected ');
    }

    public function ViewMCRAssigne(Request $request)
    {
         $request->all();
        $off_hire_id = $request->off_hire_id;
        $MCRListAssignees = MCRListAssignees::where('off_hire_id','=', $off_hire_id)
            ->with(['AssigneUserNameMCR' => function ($query) {
                $query->select('id', 'user_name');
            }])
            ->get();
        return $MCRListAssignees;
    }

    public function ViewMCRFormStepOne(Request $request)
    {
        $request->all();   
        $request->off_hire_id;
        $data['off_hire_id'] = $request->off_hire_id;
        $client_id = MCRList::select('client_id','timee','datee')->where('off_hire_id','=',$request->off_hire_id)->first();
        $data['date_time'] = $client_id;
        $data['client_record'] = Client::select('name','phone')->where('id','=',$client_id->client_id)->first(); //client records
        $mcr_list = MCRList::where('off_hire_id','=',$request->off_hire_id)->first(); //get mcr data and update new records 
        $data['attachment'] = RentalDeliveryOrder::select('id','quotation_rental_id','attachment')
        ->where('quotation_rental_id','=',$mcr_list->quotation_rental_id)->first();
        $data['Equpment_Records'] = RentalDeliveryOrderEqupment::select('id','accessories','equipment_id')
        ->where('quotation_rental_id','=',$mcr_list->quotation_rental_id)
        ->with(['DeliveryNoteRecord'=>function($query){
            $query->select('id','pdi_id','equipment_id','services_meter_reading');
        }])
        ->with(['equipment_records'=>function($query){
            $query->select('id','Equipment','MachineMakerID','MachineModel','SerialNumber','PlateNumber','PlantNumber')
            ->with(['MachineMakerRecord'=>function($eqp){
                $eqp->select('id','machineMaker');
            }]);
        }])
        ->get();
        return $data;
    }

    public function ViewMCRFormStepTwo(Request $request)
    {
        $off_hire_id = $request->off_hire_id;
        $equipment_id = $request->equipment_id;
        $rental_quotation = MCRList::select('id','quotation_rental_id','rental_quotation_id','prior_delivery_inspection_id','client_id')->where('off_hire_id','=',$off_hire_id)->first();
        $data['lesses_data'] = Client::select('id','name')->where('id','=',$rental_quotation->client_id)->first();
        $data['date_time'] = MCRList::select('timee','datee')->where('off_hire_id','=',$off_hire_id)->first();
        $final_status = Equipment::select('id','final_status',)->where('id', '=', $equipment_id)->first();
        $equipment_data = Equipment::select('id','MachineMakerID','YOM', 'Capacity', 'Equipment', 'PurchaseOrderNumber', 'MachineModel', 'SupplierPlantNumber', 'SerialNumber', 'HourlyRate', 'DailyRate',
        'WeeklyRate', 'PlantNumber', 'PlateNumber', 'PlateExpiry')->where('id', '=', $equipment_id)
        ->with(['MachineMakerRecord'=>function($query){
            $query->select('id','machineMaker');
        }])
        ->first();
        // $data['leassor_and_leassee_data'] = RentalDeliverNote::select('leassor_name','leassee_name','leassee_position',)->where('quotation_rental_id','=',$rental_quotation->quotation_rental_id)->first();
        $data['leassor_data'] = DeliveryNoteEquipment::select('leassor_name')->where('equipment_id','=',$equipment_id)->first();
        $data['leassor_position'] = RentalQuotation::select('position')->where('id','=',$rental_quotation->rental_quotation_id)->first();
        $data['leassor_platenumber'] = Equipment::select('PlateNumber')->where('id','=',$equipment_id)->first();
        
       $data['rental_and_finance_data'] = MCRListEquipment::where('mcr_list_id','=',$rental_quotation->id)
        ->where('off_hire_id','=',$off_hire_id)
        ->where('equipment_id','=',$equipment_id)->first();

        $inspectionlist_data_categort_one = InspectionLlist::select('id','name')->where('cat_id', '=', 1)
            ->with(['MCRSelected' => function ($query) use ($equipment_id, $off_hire_id) {
                $query->select('id', 'inspection_list_id', 'arkad', 'owner')
                ->where('off_hire_id', '=', $off_hire_id)->where('equipment_id', '=', $equipment_id)
                ->where('category_id', '=', 1);
            }])
        ->get();

        $inspectionlist_data_categort_two = InspectionLlist::select('id','name')->where('cat_id', '=', 1)
            ->with(['MCRSelected' => function ($query) use ($equipment_id, $off_hire_id) {
                $query->select('id', 'inspection_list_id', 'arkad', 'owner')
                ->where('off_hire_id', '=', $off_hire_id)->where('equipment_id', '=', $equipment_id)
                ->where('category_id', '=', 2);
            }])
        ->get();

        $inspectionlist_data_categort_three = InspectionLlist::select('id','name')->where('cat_id', '=', 1)
            ->with(['MCRSelected' => function ($query) use ($equipment_id, $off_hire_id) {
                $query->select('id', 'inspection_list_id', 'arkad', 'owner')
                ->where('off_hire_id', '=', $off_hire_id)->where('equipment_id', '=', $equipment_id)
                ->where('category_id', '=', 3);
            }])
        ->get();

        $data['final_status'] =  $final_status;
        $data['equipment_data'] =  $equipment_data;
        $data['inspectionlist_data_categort_one'] =  $inspectionlist_data_categort_one;
        $data['inspectionlist_data_categort_two'] =  $inspectionlist_data_categort_two;
        $data['inspectionlist_data_categort_three'] =  $inspectionlist_data_categort_three;
        return $data;
      
    }

    public function ChangeMCRAssigneStatus(Request $request)
    {
        $reject_note = $request->reject_note;
        $off_hire_id = $request->off_hire_id;
        $status = $request->status;
        $user_id = Auth::User()->id;
        $prior_delivery_assigne = MCRListAssignees::where('off_hire_id', '=', $off_hire_id)
        ->where('user_id', '=', $user_id)->first();
        if ($prior_delivery_assigne) {
            $prior_delivery_assigne->status = $status;
            $prior_delivery_assigne->save();
            $statuses =  MCRListAssignees::select('status')->where('off_hire_id', '=', $off_hire_id)->get()->toArray();
            $statuses_ids = array_column($statuses, 'status');
            $rqr_status_approved = in_array(1, $statuses_ids) ? 1 : 2;
            $rqr_status_cancle = in_array(3, $statuses_ids) ? 3 : $rqr_status_approved;
            if ($rqr_status_approved === 2) {
                $update_status = MCRList::where('off_hire_id', '=', $off_hire_id)->first();
                $update_status->status = 2;
                $update_status->save();
                // send check question form msr checked table to equipment mater data table 
                // Start code form here 
                $mcr_list_checked_record = MCRListChecked::where('off_hire_id','=',$off_hire_id)
                ->Where('arkad','=',2)->orWhere('owner','=',2)
                ->get();
                $new_data = array();
                foreach($mcr_list_checked_record as $key=>$val){
                    if($val->off_hire_id == $off_hire_id){
                        array_push($new_data,$val);
                    }else{
                        unset($mcr_list_checked_record[$key]);
                    }
                }
                // return $new_data;
                EquipMaintenanceSelected::where('off_hire_id','=',$off_hire_id)->delete();
                foreach($new_data as $mcr_list_checked_record_list){
                    $equip_maintenance_selected = new EquipMaintenanceSelected();
                    $equip_maintenance_selected->equipment_id = $mcr_list_checked_record_list->equipment_id;
                    $equip_maintenance_selected->inspection_list_id = $mcr_list_checked_record_list->inspection_list_id;
                    $equip_maintenance_selected->off_hire_id = $mcr_list_checked_record_list->off_hire_id;
                    $equip_maintenance_selected->category_id = $mcr_list_checked_record_list->category_id;
                    $equip_maintenance_selected->arkad = $mcr_list_checked_record_list->arkad;
                    $equip_maintenance_selected->owner = $mcr_list_checked_record_list->owner;
                    $equip_maintenance_selected->save();
                }
                // End code here 

            } else {
                $update_status = MCRList::where('off_hire_id', '=', $off_hire_id)->first();
                $update_status->status = 1;
                $update_status->save();
            }
            if ($rqr_status_cancle === 3) {
                if ($status == 3) {
                    if ($reject_note) {
                        $prior_delivery_assigne->reject_note = $reject_note;
                        $prior_delivery_assigne->save();
                    }
                }
                $update_status = MCRList::where('off_hire_id', '=', $off_hire_id)->first();
                $update_status->status = 3;
                $update_status->save();
            }
            return $this->sendResponse([], 'Status Updated');
        } else {
            return $this->sendError([], 'You Are Not Authorized to Change Status');
        }
    }

    public function GetEquMaintanceData(Request $request)
    {
        $mcr_list_data = Client::select(
            "clients.id",
            "clients.name",
            "mcr_list.*"
        )
            ->join("mcr_list", "mcr_list.client_id", "=", "clients.id")
            ->where('mcr_list.status', '=', 2)->get();
        foreach ($mcr_list_data as $mcr_list_record) {
            // RentalDeliverNote
             $previous_data = EquipMaintenance::where('mcr_list_id', '=', $mcr_list_record->id)->first();
            if ($previous_data) {
                // return $previous_data;
                if ($previous_data->mcr_list_id === $mcr_list_record->id) {
                    //data is already added so if condation is just for checking
                } else {
                    $equip_maintenance = new EquipMaintenance();
                    $equip_maintenance->rqr_id = $mcr_list_record->rental_quotation_id;
                    $equip_maintenance->qr_id = $mcr_list_record->quotation_rental_id;
                    $equip_maintenance->rdo_id = $mcr_list_record->rental_delivery_order_id;
                    $equip_maintenance->pdi_id = $mcr_list_record->prior_delivery_inspection_id;
                    $equip_maintenance->rdn_id = $mcr_list_record->rental_delivery_note_id;
                    $equip_maintenance->off_hire_id = $mcr_list_record->off_hire_id;
                    $equip_maintenance->mcr_list_id = $mcr_list_record->id;
                    $equip_maintenance->client_id = $mcr_list_record->client_id;
                    $equip_maintenance->total_basic_price = $mcr_list_record->total_basic_price;
                    $equip_maintenance->final_equp_condition = $mcr_list_record->final_equp_condition;
                    $equip_maintenance->status = 1;
                    $equip_maintenance->save();
                }
            } else {
                $equip_maintenance = new EquipMaintenance();
                $equip_maintenance->rqr_id = $mcr_list_record->rental_quotation_id;
                $equip_maintenance->qr_id = $mcr_list_record->quotation_rental_id;
                $equip_maintenance->rdo_id = $mcr_list_record->rental_delivery_order_id;
                $equip_maintenance->pdi_id = $mcr_list_record->prior_delivery_inspection_id;
                $equip_maintenance->rdn_id = $mcr_list_record->rental_delivery_note_id;
                $equip_maintenance->off_hire_id = $mcr_list_record->off_hire_id;
                $equip_maintenance->mcr_list_id = $mcr_list_record->id;
                $equip_maintenance->client_id = $mcr_list_record->client_id;
                $equip_maintenance->total_basic_price = $mcr_list_record->total_basic_price;
                $equip_maintenance->final_equp_condition = $mcr_list_record->final_equp_condition;
                $equip_maintenance->status = 1;
                $equip_maintenance->save();
            }
        }
        
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = Client::select(
            "clients.id",
            "clients.name",
            "equip_maintenance.mcr_list_id",
            "equip_maintenance.total_basic_price",
            "equip_maintenance.final_equp_condition",
            
        )
            ->join("equip_maintenance", "equip_maintenance.client_id", "=", "clients.id");
        // search by name
        if (isset($request->s) && !empty($request->s)) {
            $user_list->orWhere('name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        // search by status
        if (isset($request->status)) {
            $user_list->orWhere('equip_maintenance.status', '=', $request->status);
        }
        // search by status
        if (isset($request->start_date) && $request->end_date) {
            $user_list->whereBetween('equip_maintenance.created_at', [$request->start_date, $request->end_date]);
        }
        return response()->json($user_list->paginate((int)$per_page,), 200);
    }


    public function EditEQPMFormStepOne(Request $request)
    {
        $request->all();   
        $mcr_list_id = $request->mcr_list_id;
        $data['mcr_list_id'] = $mcr_list_id;
        $client_id = EquipMaintenance::select('client_id','qr_id','timee','datee')->where('mcr_list_id','=',$mcr_list_id)->first();
        $data['client_id_mentance'] = $client_id;
        $data['client_record_name_phone'] = Client::select('name','phone')->where('id','=',$client_id->client_id)->first(); //client records
        $mcr_list = MCRList::where('off_hire_id','=',$mcr_list_id)->first(); //get mcr data and update new records 

        $data['attachment'] = RentalDeliveryOrder::select('id','quotation_rental_id','attachment')
        ->where('quotation_rental_id','=',$client_id->qr_id)->first();

        $data['Equpment_Records'] = RentalDeliveryOrderEqupment::select('id','accessories','equipment_id')
        ->where('quotation_rental_id','=',$client_id->qr_id)
        ->with(['DeliveryNoteRecord'=>function($query){
            $query->select('id','pdi_id','equipment_id','services_meter_reading');
        }])
        ->with(['equipment_records'=>function($query){
            $query->select('id','Equipment','MachineMakerID','MachineModel','SerialNumber','PlateNumber','PlantNumber')
            ->with(['MachineMakerRecord'=>function($eqp){
                $eqp->select('id','machineMaker');
            }]);
            $query->with(['equip_maintenance_equipments_record'=>function($mantance){
                $mantance->select('equipment_id','note');
            }]);
        }])
        
        ->get();
        return $data;
    }

    public function EditEQPMFormStepTwo(Request $request)
    {
        $mcr_list_id = $request->mcr_list_id;
        $equipment_id = $request->equipment_id;
        $data['mcr_list_id'] = $mcr_list_id;
        $data['equipment_id'] = $equipment_id;
        $equipment_name = Equipment::select('id','Equipment')->where('id','=',$equipment_id)->first();
        $data['equipment_name'] = $equipment_name;
        $off_hire_id = EquipMaintenance::select('off_hire_id','client_id')->where('mcr_list_id','=',$mcr_list_id)->first();
        $data['off_hire_id'] = $off_hire_id->off_hire_id;
        $data['client_record_name'] = Client::select('id','name','email')->where('id','=',$off_hire_id->client_id)->first(); //client records   
        $data['equip_maintenance_selected_inspection'] = EquipMaintenanceSelected::where('off_hire_id','=',$off_hire_id->off_hire_id)
        // ->with(['equipment_masterdate_list'=>function($queryy){
        //     $queryy->select('id','Equipment');
        // }])
        ->with(['equipment_masterdate_list_name'=>function($qry){
            $qry->select('id','name');
        }])
        ->where('equipment_id','=',$equipment_id)->get();
        $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
        ->with(['assinged_user_equipment_masterdata' => function ($query) use ($mcr_list_id) {
            $query->select('user_id')->where('mcr_list_id','=', $mcr_list_id);
        }])
        ->get();
        return $data;
    }

    public function UpdateEQPMData(Request $request)
    {
        $timee = $request->timee;
        $datee = $request->datee;
        $mcr_list_id = $request->mcr_list_id;
        $first_equipment_id = $request->equipment_id;
        $note = $request->note;
        $data = $request->all();
        $insert_record = EquipMaintenance::where('mcr_list_id','=',$mcr_list_id)->first();
        $insert_record->timee = $timee; 
        $insert_record->datee = $datee; 
        $insert_record->save();

        $mcr_list_id_array = $data['data'][0]['off_hire_id'] ;
        $equipment_id_array = $data['data'][0]['equipment_id'] ;
        $category_id_array = $data['data'][0]['category_id'] ;
        $inspection_list_id = $data['data'][0]['inspection_list_id'] ;
        EquipMaintenanceSelected::where('equipment_id','=',$equipment_id_array)
        ->where('off_hire_id','=',$mcr_list_id_array)->delete();
        EquipMaintenanceSelected::insert($data['data']);

        // EquipMaintenanceAssignees::where('mcr_list_id', '=', $mcr_list_id)->delete();
        // $assignee = $request->assignee;
        // foreach ($assignee as $assignes) {
        //     $rental_quotation_assigne = new EquipMaintenanceAssignees();
        //     $rental_quotation_assigne->mcr_list_id = $mcr_list_id;
        //     $rental_quotation_assigne->user_id = $assignes;
        //     $rental_quotation_assigne->status = 1;
        //     $rental_quotation_assigne->save();
        // }

        EquipMaintenanceEquipments::where('mcr_list_id','=',$mcr_list_id)->where('equipment_id','=',$first_equipment_id)->delete();
        $update_note = new EquipMaintenanceEquipments();
        $update_note->mcr_list_id = $mcr_list_id;
        $update_note->equipment_id = $first_equipment_id;
        $update_note->note = $note;
        $update_note->save();

         $QuotationsRentalEquipment = QuotationsRentalEquipment::select('equipment_id')
         ->where('quotations_rental_id','=',$insert_record->qr_id)
         ->withCount(['EquipmantanceCheckEquipment'=>function($query)use($mcr_list_id){
             $query->where('mcr_list_id', '=', $mcr_list_id);
         }])
        ->get()->toArray();
        
         $counted_records = array_column($QuotationsRentalEquipment,'equipmantance_check_equipment_count');
        if(in_array(0,$counted_records)){
            return $this->sendResponse([], 'Updated Successfully');
         }else{
            $mcr_list_idd = $request->mcr_list_id;
            $dataa['mcr_list_idd'] = $mcr_list_idd;
            $dataa['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
            ->with(['assinged_user_equipment_masterdata' => function ($query) use ($mcr_list_id) {
                $query->select('user_id')->where('mcr_list_id','=', $mcr_list_id);
            }])->get();
            return $dataa;
     }

    }

    public function EqupMentanceAssignes(Request $request)
    {
        $mcr_list_id = $request->mcr_list_id;
        EquipMaintenanceAssignees::where('mcr_list_id', '=', $mcr_list_id)->delete();
        $assignee = $request->assignee;
       if($assignee){
            foreach ($assignee as $assignes) {
                $rental_quotation_assigne = new EquipMaintenanceAssignees();
                $rental_quotation_assigne->mcr_list_id = $mcr_list_id;
                $rental_quotation_assigne->user_id = $assignes;
                $rental_quotation_assigne->status = 1;
                $rental_quotation_assigne->save();
            }
       }
       return $this->sendResponse([], 'Updated Successfully');
    }

    public function ViewEQPMFormStepOne(Request $request)
    {
        $request->all();   
        $mcr_list_id = $request->mcr_list_id;
        $data['mcr_list_id'] = $mcr_list_id;
        $client_id = EquipMaintenance::select('client_id','qr_id','timee','datee')->where('mcr_list_id','=',$mcr_list_id)->first();
        $data['client_id_mentance'] = $client_id;
        $data['client_record_name_phone'] = Client::select('name','phone')->where('id','=',$client_id->client_id)->first(); //client records
        $mcr_list = MCRList::where('off_hire_id','=',$mcr_list_id)->first(); //get mcr data and update new records 

        $data['attachment'] = RentalDeliveryOrder::select('id','quotation_rental_id','attachment')
        ->where('quotation_rental_id','=',$client_id->qr_id)->first();

        $data['Equpment_Records'] = RentalDeliveryOrderEqupment::select('id','accessories','equipment_id')
        ->where('quotation_rental_id','=',$client_id->qr_id)
        ->with(['DeliveryNoteRecord'=>function($query){
            $query->select('id','pdi_id','equipment_id','services_meter_reading');
        }])
        ->with(['equipment_records'=>function($query){
            $query->select('id','Equipment','MachineMakerID','MachineModel','SerialNumber','PlateNumber','PlantNumber')
            ->with(['MachineMakerRecord'=>function($eqp){
                $eqp->select('id','machineMaker');
            }]);
            $query->with(['equip_maintenance_equipments_record'=>function($mantance){
                $mantance->select('equipment_id','note');
            }]);
        }])
        
        ->get();
        return $data;
    }


    public function ViewEQPMFormStepTwo(Request $request)
    {
        $mcr_list_id = $request->mcr_list_id;
        $equipment_id = $request->equipment_id;
        $data['mcr_list_id'] = $mcr_list_id;
        $data['equipment_id'] = $equipment_id;
        $equipment_name = Equipment::select('id','Equipment')->where('id','=',$equipment_id)->first();
        $data['equipment_name'] = $equipment_name;
        $off_hire_id = EquipMaintenance::select('off_hire_id','client_id')->where('mcr_list_id','=',$mcr_list_id)->first();
        $data['off_hire_id'] = $off_hire_id->off_hire_id;
        $data['client_record_name'] = Client::select('id','name','email')->where('id','=',$off_hire_id->client_id)->first(); //client records   
        $data['equip_maintenance_selected_inspection'] = EquipMaintenanceSelected::where('off_hire_id','=',$off_hire_id->off_hire_id)
        // ->with(['equipment_masterdate_list'=>function($queryy){
        //     $queryy->select('id','Equipment');
        // }])
        ->with(['equipment_masterdate_list_name'=>function($qry){
            $qry->select('id','name');
        }])
        ->where('equipment_id','=',$equipment_id)->get();
        $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
        ->with(['assinged_user_equipment_masterdata' => function ($query) use ($mcr_list_id) {
            $query->select('user_id')->where('mcr_list_id','=', $mcr_list_id);
        }])
        ->get();
        return $data;
    }

    public function ChangeEQPMssigneStatus(Request $request)
    {
        $reject_note = $request->reject_note;
        // $rdn_id = $request->mcr_list_id;
        $mcr_list_id = $request->mcr_list_id;
        $status = $request->status;
        $user_id = Auth::User()->id;
        $prior_delivery_assigne = EquipMaintenanceAssignees::where('mcr_list_id', '=', $mcr_list_id)->where('user_id', '=', $user_id)->first();
        if ($prior_delivery_assigne) {
            $prior_delivery_assigne->status = $status;
            $prior_delivery_assigne->save();
            $statuses =  EquipMaintenanceAssignees::select('status')->where('mcr_list_id', '=', $mcr_list_id)->get()->toArray();
            $statuses_ids = array_column($statuses, 'status');
            $rqr_status_approved = in_array(1, $statuses_ids) ? 1 : 2;
            $rqr_status_cancle = in_array(3, $statuses_ids) ? 3 : $rqr_status_approved;
            if ($rqr_status_approved === 2) {
                $update_status = EquipMaintenance::where('mcr_list_id', '=', $mcr_list_id)->first();
                $update_status->status = 2;
                $update_status->save();
            } else {
                $update_status = EquipMaintenance::where('mcr_list_id', '=', $mcr_list_id)->first();
                $update_status->status = 1;
                $update_status->save();
            }
            if ($rqr_status_cancle === 3) {
                if ($status == 3) {
                    if ($reject_note) {
                        $prior_delivery_assigne->reject_note = $reject_note;
                        $prior_delivery_assigne->save();
                    }
                }
                $update_status = EquipMaintenance::where('mcr_list_id', '=', $mcr_list_id)->first();
                $update_status->status = 3;
                $update_status->save();
            }
            return $this->sendResponse([], 'Status Updated');
        } else {
            return $this->sendError([], 'You Are Not Authorized to Change Status');
        }
    }

    public function ViewEQPMAssigne(Request $request)
    {
        $mcr_list_id = $request->mcr_list_id;
        $rental_delivery_assigne = EquipMaintenanceAssignees::where('mcr_list_id', $mcr_list_id)
            ->with(['AssigneUserNameEQPM' => function ($query) {
                $query->select('id', 'user_name');
            }])
            ->get();
        return $rental_delivery_assigne;
    }

    public function ChangeStatusToGood(Request $request)
    {
        $request->all();
        $mcr_list_id = $request->mcr_list_id;
        $change_to_good = $request->change_to_good;
        $equip_maintenance_record = EquipMaintenance::where('mcr_list_id', '=', $mcr_list_id)->first();
        $equip_maintenance_record->final_equp_condition = $change_to_good;
        $equip_maintenance_record->save();
        $qr_id = $equip_maintenance_record->qr_id;
        $quotations_rental_equipment_update_master_status = QuotationsRentalEquipment::select('equipment_id')->where('quotations_rental_id','=',$qr_id)->get()->pluck('equipment_id');
        $quotations_rental_equipment_update_master_status;
        $update_record = Equipment::select('id','final_status')->whereIn('id',$quotations_rental_equipment_update_master_status)->get();
        foreach($update_record as $changed){
            $changed->final_status = $change_to_good; 
            $changed->save();
        }
        return $this->sendResponse([], 'Status Updated');
    }

}