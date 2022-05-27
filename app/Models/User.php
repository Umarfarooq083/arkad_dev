<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Authorizable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
//        'pivot'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $guard_name = 'api';

    public function user_roles()
    {
        return $this->belongsToMany(Roles::class, 'user_role', 'role_id', 'user_id');
    }

    public function designation()
    {
        return $this->hasOne(Designation::class, 'id', 'department_id');
    }

    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }
    // RentalQuotationAssigne

    public function rental_qutation_assignees()
    {
        return $this->hasOne(RentalQuotationAssigne::class, 'user_id','id');
    }

    public function assinged_user_qutation_rental()
    {
        return $this->hasOne(QuotationRentalAssine::class,);
    }

    public function assinged_user_prior_delivery_assigne()
    {
        return $this->hasOne(PriorDeliveryAssigne::class,);
    }

    public function assinged_user_mcr_list_assignes()
    {
        return $this->hasOne(MCRListAssignees::class,);
    }

    // MCRListChecked
    public function assinged_user_rdo_assigne()
    {
        return $this->hasOne(RentalDeliveryAssignes::class,);
    }

    public function assinged_user_rdn_assigne()
    {
        return $this->hasOne(DeliveryNoteAssignes::class,);
    }
    public function assinged_user_off_hire_assigne()
    {
        return $this->hasOne(OffHireAssignes::class,);
    }
    
    public function assinged_user_equipment_masterdata()
    {
        return $this->hasOne(EquipMaintenanceAssignees::class,);
    }
    
    

}
