<?php

namespace Database\Seeds;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class UserSeeder extends SeederBase
{
    /**
     * 待填充数据库表
     *
     * @var array
     */
    protected $tables = [
        'users'
    ];

    /**
     * 获取 users 表数据
     *
     * @return array[]
     */
    protected function getUsersTableRecords()
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
