<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Roles extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $fillable = ['role_name'];
    protected $hidden = [
        'pivot'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions' );
    }
}
