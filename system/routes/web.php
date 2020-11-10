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

// 安装
// Route::get('install', ['uses'=>'InstallController@home', 'as'=>'install.home']);
// Route::post('install', ['uses'=>'InstallController@install', 'as'=>'install.install']);
// Route::post('install/migrate', ['uses'=>'InstallController@migrate', 'as'=>'install.migrate']);

// 后台
Route::group([
    'prefix' => config('jc.site.backend_route_prefix', 'admin'),
    'middleware' => 'admin',
], function() {

//     // 登录路由
//     Route::get('login', 'Auth\LoginController@showLoginForm')->name('admin.login');
//     Route::post('login', 'Auth\LoginController@login')->name('admin.auth');
//     Route::get('logout', 'Auth\LoginController@logout')->name('admin.logout');

    Route::middleware('auth')->group(function() {
        // 后台首页
        Route::get('/', 'Dashboard')->name('admin.home');

//         // 配置
//         Route::get('configs/{group}', 'ConfigController@edit')->name('configs.edit');
//         Route::post('configs', 'ConfigController@update')->name('configs.update');

//         // 字段
//         Route::resource('node_fields', 'NodeFieldController')->parameters([
//             'node_fields' => 'nodeField',
//         ])->names('node_fields');

//         // 内容类型
//         // Route::get('node_types/{nodeType}/translate', 'NodeTypeController@translate')->name('node_types.translate');
//         // Route::get('node_types/{nodeType}/translate/{langcode}', 'NodeTypeController@edit')->name('node_types.translate_to');
//         Route::resource('node_types', 'NodeTypeController')->parameters([
//             'node_types' => 'nodeType',
//         ])->names('node_types');

//         // 目录
//         Route::get('catalogs/{catalog}/sort', 'CatalogController@sort')->name('catalogs.sort');
//         Route::put('catalogs/{catalog}/sort', 'CatalogController@updateOrders')->name('catalogs.updateOrders');
//         Route::resource('catalogs', 'CatalogController')->parameters([
//             'catalogs' => 'catalog',
//         ])->names('catalogs');

//         // 内容
//         Route::get('nodes/create/{nodeType}', 'NodeController@create')->name('nodes.create');
//         Route::get('nodes/nodetypes', 'NodeController@chooseNodetype')->name('nodes.nodetypes');
//         Route::get('nodes/{node}/languages', 'NodeController@chooseLanguage')->name('nodes.languages');
//         Route::get('nodes/{node}/translate/{langcode}', 'NodeController@edit')->name('nodes.translate');
//         Route::post('nodes/render', 'NodeController@render')->name('nodes.render');
//         Route::resource('nodes', 'NodeController')->except(['create'])->parameters([
//             'nodes' => 'node',
//         ])->names('nodes');

//         // 标签
//         Route::get('tags', 'TagController@index')->name('tags.index');
//         Route::post('tags', 'TagController@update')->name('tags.update');

//         // 查重
//         Route::get('checkunique/node_fields/{truename}', 'NodeFieldController@unique')->name('checkunique.node_fields');
//         Route::get('checkunique/node_types/{truename}', 'NodeTypeController@unique')->name('checkunique.node_types');
//         Route::get('checkunique/catalogs/{truename}', 'CatalogController@unique')->name('checkunique.catalogs');
//         Route::post('checkunique/node__url', 'NodeFieldController@uniqueUrl')->name('checkunique.url');


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


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
});

// 前台
Route::post('newmessage', [CommandController::class, 'newMessage'])
    ->name('ajax.newmessage');

Route::get('search', [CommandController::class, 'search'])
    ->name('ajax.search');

Route::fallback('AnyPage');
