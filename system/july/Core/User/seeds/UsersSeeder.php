<?php

namespace July\Core\User\seeds;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use App\Database\SeederBase;

class UsersSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = [
        'users'
    ];

    /**
     * 获取 users 表数据
     *
     * @return array
     */
    protected function getUsersRecords()
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
