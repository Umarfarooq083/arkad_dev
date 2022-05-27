<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;


class RolesAndDesignations extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = array(
            ['role_name'=>'OPERATIONS MANAGER'],
            ['role_name'=>'RENTAL COORDINATOR'],
            ['role_name'=>'PMV PLANT MANAGER'],
            ['role_name'=>'WORKSHOP MANAGER'],
            ['role_name'=>'RETNAL MANAGER'],
            ['role_name'=>'LOGISTICS OFFICER'],
            ['role_name'=>'PLANT ADMIN'],
            ['role_name'=>'CFO'],
            ['role_name'=>'CEO'],
        );
        $designations = array(
            ['designation_name'=>'OPERATIONS MANAGER','status'=>1 ],
            ['designation_name'=>'RENTAL COORDINATOR','status'=>1 ],
            ['designation_name'=>'PMV PLANT MANAGER','status'=>1 ],
            ['designation_name'=>'WORKSHOP MANAGER','status'=>1 ],
            ['designation_name'=>'RETNAL MANAGER','status'=>1 ],
            ['designation_name'=>'LOGISTICS OFFICER','status'=>1 ],
            ['designation_name'=>'PLANT ADMIN','status'=>1 ],
            ['designation_name'=>'CFO','status'=>1 ],
            ['designation_name'=>'CEO','status'=>1 ],
        );

        DB::table('roles')->insert($roles);
        DB::table('designations')->insert($designations);


    }
}
