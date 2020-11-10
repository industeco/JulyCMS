<?php

namespace July\Core\User;

use Illuminate\Support\Facades\Route;
use July\Base\RouteProviderInterface;

class RouteProvider implements RouteProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public static function register()
    {
        Route::prefix(config('jc.site.backend_route_prefix', 'admin'))
            ->middleware(['web', 'admin'])
            ->group(function() {
                Route::get('login',  [Controllers\LoginController::class, 'showLoginForm'])->name('admin.login');
                Route::post('login', [Controllers\LoginController::class, 'login'])->name('admin.auth');
                Route::get('logout', [Controllers\LoginController::class, 'logout'])->name('admin.logout');
            });
    }
}
