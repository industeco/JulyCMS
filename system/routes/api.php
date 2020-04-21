<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::get('/install', function() {
//     if (config('app.installed')) {
//         return Response::make([
//             'success' => false,
//             'msg' => '',
//         ], 404);
//     }

//     Artisan::call('key:generate');
//     Artisan::call('migrate', [
//         '--seed' => true,
//     ]);

//     Config::set('app.installed', true);

//     return Response::make([
//         'success' => true,
//         'msg' => '安装成功',
//     ]);
// });
