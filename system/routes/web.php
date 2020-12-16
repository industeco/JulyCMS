<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\CommandController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 后台
Route::group([
    'prefix' => config('jc.site.backend_route_prefix', 'admin'),
    'middleware' => ['admin', 'auth'],
], function() {
    // 后台首页
    Route::get('/', 'Dashboard')->name('admin.home');

    // 文件管理
    Route::get('/media', [MediaController::class, 'index'])
        ->name('media.index');

    Route::get('/media/select', [MediaController::class, 'select'])
        ->name('media.select');

    Route::post('/media/under', [MediaController::class, 'under'])
        ->name('media.under');

    Route::post('/media/upload', [MediaController::class, 'upload'])
        ->name('media.upload');

    Route::post('/media/mkdir', [MediaController::class, 'mkdir'])
        ->name('media.mkdir');

    Route::post('/media/rename/{type}', [MediaController::class, 'rename'])
        ->name('media.rename');

    Route::post('/media/delete/{type}', [MediaController::class, 'delete'])
        ->name('media.delete');

    // Route::post('/media/move', [MediaController::class, 'move'])
    //     ->name('media.move');


    // 执行命令
    Route::get('search', [CommandController::class, 'searchDatabase'])
        ->name('cmd.search');

    Route::post('cmd/update/password', [CommandController::class, 'updateAdminPassword'])
        ->name('cmd.updatePassword');

    Route::get('cmd/clear/cache', [CommandController::class, 'clearCache'])
        ->name('cmd.clearCache');

    Route::get('cmd/build/index', [CommandController::class, 'buildIndex'])
        ->name('cmd.buildIndex');

    Route::get('cmd/build/google-sitemap', [CommandController::class, 'buildGoogleSitemap'])
        ->name('cmd.buildGoogleSitemap');

    Route::get('cmd/find/invalid-links', [CommandController::class, 'findInvalidLinks'])
        ->name('cmd.findInvalidLinks');
});

// 前台
Route::post('newmessage', [CommandController::class, 'newMessage'])
    ->name('ajax.newmessage');

Route::get('search', [CommandController::class, 'search'])
    ->name('ajax.search');
