<?php

use Illuminate\Database\Seeder;
use App\Models\Administrator;

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
            'truename' => config('admin_truename', 'admin'),
            'password' => bcrypt(config('admin_password', 'admin666')),
            'name'     => '管理员',
        ]);
    }
}
