<?php

namespace July;

use App\Utils\Pocket;
use Illuminate\Support\Str;
use July\Base\RouteRegisterInterface;
use July\Base\SeederProviderInterface;
use Symfony\Component\Finder\Finder;

class July
{
    /**
     * 起始目录
     *
     * @var string
     */
    protected static $basePath = __DIR__.\DIRECTORY_SEPARATOR.'Core';

    /**
     * 获取插件类的开头部分
     *
     * @var string
     */
    protected static $classBase = 'July\\Core\\';

    /**
     * 尝试从缓存获取值，如果不存在则使用 discover 方法，然后存入缓存
     *
     * @param  string $key
     * @return array
     */
    public static function takeout(string $key)
    {
        $pocket = Pocket::make(static::class)->setKey($key);
        if ($value = $pocket->get()) {
            return $value->value();
        }

        if (method_exists(static::class, $method = 'discover'.Str::studly($key))) {
            $value = static::{$method}();
            $pocket->put($value);
            return $value;
        }

        return [];
    }

    /**
     * 收集插件迁移目录
     *
     * @return array
     */
    public static function discoverMigrationPaths()
    {
        $paths = [];
        $finder = Finder::create()->directories()->name('migrations')->in(static::$basePath);
        foreach ($finder as $dir) {
            $paths[] = $dir->getRealPath();
        }
        return $paths;
    }

    /**
     * 收集插件迁移目录
     *
     * @return array
     */
    public static function discoverSeeders()
    {
        $seeders = [];
        $finder = Finder::create()->files()->name('SeederProvider.php')->in(static::$basePath);
        foreach ($finder as $file) {
            $class = static::normalizeClass($file->getRelativePathname());
            if (is_subclass_of($class, SeederProviderInterface::class)) {
                $seeders = array_merge($seeders, $class::getSeeders());
            }
        }
        return $seeders;
    }

    /**
     * 收集路由注册器
     *
     * @return array
     */
    public static function discoverRoutes()
    {
        $routes = [];
        $finder = Finder::create()->files()->name('RouteRegister.php')->in(static::$basePath);
        foreach ($finder as $file) {
            $class = static::normalizeClass($file->getRelativePathname());
            if (is_subclass_of($class, RouteRegisterInterface::class)) {
                $routes[] = $class;
            }
        }
        return $routes;
    }

    /**
     * 将路径转化为类名
     *
     * @param  string $path
     * @return string
     */
    protected static function normalizeClass($path)
    {
        return static::$classBase.str_replace('/', '\\', preg_replace('/\.php$/i', '', $path));
    }
}
