<?php

namespace July\Core\Config;

use Illuminate\Support\Facades\Route;
use July\Base\RouteRegisterInterface;

class RouteRegister implements RouteRegisterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function register()
    {
        Route::prefix(config('app.route_prefix', 'admin'))
            ->middleware(['web','admin','auth'])
            ->group(function() {
                Route::get('configs/{group}', [Controllers\ConfigController::class, 'edit'])
                    ->name('configs.edit');

                Route::post('configs', [Controllers\ConfigController::class, 'update'])
                    ->name('configs.update');

                Route::post('path_aliases/exists', [Controllers\PathAliasController::class, 'isExist'])
                    ->name('path_aliases.is_exist');
            });
    }
}
