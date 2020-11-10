<?php

namespace July;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\QueryException;
use July\Core\Config\Config as JulyConfig;

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
