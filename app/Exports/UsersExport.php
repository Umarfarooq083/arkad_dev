<?php

namespace App\Exports;

use App\Models\User;
use App\Models\RentalDeliveryOrder;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsersExport implements FromCollection
{
    private $data;
  
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
        // return RentalDeliveryOrder::;
    }
}
