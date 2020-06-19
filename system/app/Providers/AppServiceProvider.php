<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
            // 查找字段类型
            // \App\FieldTypes\FieldType::findFieldTypes();

            // 加载数据库中的配置
            \App\Models\Config::loadConfigurations();
        } catch (\Throwable $th) {
            throw $th;
        }

        // 添加视图命名空间
        view()->addNamespace('admin', background_path('template'));
        // view()->addNamespace('theme', public_path('themes/default/template'));
    }
}
