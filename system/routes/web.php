<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

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
Route::group([
    'prefix' => config('app.management_prefix', 'admin'),
    'middleware' => ['admin'],
], function() {
    Route::get('login',  [Controllers\LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [Controllers\LoginController::class, 'login'])->name('admin.auth');
    Route::get('logout', [Controllers\LoginController::class, 'logout'])->name('admin.logout');
});

// 后台
Route::group([
    'prefix' => config('app.management_prefix', 'admin'),
    'middleware' => ['admin', 'auth'],
], function() {
    // 后台首页
    Route::get('/', 'Dashboard')->name('admin.home');

    // 配置管理
    Route::get('settings/{group}', [Controllers\SettingsController::class, 'edit'])
        ->name('settings.edit');

    Route::post('settings/{group}', [Controllers\SettingsController::class, 'update'])
        ->name('settings.update');

    // 文件管理
    Route::get('/media', [Controllers\MediaController::class, 'index'])
        ->name('media.index');

    Route::get('/media/select', [Controllers\MediaController::class, 'select'])
        ->name('media.select');

    Route::post('/media/under', [Controllers\MediaController::class, 'under'])
        ->name('media.under');

    Route::post('/media/upload', [Controllers\MediaController::class, 'upload'])
        ->name('media.upload');

    Route::post('/media/mkdir', [Controllers\MediaController::class, 'mkdir'])
        ->name('media.mkdir');

    Route::post('/media/rename/{type}', [Controllers\MediaController::class, 'rename'])
        ->name('media.rename');

    Route::post('/media/delete/{type}', [Controllers\MediaController::class, 'delete'])
        ->name('media.delete');

    // Route::post('/media/move', [MediaController::class, 'move'])
    //     ->name('media.move');

    // 实体路径别名查重
    Route::post('path_alias/exists', [Controllers\PathAliasController::class, 'exists'])
        ->name('path_alias.exists');


    // 执行命令
    Route::get('search', [Controllers\CommandController::class, 'searchDatabase'])
        ->name('cmd.search');

    Route::post('cmd/update/password', [Controllers\CommandController::class, 'updateAdminPassword'])
        ->name('cmd.updatePassword');

    Route::get('cmd/clear/cache', [Controllers\CommandController::class, 'clearCache'])
        ->name('cmd.clearCache');

    Route::get('cmd/build/index', [Controllers\CommandController::class, 'buildIndex'])
        ->name('cmd.buildIndex');

    Route::get('cmd/build/google-sitemap', [Controllers\CommandController::class, 'buildGoogleSitemap'])
        ->name('cmd.buildGoogleSitemap');

    Route::get('cmd/find/invalid-links', [Controllers\CommandController::class, 'findInvalidLinks'])
        ->name('cmd.findInvalidLinks');
});

// // 前台
// Route::post('newmessage', [Controllers\CommandController::class, 'newMessage'])
//     ->name('ajax.newmessage');

Route::get('search', [Controllers\CommandController::class, 'search'])
    ->name('ajax.search');
