<?php

namespace App\Providers;

use App\Database\SeederManager;
use App\Plugin\Plugin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Closure;
use DatabaseSeeder;
use Symfony\Component\Finder\Finder;

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

        // 启动插件
        $this->bootPlugins();
    }

    /**
     * 加载插件
     *
     * @return void
     */
    protected function bootPlugins()
    {
        foreach (Plugin::all() as $plugin) {
            // 融合插件设置
            $this->mergeConfigFrom($plugin->getConfigFile(), $plugin->getName());

            // 添加翻译文本目录
            $this->loadTranslationsFrom($plugin->getTranslationsPath(), $plugin->getName());

            // 添加视图目录
            $this->loadViewsFrom($plugin->getViewsPath(), $plugin->getName());

            // 登记迁移路径
            $this->loadMigrationsFrom($plugin->getMigrationsPath());

            // 登记数据填充器路径
            $this->loadSeedersFrom($plugin->getSeedersPath(), $plugin->getNamespace().'\\seeds\\');
        }
    }

    /**
     * 登记数据填充器路径
     */
    protected function loadSeedersFrom($path, string $namespace = '')
    {
        $this->callAfterResolving(DatabaseSeeder::class, function(DatabaseSeeder $seeder) use($path, $namespace) {
            $seeders = [];
            if (is_string($path)) {
                foreach (Finder::create()->files()->name('*.php')->in($path)->depth(0) as $file) {
                    if (class_exists($class = $namespace.$file->getBasename('.php'))) {
                        $seeders[] = $class;
                    }
                }
            } elseif ($path instanceof Closure) {
                $seeders = $path();
            }

            $seeder->registerSeeders($seeders);
        });
    }
}
