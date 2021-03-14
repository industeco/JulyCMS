<?php

namespace Database\Seeds;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class UserSeeder extends SeederBase
{
    /**
     * 指定数据表
     *
     * @var string|string[]
     */
    protected $table = 'users';

    /**
     * 获取初始数据
     *
     * @return array[]
     */
    public static function getRecords()
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
