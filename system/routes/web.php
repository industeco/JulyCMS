<?php

use App\July;
use App\Models\NodeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

Route::get('install', ['uses'=>'InstallController@home', 'as'=>'install.home']);
Route::post('install', ['uses'=>'InstallController@install', 'as'=>'install.install']);
Route::post('install/migrate', ['uses'=>'InstallController@migrate', 'as'=>'install.migrate']);

Route::group([
    'prefix' => 'admin',
    'middleware' => ['web', 'admin'],
], function() {
    July::adminRoutes();
});

July::webRoutes();
