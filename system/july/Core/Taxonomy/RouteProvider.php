<?php

namespace July\Core\Taxonomy;

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
            ->middleware(['web', 'admin', 'auth'])
            ->group(function() {
                Route::get('tags', [Controllers\TagController::class, 'index'])
                    ->name('tags.index');

                Route::post('tags', [Controllers\TagController::class, 'update'])
                    ->name('tags.update');
            });
    }
}
