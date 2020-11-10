<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getUserData() as $record) {
            DB::table('users')->insert($record);
        }
    }

    protected function getUserData()
    {
        return [
            [
                'name' => Request::input('admin_name', 'admin'),
                'password' => Hash::make(Request::input('admin_password', 'admin666')),
                'role' => 'superadmin',
            ],
        ];
    }
}
