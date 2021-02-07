<?php

use Illuminate\Support\Facades\Route;
use Specs\Controllers;

Route::prefix(config('app.management_prefix', 'admin'))
->name('manage.')
->middleware(['admin','auth'])
->group(function() {
    // 规格
    // 判断规格是否已存在
    Route::get('specs/{id}/exists', [Controllers\SpecController::class, 'exists'])
        ->name('specs.exists');

    // 规格路由
    Route::resource('specs', Controllers\SpecController::class)
        ->parameters(['specs' => 'spec'])
        ->names('specs');

    // 规格数据
    // 浏览规格数据
    Route::get('specs/{spec}/records', [Controllers\RecordController::class, 'index'])
        ->name('specs.records.index');

    // 插入或更新数据
    Route::post('specs/{spec}/records', [Controllers\RecordController::class, 'upsert'])
        ->name('specs.records.upsert');

    // 删除或清空数据
    Route::delete('specs/{spec}/records', [Controllers\RecordController::class, 'destroy'])
        ->name('specs.records.destroy');
});

// 显示规格搜索页
Route::get('specs/records', [Controllers\RecordController::class, 'search'])
    ->name('specs.records.search');

// 规格展示
Route::get('specs/{spec}/records/{record_id}', [Controllers\RecordController::class, 'show'])
    ->name('specs.record.show');
