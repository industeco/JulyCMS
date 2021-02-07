<?php

use Illuminate\Support\Facades\Route;
use July\Message\Controllers;

// 组件的 web 路由
Route::group([
    'prefix' => config('app.management_prefix', 'admin'),
    'middleware' => ['admin','auth'],
], function() {
    // 字段
    Route::get('message_fields/exists/{id}', [Controllers\MessageFieldController::class, 'exists'])
        ->name('message_fields.exists');

    Route::resource('message_fields', Controllers\MessageFieldController::class)
        ->parameters(['message_fields' => 'messageField'])
        ->names('message_fields');

    // 联系表单
    Route::get('message_forms/exists/{id}', [Controllers\MessageFormController::class, 'exists'])
        ->name('message_forms.exists');

    Route::resource('message_forms', Controllers\MessageFormController::class)
        ->parameters(['message_forms' => 'form'])
        ->names('message_forms');

    // Route::post('messages/render', [Controllers\MessageController::class, 'render'])
    //     ->name('messages.render');

    Route::resource('messages', Controllers\MessageController::class)
        ->except(['create','edit','store','update'])
        ->parameters(['messages' => 'message'])
        ->names('messages');
});
