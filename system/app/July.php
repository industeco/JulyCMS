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
        Route::namespace('Auth')->group(function() use ($lang) {
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

        // 标签
        Route::resource('tags', 'TagController')->parameters([
            'tags' => 'tag',
        ])->names($lang.'tags');

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
        Route::post('nodes/render', 'NodeController@render')->name($lang.'nodes.render');
        Route::resource('nodes', 'NodeController')->parameters([
            'nodes' => 'node',
        ])->names($lang.'nodes');

        // 文件管理
        Route::get('/medias', ['uses'=>'MediaController@index', 'as'=>$lang.'media.index']);
        Route::get('/medias/select', ['uses'=>'MediaController@select', 'as'=>$lang.'media.index']);
        Route::post('/medias/under', ['uses'=>'MediaController@under', 'as'=>$lang.'media.under']);
        Route::post('/medias/upload', ['uses'=>'MediaController@upload', 'as'=>$lang.'media.upload']);
        Route::post('/medias/create/{type?}', ['uses'=>'MediaController@create', 'as'=>$lang.'media.create_folder']);
        Route::post('/medias/rename/{type}', ['uses'=>'MediaController@rename', 'as'=>$lang.'media.rename_file']);
        Route::post('/medias/delete/{file}', ['uses'=>'MediaController@delete', 'as'=>$lang.'media.delete_file']);

        // 其它
        Route::get('checkunique/node_fields/{truename}', 'NodeFieldController@unique');
        Route::get('checkunique/node_types/{truename}', 'NodeTypeController@unique');
        Route::get('checkunique/catalogs/{truename}', 'CatalogController@unique');
        Route::post('checkunique/node__url', 'NodeFieldController@uniqueUrl');

        // 命令执行
        Route::get('cmd/clearcache', ['uses' => 'CommandController@clearCache', 'as' => 'cmd.clearcache']);
        Route::get('cmd/rebuildindex', ['uses' => 'CommandController@rebuildIndex', 'as' => 'cmd.rebuildindex']);
        Route::get('cmd/buildgooglesitemap', ['uses' => 'CommandController@buildGoogleSitemap', 'as' => 'cmd.buildgooglesitemap']);

    }

    public static function webRoutes()
    {
        Route::post('newmessage', ['uses' => 'CommandController@newMessage', 'as' => 'cmd.newmessage']);
        Route::get('search', ['uses' => 'CommandController@search', 'as' => 'cmd.search']);
        Route::get('{any}', 'AnyPage')->where('any', '.*');
    }
}
