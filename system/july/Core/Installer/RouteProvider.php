<?php

namespace July\Core\Installer;

use Illuminate\Support\Facades\Route;
use July\Base\RouteProviderInterface;
use July\Core\Installer\Controllers\InstallController;

class RouteProvider implements RouteProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public static function register()
    {
        Route::middleware('web')
            ->group(function() {
                Route::get('install', [InstallController::class, 'home'])->name('install.home');
                Route::post('install', [InstallController::class, 'install'])->name('install.install');
                Route::post('install/migrate', [InstallController::class, 'migrate'])->name('install.migrate');
            });
    }
}
