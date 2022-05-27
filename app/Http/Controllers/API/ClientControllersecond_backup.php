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
use App\Models\InspectionLlist;
use App\Models\OffHireList;
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
        //client id and client record
        $data['client'] = Client::where('id', '=', $rental_quotation_id->client_id)->first();
        //rental quotation equipment complete data
        $data['rentalquotationequipment'] = RentalQuotationEquipment::where('quotation_id', $id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment');
            }])
            ->get();
        // get equipments id form rental quotation equipment table
        // $equipments_id = RentalQuotationEquipment::select('equipment_id')
        //     ->where('quotation_id','=',$id)->get()->toArray();
        // $data['equipment'] = Equipment::select('Equipment')->whereIn('id',$equipments_id)->get();
        return $this->sendResponse($data, 'Success');
    }

    public function UpdateRQRData(Request $request)
    {
        $id = $request->id;
        $client = $request->client;

        $rental_quotation_id = RentalQuotation::where('id', '=', $id)->first();
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
            $rentanl_quotation_update->sp_name = $value['sp_name'];
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
            return $this->sendResponse([], 'Success');
        }
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

        // $user_list = Client::query();
        // if (isset($request->s) && !empty($request->s)) {
        //     $user_list->orWhere('client', 'like', '%' . $request->s . '%');
        //     $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        // }
        // if (isset($request->status)) {
        //     $user_list->where('status', '=', $request->status);
        // }
        // $user_list->orderBy('id', 'desc');
        // return $this->sendResponse($user_list->get(), 'Success');
    }

    public function SubmitRQR(Request $request)
    {
        //         return ($request->all());
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
                'sp_name' => $v['sp_name'],
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
        // return $request->all();
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
        $data['equipments'] = Equipment::select('id', 'Equipment')
            ->where('IsDeleted', '=', 0)
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
        $client_id = RentalQuotation::select('client_id')->where('id', '=', $id)->first();
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
                    $query->select('user_id');
                }])
                ->get();
            return $this->sendResponse($data, 'Success');
        }
    }

    public function UpdateQRData(Request $request)
    {
        $id = $request->id;
        $client_id = RentalQuotation::where('id', '=', $id)->first();
        $quotation_rental = QuotationRental::where('rental_quotation_id', '=', $id)->where('client_id', '=', $client_id->client_id)->first();
        $quotation_rental->attn_no = $request->attn_no;
        $quotation_rental->attan_name = $request->attan_name;
        $quotation_rental->reference_no = $request->reference_no;
        $quotation_rental->terms_condations = utf8_encode((string)$request->term_and_condations);

        $quotation_rental->save();
        QuotationsRentalEquipment::where('quotations_rental_id', '=', $quotation_rental->id)->delete();
        $equipment_data = array();
        foreach ($request->equipment as  $k => $v) {
            $v['rental_quotations_id'] = $id;
            $v['quotations_rental_id'] = $quotation_rental->id;
            $v['duration_rate'] = isset($v['duration_rate']) ? $v['duration_rate'] : null;
            $v['price'] = isset($v['price']) ? $v['price'] : null;
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

        return $this->sendResponse([], 'Updated');
    }

    public function ViewQRData(Request $request)
    {
        $quotation_id = $request->id;
        $data['view_id'] = $quotation_id;
        $client_id = RentalQuotation::where('id', '=', $quotation_id)->first();
        $data['client_data'] = Client::where('id', '=', $client_id->client_id)->first();

        $data['rental_quotation'] =  RentalQuotationEquipment::where('quotation_id', $quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment', 'EquipmentStatusID');
            }])->with(['QuotationsRentalRquipmentPrice' => function ($query) {
                $query->select('id', 'equipment_id', 'price');
            }])
            ->get();
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
                $update_status->save();
            } else {
                $update_status = QuotationRental::where('rental_quotation_id', '=', $quotation_id)->first();
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
                $update_status = QuotationRental::where('rental_quotation_id', '=', $quotation_id)->first();
                $update_status->status = 3;
                $update_status->save();
            }
            return $this->sendResponse([], 'Status Updated');
        } else {
            return $this->sendError([], 'You Are Not Authorized to Change Status');
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
                    $quotation_rental->save();
                }
            } else {
                $quotation_rental = new QuotationRental();
                $quotation_rental->rental_quotation_id = $rental_quotation_list->id;
                $quotation_rental->client_id = $rental_quotation_list->client_id;
                $quotation_rental->status = 1;
                $quotation_rental->save();
            }
        }
        // $data['rental_quotations_approved_data'] = $rental_quotations_data;
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
        // dd('skgahs');
        $rental_quotations_data = Client::select(
            "clients.id",
            "clients.name",
            "quotations_rental.*"
        )
            ->join("quotations_rental", "quotations_rental.client_id", "=", "clients.id")
            ->where('quotations_rental.status', '=', 2)->get();
        foreach ($rental_quotations_data as $rental_quotation_list) {
            $basic_price = QuotationsRentalEquipment::where('rental_quotations_id', '=', $rental_quotation_list->rental_quotation_id)->sum('price');
            //  RentalDeliveryOrder::

            $previous_data = RentalDeliveryOrder::where('rental_quotation_id', '=', $rental_quotation_list->rental_quotation_id)->first();
            if ($previous_data) {
                // return $previous_data;
                if ($previous_data->rental_quotation_id === $rental_quotation_list->rental_quotation_id) {
                    //data is already added so if condation is just for checking
                } else {
                    $rental_delivery_order = new RentalDeliveryOrder();
                    $rental_delivery_order->rental_quotation_id = $rental_quotation_list->rental_quotation_id;
                    $rental_delivery_order->quotation_rental_id = $rental_quotation_list->id;
                    $rental_delivery_order->client_id = $rental_quotation_list->client_id;
                    $rental_delivery_order->total_basic_price = $basic_price;
                    $rental_delivery_order->status = 1;
                    $rental_delivery_order->save();
                }
            } else {
                $rental_delivery_order = new RentalDeliveryOrder();
                $rental_delivery_order->rental_quotation_id = $rental_quotation_list->rental_quotation_id;
                $rental_delivery_order->quotation_rental_id = $rental_quotation_list->id;
                $rental_delivery_order->client_id = $rental_quotation_list->client_id;
                $rental_delivery_order->total_basic_price = $basic_price;
                $rental_delivery_order->status = 1;
                $rental_delivery_order->save();
            }
        }
        // $data['rental_quotations_approved_data'] = $rental_quotations_data;
        $per_page = 20;
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
        return response()->json($user_list->paginate($per_page));
    }

    public function UpdateRDOForm(Request $request)
    {
        $request->validate([
            'attachment' => 'mimes:png,jpg,jpeg,docx,pdf,txt',
            'assignee.*' => 'required'
        ]);
        $quotation_rental_id = $request->id;
        $rental_delivery_order = RentalDeliveryOrder::where('quotation_rental_id', '=', $quotation_rental_id)->first();
        $rental_delivery_order->sales_person = $request->sales_person;
        $rental_delivery_order->lpo_number = $request->lpo_number;
        $rental_delivery_order->expected_hire_period_start = $request->expected_hire_period_start;
        $rental_delivery_order->expected_hire_period_end = $request->expected_hire_period_end;
        $rental_delivery_order->operator_accommodation = $request->operator_accommodation;
        $rental_delivery_order->operator_transport = $request->operator_transport;
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
        $rental_delivery_order->company = $request->company;
        $rental_delivery_order->main_office_addres = $request->main_office_addres;
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
        $rental_delivery_order = RentalDeliveryOrder::where('quotation_rental_id', '=', $quotation_rental_id)->first();
        $client_data = Client::where('id', '=', $rental_delivery_order->client_id)->first();

        $rentalquotationequipment = RentalQuotationEquipment::where('quotation_id', $rental_delivery_order->rental_quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment');
            }])
            ->with(['rental_delivery_equpment_data' => function ($query) use ($quotation_rental_id) {
                $query->where('quotation_rental_id', '=', $quotation_rental_id);
            }])
            ->get();

        $rental_delivery_equpment = RentalDeliveryOrderEqupment::where('quotation_rental_id', '=', $quotation_rental_id)
            ->where('rental_quotation_id', '=', $rental_delivery_order->rental_quotation_id)->first();
        $assined_user = QuotationRentalAssine::select('id', 'user_id', 'status')->where('quotation_id', '=', $rental_delivery_order->rental_quotation_id)
            ->where('status', '=', 2)->with(['users' => function ($query) {
                $query->select('id')->with(['roles' => function ($query) {
                    $query->select('id', 'name');
                }]);
            }])->get();

        $data['rental_delivery_order'] = $rental_delivery_order;
        $data['rentalquotationequipment'] = $rentalquotationequipment;
        $data['client_data'] = $client_data;
        $data['rental_delivery_equpment'] = $rental_delivery_equpment;
        $data['assined_user'] = $assined_user;
        $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
            ->with(['assinged_user_rdo_assigne' => function ($query) use ($quotation_rental_id) {
                $query->select('user_id');
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
        $rental_delivery_equipment_data = RentalQuotationEquipment::where('quotation_id', $rental_deliver_data->rental_quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment', 'EquipmentStatusID');
            }])
            ->with('rental_delivery_equpment_data')
            ->with('quotations_rental_equipment_data')
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
                $prior_delivery_inspection->status = 1;
                $prior_delivery_inspection->save();
            }
        }
        // $data['rental_quotations_approved_data'] = $rental_quotations_data;
        // return $data;

        $per_page = 20;
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
        return response()->json($user_list->paginate($per_page));
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

        // $prior_delivery_id =  PriorDeliveryInspection::select('id')->where('rental_delivery_order_id','=',$rdo_id)->first();

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


        $assigne = User::select('id', 'user_name')->where('status', '!=', 0)
            ->with(['assinged_user_prior_delivery_assigne' => function ($query) use ($rdo_id) {
                $query->select('user_id');
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
        
        PriorDeliveryAssigne::where('prior_delivery_id', '=',$rdo_id)->delete();
        $assignee = $request->assignee;
        foreach ($assignee as $assignes) {
            $rental_quotation_assigne = new PriorDeliveryAssigne();

            $rental_quotation_assigne->prior_delivery_id = $PriorDeliveryInspection->id;
            $rental_quotation_assigne->user_id = $assignes;
            $rental_quotation_assigne->status = 1;
            $rental_quotation_assigne->save();
        }
        return $this->sendResponse([], 'Status Updated');
        // return (PriorDeliveryChecked::where('rdo_id', '=', $rdo_id))->get();
    }

    public function ViewPDIData(Request $request)
    {
        $rdo_id = $request->rdo_id;
        $data['view_id'] = $rdo_id;
        $prior_delivery_inspection = PriorDeliveryInspection::where('rental_delivery_order_id', '=', $rdo_id)->first();
        $data['client_data'] = Client::select('id', 'name', 'email')->where('id', '=', $prior_delivery_inspection->client_id)->first();

        $data['rental_quotation'] =  RentalQuotationEquipment::select('id','equipment_id','quantity','site_location')->where('quotation_id', $prior_delivery_inspection->rental_quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment', 'EquipmentStatusID');
            }])
            ->with(['QuotationsRentalRquipmentPrice' => function ($query) {
                $query->select('id', 'equipment_id', 'price');
            }])
        ->get();
        return $data;
    }
    public function ChangePDIAssigneStatus(Request $request)
    {
        // $request->all();
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
                    $rental_deliver_note->status = 1;
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
                $rental_deliver_note->status = 1;
                $rental_deliver_note->save();
            }
        }
        // $data['rental_quotations_approved_data'] = $rental_quotations_data;
        // return $data;

        $per_page = 20;
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
        return response()->json($user_list->paginate($per_page));
    }


    public function EditRDNFormStepOne(Request $request)
    {
        $pdi_id = $request->pdi_id;
        $data['pdi_id'] = $pdi_id; 
        $rental_deliver_note = RentalDeliverNote::where('prior_delivery_inspection_id','=',$pdi_id)->first();
        $data['rental_quotation_id'] = $rental_deliver_note->rental_quotation_id;
        $data['client_name'] = Client::select('id','name')->where('id','=',$rental_deliver_note->client_id)->first();
        $data['rental_delivery_order'] = RentalDeliveryOrder::select('id','lpo_number')->where('id','=',$rental_deliver_note->rental_delivery_order_id)->first();
        $data['rental_delivery_equpment'] = RentalDeliveryOrderEqupment::select('equipment_id','accessories')->where('rental_quotation_id','=',$rental_deliver_note->rental_quotation_id)
        ->with(['DeliveryNoteRecord'=>function($query){
            $query->select('id','pdi_id','equipment_id','date','hours','services_meter_reading');
        }])
        ->with(['equipment_records'=>function($q){
            $q->select('id','MachineModel','PlateNumber','PlantNumber','EngineNumber','MachineMakerID')
            ->with(['MachineMakerRecord'=>function($eqp){
                $eqp->select('id','machineMaker');
            }]);
        }])
        ->get();
        return $data;
    }

    public function EditRDNFormStepTwo(Request $request)
    {   
        // return $request->all();
        $pdi_id = $request->pdi_id;
        $equipment_id = $request->equipment_id;
        $date = $request->date;
        $hours = $request->hours;

        $data['pdi_id'] = $pdi_id; 
        $data['equipment_id'] = $equipment_id;
        $data['date'] = $date;
        $data['hours'] = $hours;

        $data['DeliveryNoteEquipment'] = DeliveryNoteEquipment::where('pdi_id','=',$pdi_id)->where('equipment_id','=',$equipment_id)->first();
        $data['Equipment'] = Equipment::select('PlateNumber')->where('id','=',$equipment_id)->first();
        $data['RentalQuotationEquipment'] = RentalQuotationEquipment::select('sp_position')->where('equipment_id','=',$equipment_id)->first();
        
        $data['assigne'] = User::select('id', 'user_name')->where('status', '!=', 0)
        ->with(['assinged_user_rdn_assigne' => function ($query) use ($pdi_id) {
            $query->select('user_id');
        }])
        ->get();
    
        return $data;
    }

    public function UpdateRDNForm(Request $request)
    {
        // return $request->all();
        $pdi_id = $request->pdi_id;
        $equipment_id = $request->equipment_id;
        $delivery_note_equipment_update = DeliveryNoteEquipment::where('pdi_id','=',$pdi_id)->where('equipment_id','=',$equipment_id)->first();
        $delivery_note_equipment_update->pdi_id = $pdi_id;
        $delivery_note_equipment_update->equipment_id = $equipment_id;
        $delivery_note_equipment_update->date = $request->date;
        $delivery_note_equipment_update->hours = $request->hours;
        $delivery_note_equipment_update->services_meter_reading = $request->services_meter_reading;
        $delivery_note_equipment_update->signed = $request->signed;
        $delivery_note_equipment_update->id_no = $request->id_no;
        $delivery_note_equipment_update->issued_form = $request->issued_form;
        $delivery_note_equipment_update->on_date = $request->on_date;
        $delivery_note_equipment_update->attorney_no = $request->attorney_no;
        $delivery_note_equipment_update->authorized_no = $request->authorized_no;
        $delivery_note_equipment_update->dated = $request->dated;
        $delivery_note_equipment_update->leassor_name = $request->leassor_name;
        $delivery_note_equipment_update->leassee_name = $request->leassee_name;
        $delivery_note_equipment_update->todays_date = $request->todays_date;
        $delivery_note_equipment_update->datee = $request->datee;
        $delivery_note_equipment_update->note = $request->note;
        $delivery_note_equipment_update->save();

        DeliveryNoteAssignes::where('delivery_note_id','=',$pdi_id)->delete();
        $assignee = $request->assignee;
        foreach ($assignee as $assignes) {
            $delivery_note_assignes = new DeliveryNoteAssignes();
            $delivery_note_assignes->delivery_note_id = $pdi_id;
            $delivery_note_assignes->user_id = $assignes;
            $delivery_note_assignes->status = 1;
            $delivery_note_assignes->save();
        }
        return $this->sendResponse([], 'Updated Successfully');
    }

    public function ViewRDNData(Request $request)
    {
        // $request->all();
        $rdn_id = $request->rdn_id;
        $data['view_id'] = $rdn_id;
        $rental_deliver_note = RentalDeliverNote::where('prior_delivery_inspection_id', '=', $rdn_id)->first();
        $data['client_data'] = Client::select('id', 'name', 'email')->where('id', '=', $rental_deliver_note->client_id)->first();

        $data['rental_quotation'] =  RentalQuotationEquipment::select('id','equipment_id','quantity','site_location')->where('quotation_id', $rental_deliver_note->rental_quotation_id)
            ->with(['equipment_data' => function ($query) {
                $query->select('id', 'Equipment', 'EquipmentStatusID');
            }])
            ->with(['QuotationsRentalRquipmentPrice' => function ($query) {
                $query->select('id', 'equipment_id', 'price');
            }])
        ->get();
        return $data;
    }



    public function ChangeRDNAssigneStatus(Request $request)
    {
        // return $request->all();
        // return Auth::User()->id;
        $reject_note = $request->reject_note;
        // $prior_delivery_id = $request->rdn_id;

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
                $update_status = RentalDeliverNote::where('id', '=', $rdn_id)->first();
                $update_status->status = 2;
                $update_status->save();
            } else {
                $update_status = RentalDeliverNote::where('id', '=', $rdn_id)->first();
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
                $update_status = RentalDeliverNote::where('id', '=', $rdn_id)->first();
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
        $per_page = 20;
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
        return response()->json($user_list->paginate($per_page));
    }

    public function EditOFFHIREForm(Request $request)
    {
        //  $request->all();
        $rdn_id = $request->rdn_id;
        $off_hire_list = OffHireList::where('rental_delivery_note_id','=',$rdn_id)->first();
        return Client::select('id','name')->where('id','=',$off_hire_list->client_id)->first();


    }

}
