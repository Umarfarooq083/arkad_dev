<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MCRListAssignees extends Model
{
    use HasFactory;
    protected $table = 'mcr_list_assignees';

    public function AssigneUserNameMCR()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
