<?php

namespace July\Core\Node;

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
            ->middleware(['web','admin','auth'])
            ->group(function() {
                // 字段
                Route::get('node_fields/exists/{id}', [Controllers\NodeFieldController::class, 'isExist'])
                    ->name('node_fields.is_exist');

                Route::resource('node_fields', Controllers\NodeFieldController::class)
                    ->parameters(['node_fields' => 'nodeField'])
                    ->names('node_fields');

                // 内容类型
                Route::get('node_types/exists/{id}', [Controllers\NodeTypeController::class, 'isExist'])
                    ->name('node_types.is_exist');

                // Route::get('node_types/{nodeType}/translate', [Controllers\NodeTypeController::class, 'translate'])
                //     ->name('node_types.translate');

                // Route::get('node_types/{nodeType}/translate/{langcode}', [Controllers\NodeTypeController::class, 'edit'])
                //     ->name('node_types.translate_to');

                Route::resource('node_types', Controllers\NodeTypeController::class)
                    ->parameters(['node_types' => 'nodeType'])
                    ->names('node_types');

                // 目录
                // 检查目录 id 是否存在
                Route::get('catalogs/exists/{id}', [Controllers\CatalogController::class, 'isExist'])
                    ->name('catalogs.is_exist');

                Route::get('catalogs/{catalog}/sort', [Controllers\CatalogController::class, 'sort'])
                    ->name('catalogs.sort');

                Route::put('catalogs/{catalog}/sort', [Controllers\CatalogController::class, 'updateOrders'])
                    ->name('catalogs.updateOrders');

                Route::resource('catalogs', Controllers\CatalogController::class)
                    ->parameters(['catalogs' => 'catalog'])
                    ->names('catalogs');

                // 内容
                Route::get('nodes/create/{nodeType}', [Controllers\NodeController::class, 'create'])
                    ->name('nodes.create');

                Route::get('nodes/choose/node_type', [Controllers\NodeController::class, 'chooseNodeType'])
                    ->name('nodes.choose_node_type');

                Route::get('nodes/{node}/languages', [Controllers\NodeController::class, 'chooseLanguage'])
                    ->name('nodes.languages');

                Route::get('nodes/{node}/translate/{langcode}', [Controllers\NodeController::class, 'edit'])
                    ->name('nodes.translate');

                Route::post('nodes/render', [Controllers\NodeController::class, 'render'])
                    ->name('nodes.render');

                Route::resource('nodes', Controllers\NodeController::class)
                    ->except(['create'])
                    ->parameters(['nodes' => 'node'])
                    ->names('nodes');
            });
    }
}
