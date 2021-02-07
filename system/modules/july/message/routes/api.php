<?php

use Illuminate\Support\Facades\Route;
use July\Message\Controllers;

// 添加组件 api 路由

// 发送消息
Route::post('messages/{form}/send', [Controllers\MessageController::class, 'send'])
    ->name('messages.send');
