<?php

namespace App\Providers;

use App\Entity\EntityManager;
use App\EntityField\FieldTypes\FieldTypeManager;
use App\Support\JustInTwig;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
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
        // 登记 twig 单例
        $this->registerTwig();

        // 登记实体字段类型
        EntityManager::register($this->discoverEntities());

        // 登记实体
        FieldTypeManager::register($this->discoverEntityFieldTypes());

        // 登记 Actions
        if ($actions = $this->discoverActions()) {
            config()->set('app.actions', array_merge(config('app.actions', []), $actions));
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 扩展 Blade
        $this->extendBlade();

        // 获取路由短网址
        JustInTwig::macro('short_url', function($name, ...$parameters) {
            return short_url($name, $parameters);
        });
    }

    /**
     * 登记 twig 单例
     */
    protected function registerTwig()
    {
        $this->app->singleton('twig', function() {
            $loader = new \Twig\Loader\FilesystemLoader('template', frontend_path());
            $twig = new \Twig\Environment($loader, ['debug' => config('app.debug')]);

            if ($twig->isDebug()) {
                $twig->addExtension(new \Twig\Extension\DebugExtension);
            }
            $twig->addExtension(new \Twig\Extension\StringLoaderExtension);

            foreach (config('app.twig_extensions') as $extension) {
                $twig->addExtension(new $extension);
            }

            return $twig;
        });

        // 将 JustInTwig 对象作为全局变量添加到 $twig
        $this->app->afterResolving('twig', function(\Twig\Environment $twig) {
            $twig->addGlobal('_jit', new JustInTwig);
            return $twig;
        });
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

    /**
     * 获取实体类
     *
     * @return array
     */
    protected function discoverEntities()
    {
        return [];
    }

    /**
     * 获取实体字段类型
     *
     * @return array
     */
    protected function discoverEntityFieldTypes()
    {
        return [
            // \App\EntityField\FieldTypes\Any::class,
            \App\EntityField\FieldTypes\Input::class,
            \App\EntityField\FieldTypes\Text::class,
            \App\EntityField\FieldTypes\File::class,
            \App\EntityField\FieldTypes\Html::class,
            \App\EntityField\FieldTypes\Image::class,
            \App\EntityField\FieldTypes\Url::class,
            \App\EntityField\FieldTypes\PathAlias::class,
            \App\EntityField\FieldTypes\Reference::class,
            \App\EntityField\FieldTypes\MultiReference::class,
        ];
    }

    /**
     * 获取 Actions 列表
     */
    protected function discoverActions()
    {
        return [
            \App\Http\Actions\ClearCache::class,
            \App\Http\Actions\BuildGoogleSitemap::class,
        ];
    }
}
