<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 用户表
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // 用户名
            $table->string('name', 32)->unique();

            // 密码
            $table->string('password');

            // 角色：superadmin, admin, editor, user, guest
            $table->string('role', 32)->default('user');

            // 登录令牌，防止重复登录
            $table->string('login_token')->nullable();

            // remember_token
            $table->rememberToken();

            // 时间戳
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
