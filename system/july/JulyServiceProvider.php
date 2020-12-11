<?php

namespace July;

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
        // 生成 twig 单例
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
        //
    }
}
