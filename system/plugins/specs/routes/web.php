<?php

use Illuminate\Support\Facades\Route;
use Specs\Controllers;

Route::prefix(config('jc.site.backend_route_prefix', 'admin'))
->middleware(['web','admin','auth'])
->group(function() {
    Route::resource('specs', Controllers\SpecController::class)
        ->parameters(['specs' => 'spec'])
        ->names('specs');

    // 判断是否已存在
    Route::get('specs/exists/{id}', [Controllers\SpecController::class, 'isExist'])
        ->name('specs.is_exist');

    // 数据导入
    Route::get('specs/{spec}/insert', [Controllers\SpecController::class, 'insert'])
        ->name('specs.insert');
});
