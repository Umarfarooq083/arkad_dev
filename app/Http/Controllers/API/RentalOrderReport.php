<?php

namespace App\Http\Controllers\API;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\RentalDeliveryOrder;
use Illuminate\Http\Request;
use App\Models\Client;
use Excel;

class RentalOrderReport extends Controller
{
    public function GetClientData(Request $request)
    {
        return Client::select('id','name')->get();
    }

    public function GetOrderData(Request $request)
    {
        $request->all();
        $data['client_id'] = $request->client_id; 
        $order_data = RentalDeliveryOrder::select('id','rental_quotation_id','quotation_rental_id','client_id')->where('client_id','=',$request->client_id)->get();
        $data['order_data'] = $order_data;
        return $data;
    }

    public function GetOrderStatusData(Request $request)
    {
        return $request->all();
    }

    public function DownloadOderReport(Request $request)
    {
        $order_ids = $request->order_id;
        $status =  $request->status;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $paid_or_unpaid = $request->paid_or_unpaid;
        if(count($status) ===1 & $status[0] === null){
            unset($status);
            $status = [1,2,3];
        }
        $download_able = RentalDeliveryOrder::select('id','quotation_rental_id','total_basic_price','paid_unpaid','status')->where('client_id','=',$request->client_id)
        ->whereIn('quotation_rental_id',$request->order_id)
        ->WhereIn('status',$status)
        ->WhereIn('paid_unpaid',$paid_or_unpaid)
        ->get();

        // return Excel::download(new UsersExport($download_able),'.xlsx');
        return $download_able;
    }

}
