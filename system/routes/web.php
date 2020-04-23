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
    \App\July::AdminRoutes();
});

// Route::group([
//     'prefix' => 'admin',
//     'middleware' => ['web', 'admin']
// ], function () {
//     \UniSharp\LaravelFilemanager\Lfm::routes();
// });


// Route::fallback('NotFound');

// Route::get('{any}', function($any) {

//     // 假设默认语言为中文
//     if (preg_match('~^cn/~', $any)) {
//         $any = substr($any, 3);
//     }

//     // 动态的 contact
//     if (preg_match('~(cn/|en/)?contact(\.html?)?$~i', $any)) {
//         return '<h1>这是动态生成的 contact 页</h1>';
//     }

//     // 始终查找 html 文件
//     $any = preg_replace('~\.html$~i', '.html', $any);
//     if (! preg_match('~\.html$~', $any)) {
//         $any = rtrim($any, '.') . '.html';
//     }

//     // 依次在根目录 和 pages 目录查找文件
//     if (file_exists($file = public_path($any))) {
//         return file_get_contents($file);
//     } elseif (file_exists($file = public_path('pages/'.$any))) {
//         return file_get_contents($file);
//     }

//     abort(404);

// })->where('any', '.*');
