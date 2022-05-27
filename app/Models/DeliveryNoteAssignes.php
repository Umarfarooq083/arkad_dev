<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteAssignes extends Model
{
    use HasFactory;
    protected $table = 'delivery_note_assignes';

    public function AssigneUserNameRDN()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
