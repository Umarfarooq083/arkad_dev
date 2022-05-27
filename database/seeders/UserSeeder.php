<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 100; $i++) {

            DB::table('users')->insert([
                'first_name' => 'admin'.$i,
                'last_name' => 'user'.$i,
                'role_id' => rand(1,9),
                'department_id' => rand(1,9),
                'dob' => date('Y-m-d', strtotime('-18 year', strtotime(date('Y-m-d')))),
                'secrat_question_id' => 1,
                'user_name' => 'admin'.$i,
                'email' => 'admin'.$i.'@arkad.com',
                'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'password' => Hash::make('admin'.$i),
                'status' => rand(1,2),
                'created_by' => 0,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        }

    }
}
