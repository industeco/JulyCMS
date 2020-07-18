<?php

namespace App\Installer;

use App\Contracts\RouteProviderInterface;
use Illuminate\Support\Facades\Route;

class InstallerRoutes implements RouteProviderInterface
{
    public static function register()
    {
        Route::middleware('web')
            ->namespace('App\Installer\Controllers')
            ->group(function() {
                Route::get('install', ['uses'=>'InstallController@home', 'as'=>'install.home']);
                Route::post('install', ['uses'=>'InstallController@install', 'as'=>'install.install']);
                Route::post('install/migrate', ['uses'=>'InstallController@migrate', 'as'=>'install.migrate']);
            });
    }
}
