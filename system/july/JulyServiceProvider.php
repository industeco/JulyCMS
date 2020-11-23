<?php

namespace July;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use July\Core\Config\Config as JulyConfig;
use July\Core\Config\PartialViewLinkage;
use July\Core\Config\PathAliasLinkage;
use July\Core\Node\CatalogPositionsLinkage;
use July\Core\Node\Node;

class JulyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 启动完成后从数据库中加载配置
        $this->loadConfigFromDatabase();
    }

    /**
     * 从数据库中加载配置
     */
    protected function loadConfigFromDatabase()
    {
        $this->app->booted(function() {
            try {
                // 加载数据库中的配置
                JulyConfig::overwrite();
            } catch (\Throwable $th) {
                if (! $th instanceof QueryException) {
                    throw $th;
                }
            }
        });
    }
}
