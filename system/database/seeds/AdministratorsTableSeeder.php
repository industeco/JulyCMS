<?php

use Illuminate\Database\Seeder;
use App\Models\Administrator;
use Illuminate\Support\Facades\Hash;

class AdministratorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create a user.
        Administrator::create([
            'truename' => config('admin_name', 'admin'),
            'password' => Hash::make(config('admin_password', 'admin666')),
            'name'     => '管理员',
        ]);
    }
}
