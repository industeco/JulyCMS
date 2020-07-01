<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\QueryException;
use App\Models\Config as ConfigModel;

class AppServiceProvider extends ServiceProvider
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
        try {
            // 加载数据库中的常规配置
            ConfigModel::loadConfigurations();
        } catch (\Throwable $th) {
            if (!($th instanceof QueryException)) {
                throw $th;
            }
        }

        // 添加视图命名空间
        view()->addNamespace('admin', background_path('template'));
        // view()->addNamespace('theme', public_path('themes/default/template'));
    }
}
