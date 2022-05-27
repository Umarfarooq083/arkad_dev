<!DOCTYPE html>
<html>
<head>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
<body>

<h2>Oder's Detail</h2>

<table>
    <thead>
    
  <tr>
    <th>id</th>
    <th>Client Name</th>
    <th>Order ID</th>
    <th>Total Price</th>
    <th>Paid Unpaid</th>
    <th>Status</th>
    <th>Created Date</th>
  </tr>
  </thead>
  <tbody>
     
  @foreach($download_able as $download)
    <tr>
        <td>{{$download->id}}</td>
        <td>{{$download->ClientRecord->name}}</td>
        <td>{{$download->quotation_rental_id}}</td>
        <td>{{$download->total_basic_price}}</td>
        <td>
            @if($download->paid_unpaid == 1)
             Paid
            @else 
             Unpaid
            @endif
        </td>
        <td>
            @if($download->status == 1)
                Watting For Approve  
            @elseif($download->status == 2)
                Approved 
            @elseif($download->status == 3) 
                Rejected 
            @endif
        </td>
        <td>{{$download->created_at}}</td>
    </tr>
      @endforeach()
  </tbody>
</table>

</body>
</html>

