<?php

namespace App\Providers;

use App\Utils\EventsBook;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use July\July;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->singleton('events_book', function() {
        //     return new EventsBook();
        // });

        // $this->app->terminating(function() {
        //     events()->store();
        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 添加视图命名空间
        view()->addNamespace('backend', backend_path('template'));

        // 登记迁移文件的路径
        if (!config('app.is_installed') || $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(July::takeout('migration_paths'));
        }
    }
}
