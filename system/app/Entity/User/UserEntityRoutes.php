<?php

namespace App\Entity\User;

use App\Contracts\RouteProviderInterface;
use Illuminate\Support\Facades\Route;

class UserEntityRoutes implements RouteProviderInterface
{
    public static function register()
    {
        Route::prefix(config('jc.background_route_prefix', 'admin'))
            ->middleware(['web', 'admin'])
            ->namespace('App\Entity\User\Controllers')
            ->group(function() {
                Route::get('login', 'LoginController@showLoginForm')->name('admin.login');
                Route::post('login', 'LoginController@login')->name('admin.auth');
                Route::get('logout', 'LoginController@logout')->name('admin.logout');
            });
    }
}
