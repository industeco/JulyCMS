<?php

namespace July;

use App\Utils\Settings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use July\Core\Config\Config as JulyConfig;
use July\Core\Config\PartialViewLinkage;
use July\Core\Config\PathAliasLinkage;
use July\Core\Node\CatalogPositionsLinkage;
use July\Core\Node\Node;

class JulyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 生成 twig 单例
        $this->app->singleton('twig', function () {
            $loader = new \Twig\Loader\FilesystemLoader('template', frontend_path());
            if (config('app.debug')) {
                $twig = new \Twig\Environment($loader, ['debug' => true]);
                $twig->addExtension(new \Twig\Extension\DebugExtension);
            } else {
                $twig = new \Twig\Environment($loader);
            }

            $twig->addExtension(new \Twig\Extension\StringLoaderExtension());
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
        // 启动完成后从数据库中加载配置
        // $this->loadConfigFromDatabase();
    }

    // /**
    //  * 从数据库中加载配置
    //  */
    // protected function loadConfigFromDatabase()
    // {
    //     $this->app->booted(function() {
    //         try {
    //             // 加载数据库中的配置
    //             JulyConfig::overwrite();
    //         } catch (\Throwable $th) {
    //             if (! $th instanceof QueryException) {
    //                 throw $th;
    //             }
    //         }
    //     });
    // }
}
