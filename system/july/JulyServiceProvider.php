<?php

namespace July;

use App\Database\SeederManager;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class JulyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 注册 twig 单例
        $this->app->singleton('twig', function () {
            $loader = new \Twig\Loader\FilesystemLoader('template', frontend_path());
            $twig = new \Twig\Environment($loader, ['debug' => config('app.debug')]);

            if ($twig->isDebug()) {
                $twig->addExtension(new \Twig\Extension\DebugExtension);
            }
            $twig->addExtension(new \Twig\Extension\StringLoaderExtension);
            $twig->addExtension(new \July\Support\Twig\EntityQueryExtension);

            return $twig;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (! ($this->app instanceof CachesRoutes && $this->app->routesAreCached())) {
            optional($this->app)->routesAreCached();
            $this->loadRoutes();
        }

        if (!config('app.is_installed') || $this->app->runningInConsole()) {
            // 注册数据填充器类
            SeederManager::registerSeeders(function() {
                return July::takeout('seeders');
            });

            // 登记迁移文件的路径
            $this->loadMigrationsFrom(July::takeout('migration_paths'));
        }
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function loadRoutes()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * 登记 web 路由
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        // 注册核心组件路由
        foreach (July::takeout('routes') as $routes) {
            $routes::register();
        }
    }

    /**
     * 登记 api 路由
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        //
    }
}
