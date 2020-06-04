<?php

namespace App\Providers;

use App\Models\Config;
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
            // 加载数据库中的配置
            Config::loadConfigurations();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // 添加视图命名空间
        view()->addNamespace('admin', public_path('themes/admin/template'));
        view()->addNamespace('theme', public_path('themes/default/template'));
    }
}
