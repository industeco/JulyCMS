<?php

use Illuminate\Support\Facades\Route;
use July\Message\Controllers;

// 组件的 web 路由
Route::group([
    'prefix' => config('app.management_prefix', 'admin'),
    'middleware' => ['admin','auth'],
], function() {
    // // 字段
    // Route::get('node_fields/exists/{id}', [Controllers\NodeFieldController::class, 'exists'])
    //     ->name('node_fields.exists');

    // Route::resource('node_fields', Controllers\NodeFieldController::class)
    //     ->parameters(['node_fields' => 'nodeField'])
    //     ->names('node_fields');

    // // 内容类型
    // Route::get('node_types/exists/{id}', [Controllers\NodeTypeController::class, 'exists'])
    //     ->name('node_types.exists');

    // Route::resource('node_types', Controllers\NodeTypeController::class)
    //     ->parameters(['node_types' => 'nodeType'])
    //     ->names('node_types');

    // // 内容
    // Route::get('nodes/create/{nodeType}', [Controllers\NodeController::class, 'create'])
    //     ->name('nodes.create');

    // Route::get('nodes/mold', [Controllers\NodeController::class, 'chooseMold'])
    //     ->name('nodes.choose_mold');

    // Route::get('nodes/{node}/translate', [Controllers\NodeController::class, 'translateTo'])
    //     ->name('nodes.translate_to');

    // Route::get('nodes/{node}/translate/{langcode}', [Controllers\NodeController::class, 'edit'])
    //     ->name('nodes.translate');

    // Route::post('nodes/render', [Controllers\NodeController::class, 'render'])
    //     ->name('nodes.render');

    // Route::resource('nodes', Controllers\NodeController::class)
    //     ->except(['create'])
    //     ->parameters(['nodes' => 'node'])
    //     ->names('nodes');
});
