<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffHireAssignes extends Model
{
    use HasFactory;
    protected $table = 'off_hire_assignes';
    public function AssigneUserNameOffHire()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
