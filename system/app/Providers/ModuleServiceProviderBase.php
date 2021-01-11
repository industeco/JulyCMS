<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProviderBase extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 加载组件路由
        $this->loadRoutes();

        // 融合插件设置
        if (is_file($file = $this->modulePath('config.php'))) {
            $this->mergeConfigFrom($file, $this->moduleName());
        }

        // 登记视图目录
        if (is_dir($path = $this->modulePath('views'))) {
            $this->loadViewsFrom($path, $this->moduleName());
            // View::addNamespace($this->moduleName(), $path);
        }

        // 登记翻译文本目录
        if (is_dir($path = $this->modulePath('lang'))) {
            $this->loadTranslationsFrom($path, $this->moduleName());
        }

        // 登记迁移目录
        if (is_dir($path = $this->modulePath('migrations'))) {
            $this->loadMigrationsFrom($path);
        }
    }

    /**
     * 加载组件路由
     *
     * @return void
     */
    protected function loadRoutes()
    {
        Route::middleware('web')->group($this->modulePath('routes/web.php'));
    }

    /**
     * 获取组件根目录
     *
     * @param  string|null $path
     * @return string
     */
    protected function modulePath(?string $path = null)
    {
        return $this->moduleBasePath().(is_null($path) ? '' : '/'.$path);
    }

    /**
     * 组件名
     *
     * @return string
     */
    protected function moduleName()
    {
        return strtolower(basename(static::class, 'ServiceProvider'));
    }

    /**
     * 获取组件根目录
     *
     * @return string
     */
    protected function moduleBasePath()
    {
        return base_path('modules/'.$this->moduleName());
    }
}
