<?php

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

// Route::get('/', 'HomePage');

Route::group([
    'prefix' => 'admin',
    'middleware' => ['web', 'admin'],
], function() {
    \App\July::adminRoutes();
});

\App\July::webRoutes();
