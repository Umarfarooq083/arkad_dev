<?php
  
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use App\Models\RentalQuotationEquipment;
use App\Models\QuotationRental;
use App\Models\RentalDeliveryOrder;
use App\Models\Client;
use PDF;
  
class PDFController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generatePDF(Request $request)
    {
      $quotation_id = $request->id;
      $data['quotation_rental'] = QuotationRental::where('rental_quotation_id','=',$quotation_id)->first();
      $data['pdf_file_data'] =  RentalQuotationEquipment::where('quotation_id' , $quotation_id)
      ->with(['equipment_data'=>function($query){
          $query->select('id','Equipment');
      }])
      ->with(['quotations_rental_equipment_price'=>function($query) use( $quotation_id ){ 
        $query->select('rental_quotations_id','equipment_id','price')
        ->where('rental_quotations_id','=',$quotation_id);
      }])
      ->get();
      return $data;
//    return view('myPDF', $data);
      // $pdf = PDF::loadView('myPDF', $data);
      // $pdf->render();
      // return $pdf->download('itsolutionstuff.pdf');
    }

    public function GenrateOrderPDF(Request $request)
    {
     $client_id =  $request->client_id;
      $order_ids = $request->order_id;
      $status =  $request->status;
      $start_date = $request->start_date;
      $end_date = $request->end_date;
      $paid_or_unpaid = $request->paid_or_unpaid;
      if(count($status) ===1 & $status[0] === null){
          unset($status);
          $status = [1,2,3];
      }

      $data['client_record'] = Client::select('id','name')->where('id','=',$request->client_id)->get();
      $download_able = RentalDeliveryOrder::select('id','quotation_rental_id','total_basic_price','paid_unpaid','status','client_id','created_at','updated_at')->where('client_id','=',$request->client_id)
      ->whereIn('quotation_rental_id',$request->order_id)
      ->WhereIn('status',$status)
      ->WhereIn('paid_unpaid',$paid_or_unpaid)
      ->with(['ClientRecord'=>function($query) use($client_id){
        $query->select('id','name')->where('id','=',$client_id)->first();
      }])
      ->get();
      $data['download_able'] = $download_able;
      return $data;
      // return QuotationRental::whereIn('id',$order_ids)->get();
     
      // $pdf = PDF::loadView('orderpdf', $data);
      // $pdf->render();
      // return $pdf->download('itsolutionstuff.pdf');
    }
}