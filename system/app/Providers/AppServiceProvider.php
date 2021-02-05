<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

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

        $this->extendBlade();
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

    /**
     * 扩展 Blade
     */
    protected function extendBlade()
    {
        Blade::directive('jjson', function ($expression) {
            if (Str::startsWith($expression, '(')) {
                $expression = substr($expression, 1, -1);
            }

            $parts = explode(',', $expression);

            $options = 'JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE'.(isset($parts[1]) ? '|'.trim($parts[1]) : '');

            $depth = isset($parts[2]) ? trim($parts[2]) : 512;

            return "<?php echo json_encode($parts[0], $options, $depth) ?>";
        });
    }
}
