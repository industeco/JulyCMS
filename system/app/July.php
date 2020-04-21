<?php

namespace App;

use Illuminate\Support\Facades\Route;

class July
{
    public static function AdminRoutes($lang = '')
    {
        if ($lang) {
            $lang .= '.';
        }
        return function () use ($lang) {
            /* @var \Illuminate\Routing\Router $router */
            // 登录
            Route::namespace('Auth')->group(function() use ($lang) {
                Route::get('/', 'AdminLoginController@home')->name($lang.'admin.home');
                Route::get('login', 'AdminLoginController@showLoginForm')->name($lang.'admin.login');
                Route::post('login', 'AdminLoginController@login')->name($lang.'admin.auth');
                Route::get('logout', 'AdminLoginController@logout')->name($lang.'admin.logout');
            });

            // 配置
            Route::resource('configs', 'ConfigController')->parameters([
                'configs' => 'config',
            ])->names($lang.'configs');

            // 字段
            Route::resource('node_fields', 'NodeFieldController')->parameters([
                'node_fields' => 'nodeField',
            ])->names($lang.'node_fields');

            // 内容类型
            Route::resource('node_types', 'NodeTypeController')->parameters([
                'node_types' => 'nodeType',
            ])->names($lang.'node_types');

            // 目录
            Route::get('catalogs/{catalog}/reorder', 'CatalogController@reorder')->name($lang.'catalogs.reorder');
            Route::put('catalogs/{catalog}/sort', 'CatalogController@sort')->name($lang.'catalogs.sort');
            Route::resource('catalogs', 'CatalogController')->parameters([
                'catalogs' => 'catalog',
            ])->names($lang.'catalogs');

            // 内容
            Route::get('nodes/create/{nodeType}', 'NodeController@createWith')->name($lang.'nodes.create_with');
            Route::get('nodes/{node}/translate', 'NodeController@translate')->name($lang.'nodes.translate');
            Route::get('nodes/{node}/translate/{langcode}', 'NodeController@edit')->name($lang.'nodes.translate_to');
            Route::resource('nodes', 'NodeController')->parameters([
                'nodes' => 'node',
            ])->names($lang.'nodes');

            // 其它
            Route::get('checkunique/node_fields/{truename}', 'NodeFieldController@unique');
            Route::get('checkunique/node_types/{truename}', 'NodeTypeController@unique');
            Route::get('checkunique/catalogs/{truename}', 'CatalogController@unique');
            Route::post('checkunique/node__url', 'NodeFieldController@uniqueUrl');
        };
    }
}
