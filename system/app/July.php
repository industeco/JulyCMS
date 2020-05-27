<?php

namespace App;

use App\Filemanager\Controllers;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

class July
{
    public static function adminRoutes($lang = '')
    {
        if ($lang) {
            $lang .= '.';
        }

        Route::get('/', 'Dashboard')->name($lang.'admin.home');

        /* @var \Illuminate\Routing\Router $router */
        // 登录
        Route::namespace('Auth')->name($lang.'admin.')->group(function() {
            Route::get('login', 'AdminLoginController@showLoginForm')->name('login');
            Route::post('login', 'AdminLoginController@login')->name('auth');
            Route::get('logout', 'AdminLoginController@logout')->name('logout');
        });

        // 配置
        Route::name($lang.'configs.')->group(function() {
            Route::get('configs/basic', 'JulyConfigController@editBasicSettings')->name('basic.edit');
            Route::post('configs/basic', 'JulyConfigController@updateBasicSettings')->name('basic.update');
            Route::get('configs/language', 'JulyConfigController@editLanguageSettings')->name('language.edit');
            Route::post('configs/language', 'JulyConfigController@updateLanguageSettings')->name('language.update');
        });

        // 字段
        Route::resource('node_fields', 'NodeFieldController')->parameters([
            'node_fields' => 'nodeField',
        ])->names($lang.'node_fields');

        // 内容类型
        Route::name($lang.'node_types.')->group(function() {
            Route::get('node_types/{nodeType}/translate', 'NodeTypeController@translate')->name('translate');
            Route::resource('node_types', 'NodeTypeController')->parameters([
                'node_types' => 'nodeType',
            ])->names('');
        });

        // 标签
        Route::name($lang.'tags.')->group(function() {
            Route::get('tags', 'TagController@index')->name('index');
            Route::post('tags', 'TagController@update')->name('update');
        });

        // 目录
        Route::name($lang.'catalogs.')->group(function() {
            Route::get('catalogs/{catalog}/sort', 'CatalogController@sort')->name('sort');
            Route::put('catalogs/{catalog}/sort', 'CatalogController@updateOrders')->name('updateOrders');
            Route::resource('catalogs', 'CatalogController')->parameters([
                'catalogs' => 'catalog',
            ])->names('');
        });

        // 内容
        Route::name($lang.'nodes.')->group(function() {
            Route::get('nodes/create/{nodeType}', 'NodeController@createWith')->name('create_with');
            Route::get('nodes/{node}/translate', 'NodeController@translate')->name('translate');
            Route::get('nodes/{node}/translate/{langcode}', 'NodeController@edit')->name('translate_to');
            Route::post('nodes/render', 'NodeController@render')->name('render');
            Route::resource('nodes', 'NodeController')->parameters([
                'nodes' => 'node',
            ])->names('');
        });

        // 文件管理
        Route::name($lang.'media.')->group(function() {
            Route::get('/media', 'MediaController@index')->name('index');
            Route::get('/media/select', 'MediaController@select')->name('select');
            Route::post('/media/under', 'MediaController@under')->name('under');
            Route::post('/media/upload', 'MediaController@upload')->name('upload');
            Route::post('/media/mkdir', 'MediaController@mkdir')->name('mkdir');
            Route::post('/media/rename/{type}', 'MediaController@rename')->name('rename');
            Route::post('/media/delete/{type}', 'MediaController@delete')->name('delete');
        });

        // 其它
        Route::name('checkunique.')->group(function() {
            Route::get('checkunique/node_fields/{truename}', 'NodeFieldController@unique')->name('node_fields');
            Route::get('checkunique/node_types/{truename}', 'NodeTypeController@unique')->name('node_types');
            Route::get('checkunique/catalogs/{truename}', 'CatalogController@unique')->name('catalogs');
            Route::post('checkunique/node__url', 'NodeFieldController@uniqueUrl')->name('url');
        });

        // 命令执行
        Route::name('adminCommand.')->group(function() {
            Route::get('search', 'CommandController@searchDatabase')->name('search');
            Route::post('cmd/update/password', 'CommandController@updateAdminPassword')->name('updatePassword');
            Route::get('cmd/clear/cache', 'CommandController@clearCache')->name('clearCache');
            Route::get('cmd/build/index', 'CommandController@buildIndex')->name('buildIndex');
            Route::get('cmd/build/google-sitemap', 'CommandController@buildGoogleSitemap')->name('buildGoogleSitemap');
            Route::get('cmd/find/invalid-links', 'CommandController@findInvalidLinks')->name('findInvalidLinks');
        });
    }
}
