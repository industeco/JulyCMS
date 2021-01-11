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
        $this->loadModuleRoutes();

        // 融合插件设置
        if (is_file($file = $this->getModulePath('config/config.php'))) {
            $this->mergeConfigFrom($file, $this->getModuleName());
        }

        // 登记视图目录
        if (is_dir($path = $this->getModulePath('views'))) {
            $this->loadViewsFrom($path, $this->getModuleName());
            // View::addNamespace($this->getModuleName(), $path);
        }

        // 登记翻译文本目录
        if (is_dir($path = $this->getModulePath('lang'))) {
            $this->loadTranslationsFrom($path, $this->getModuleName());
        }

        // 登记迁移目录
        if (is_dir($path = $this->getModulePath('migrations'))) {
            $this->loadMigrationsFrom($path);
        }
    }

    /**
     * 加载组件路由
     *
     * @return void
     */
    protected function loadModuleRoutes()
    {
        Route::middleware('web')->group($this->getModulePath('routes/web.php'));
    }

    /**
     * 获取组件根目录
     *
     * @param  string|null $path
     * @return string
     */
    protected function getModulePath(?string $path = null)
    {
        return $this->getModuleBasePath().(is_null($path) ? '' : '/'.$path);
    }

    /**
     * 获取组件根目录
     *
     * @return string
     */
    protected function getModuleBasePath()
    {
        return base_path('modules/'.$this->getModuleName());
    }

    /**
     * 组件名
     *
     * @return string
     */
    protected function getModuleName()
    {
        return strtolower(basename(static::class, 'ServiceProvider'));
    }
}
