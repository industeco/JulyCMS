<?php

use Illuminate\Support\Facades\Route;
use July\Message\Controllers;

// 发送消息
Route::post('messages/{form}/send', [Controllers\MessageController::class, 'send'])
    ->name('messages.send');
