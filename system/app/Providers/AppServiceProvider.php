<?php

namespace App\Providers;

use App\Entity\EntityManager;
use App\EntityField\FieldTypes\FieldTypeManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 注册 twig 单例
        $this->registerTwig();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 启动管理类
        $this->bootManagers();

        // 添加视图命名空间
        View::addNamespace('backend', backend_path('template'));
    }

    /**
     * 注册 twig 单例
     */
    protected function registerTwig()
    {
        $this->app->singleton('twig', function () {
            $loader = new \Twig\Loader\FilesystemLoader('template', frontend_path());
            $twig = new \Twig\Environment($loader, ['debug' => config('app.debug')]);

            if ($twig->isDebug()) {
                $twig->addExtension(new \Twig\Extension\DebugExtension);
            }
            $twig->addExtension(new \Twig\Extension\StringLoaderExtension);
            // $twig->addExtension(new \July\Support\Twig\EntityQueryExtension);

            return $twig;
        });
    }

    /**
     * 启动管理类
     */
    protected function bootManagers()
    {
        foreach (config('app.managers') as $manager) {
            if (class_exists($manager)) {
                $manager::discoverIfNotDiscovered();
            }
        }
    }
}
