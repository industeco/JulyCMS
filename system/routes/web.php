<?php

use App\July;
use Illuminate\Support\Facades\Route;

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
    'prefix' => config('jc.admin.prefix'),
    'middleware' => ['web', 'admin'],
], function() {
    July::adminRoutes();
});

// 安装
Route::get('install', ['uses'=>'InstallController@home', 'as'=>'install.home']);
Route::post('install', ['uses'=>'InstallController@install', 'as'=>'install.install']);
Route::post('install/migrate', ['uses'=>'InstallController@migrate', 'as'=>'install.migrate']);

// 前台
Route::post('newmessage', ['uses' => 'CommandController@newMessage', 'as' => 'userCommand.newmessage']);
Route::get('search', ['uses' => 'CommandController@search', 'as' => 'userCommand.search']);
Route::get('{any}', 'AnyPage')->where('any', '.*');
