<?php

use Illuminate\Support\Facades\Route;
use Specs\Controllers;

// 获取规格数据
Route::get('specs/records', [Controllers\RecordController::class, 'fetch'])
    ->name('specs.records.fetch');
