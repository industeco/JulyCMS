<?php

use Illuminate\Support\Facades\Route;
use Specs\Controllers;

Route::prefix(config('app.management_prefix', 'admin'))
->middleware(['admin','auth'])
->group(function() {
    Route::resource('specs', Controllers\SpecController::class)
        ->parameters(['specs' => 'spec'])
        ->names('specs');

    // 判断是否已存在
    Route::get('specs/exists/{id}', [Controllers\SpecController::class, 'exists'])
        ->name('specs.exists');

    // 浏览数据
    Route::get('specs/{spec}/records', [Controllers\SpecController::class, 'records'])
        ->name('specs.records');

    // 插入或更新数据
    Route::post('specs/{spec}/records', [Controllers\SpecController::class, 'upsertRecords'])
        ->name('specs.records.upsert');

    // 删除数据
    Route::post('specs/{spec}/records/remove', [Controllers\SpecController::class, 'removeRecords'])
        ->name('specs.records.remove');

    // 清空数据
    Route::delete('specs/{spec}/records/clear', [Controllers\SpecController::class, 'clearRecords'])
        ->name('specs.records.clear');
});

// 搜索规格
Route::get('specs/{spec}/search', [Controllers\SpecController::class, 'showSearch'])
    ->name('specs.show_search');

// 搜索规格
Route::post('specs/{spec}/search', [Controllers\SpecController::class, 'search'])
    ->name('specs.search');

// 规格展示
Route::get('specs/{spec}/records/{record_id}', [Controllers\SpecController::class, 'showRecord'])
    ->name('specs.show_record');
