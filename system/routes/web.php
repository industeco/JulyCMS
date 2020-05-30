<?php

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

// 安装
Route::get('install', ['uses'=>'InstallController@home', 'as'=>'install.home']);
Route::post('install', ['uses'=>'InstallController@install', 'as'=>'install.install']);
Route::post('install/migrate', ['uses'=>'InstallController@migrate', 'as'=>'install.migrate']);

// 后台
Route::group([
    'prefix' => config('jc.admin_prefix', 'admin'),
    'middleware' => ['web', 'admin'],
], function() {
    Route::get('/', 'Dashboard')->name('admin.home');

    /* @var \Illuminate\Routing\Router $router */
    // 登录
    Route::namespace('Auth')->group(function() {
        Route::get('login', 'AdminLoginController@showLoginForm')->name('admin.login');
        Route::post('login', 'AdminLoginController@login')->name('admin.auth');
        Route::get('logout', 'AdminLoginController@logout')->name('admin.logout');
    });

    // 配置
    Route::get('configs/basic', 'JulyConfigController@editBasicSettings')->name('configs.basic.edit');
    Route::post('configs/basic', 'JulyConfigController@updateBasicSettings')->name('configs.basic.update');
    Route::get('configs/language', 'JulyConfigController@editLanguageSettings')->name('configs.language.edit');
    Route::post('configs/language', 'JulyConfigController@updateLanguageSettings')->name('configs.language.update');

    // 字段
    Route::resource('node_fields', 'NodeFieldController')->parameters([
        'node_fields' => 'nodeField',
    ])->names('node_fields');

    // 内容类型
    Route::resource('node_types', 'NodeTypeController')->parameters([
        'node_types' => 'nodeType',
    ])->names('node_types');

    // 标签
    Route::get('tags', 'TagController@index')->name('tags.index');
    Route::post('tags', 'TagController@update')->name('tags.update');

    // 目录
    Route::get('catalogs/{catalog}/sort', 'CatalogController@sort')->name('catalogs.sort');
    Route::put('catalogs/{catalog}/sort', 'CatalogController@updateOrders')->name('catalogs.updateOrders');
    Route::resource('catalogs', 'CatalogController')->parameters([
        'catalogs' => 'catalog',
    ])->names('catalogs');

    // 内容
    Route::get('nodes/create/{nodeType}', 'NodeController@createWith')->name('nodes.create_with');
    Route::get('nodes/{node}/translate', 'NodeController@translate')->name('nodes.translate');
    Route::get('nodes/{node}/translate/{langcode}', 'NodeController@edit')->name('nodes.translate_to');
    Route::post('nodes/render', 'NodeController@render')->name('nodes.render');
    Route::resource('nodes', 'NodeController')->parameters([
        'nodes' => 'node',
    ])->names('nodes');

    // 文件管理
    Route::get('/media', 'MediaController@index')->name('media.index');
    Route::get('/media/select', 'MediaController@select')->name('media.select');
    Route::post('/media/under', 'MediaController@under')->name('media.under');
    Route::post('/media/upload', 'MediaController@upload')->name('media.upload');
    Route::post('/media/mkdir', 'MediaController@mkdir')->name('media.mkdir');
    Route::post('/media/rename/{type}', 'MediaController@rename')->name('media.rename');
    Route::post('/media/delete/{type}', 'MediaController@delete')->name('media.delete');

    // 查重
    Route::get('checkunique/node_fields/{truename}', 'NodeFieldController@unique')->name('checkunique.node_fields');
    Route::get('checkunique/node_types/{truename}', 'NodeTypeController@unique')->name('checkunique.node_types');
    Route::get('checkunique/catalogs/{truename}', 'CatalogController@unique')->name('checkunique.catalogs');
    Route::post('checkunique/node__url', 'NodeFieldController@uniqueUrl')->name('checkunique.url');

    // 执行命令
    Route::get('search', 'CommandController@searchDatabase')->name('adminCommand.search');
    Route::post('cmd/update/password', 'CommandController@updateAdminPassword')->name('adminCommand.updatePassword');
    Route::get('cmd/clear/cache', 'CommandController@clearCache')->name('adminCommand.clearCache');
    Route::get('cmd/build/index', 'CommandController@buildIndex')->name('adminCommand.buildIndex');
    Route::get('cmd/build/google-sitemap', 'CommandController@buildGoogleSitemap')->name('adminCommand.buildGoogleSitemap');
    Route::get('cmd/find/invalid-links', 'CommandController@findInvalidLinks')->name('adminCommand.findInvalidLinks');
});

// 前台
Route::post('newmessage', ['uses' => 'CommandController@newMessage', 'as' => 'userCommand.newmessage']);
Route::get('search', ['uses' => 'CommandController@search', 'as' => 'userCommand.search']);
Route::fallback('AnyPage');
