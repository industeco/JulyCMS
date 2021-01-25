<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

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

        $namespace = $this->getModuleName();

        // 融合插件设置
        if (is_file($file = $this->getModulePath('config/config.php'))) {
            $this->mergeConfigFrom($file, str_replace('/', '.', $namespace));
        }

        // 登记视图目录
        if (is_dir($path = $this->getModulePath('views'))) {
            $this->loadViewsFrom($path, $namespace);
        }

        // 登记翻译文本目录
        if (is_dir($path = $this->getModulePath('lang'))) {
            $this->loadTranslationsFrom($path, $namespace);
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
     * 获取组件内部路径
     *
     * @param  string|null $path
     * @return string
     */
    protected function getModulePath(?string $path = null)
    {
        return $this->getModuleRoot().($path ? '/'.$path : '');
    }

    /**
     * 获取组件根路径
     *
     * @return string
     */
    protected function getModuleRoot()
    {
        $ref = new ReflectionClass(static::class);
        return dirname(dirname($ref->getFileName()));
    }

    /**
     * 组件名
     *
     * @return string
     */
    protected function getModuleName()
    {
        $relativePath = substr($this->getModuleRoot(), strlen(base_path('modules/')));
        return str_replace('\\', '/', $relativePath);
    }
}
