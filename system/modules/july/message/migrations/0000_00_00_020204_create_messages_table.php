<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // 消息主题
            $table->string('subject')->default('New Message');

            // 消息类型（表单）
            $table->string('mold_id');

            // 是否已发送
            $table->boolean('is_sent')->default(false);

            // 用户代理描述
            $table->string('user_agent');

            // ip 地址
            $table->string('ip');

            // 浏览轨迹报告
            $table->string('trails')->nullable();

            // 序列化的 $_SERVER 数组
            $table->binary('_server');

            // 源语言
            $table->string('langcode', 12);

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
        Schema::dropIfExists('messages');
    }
}
