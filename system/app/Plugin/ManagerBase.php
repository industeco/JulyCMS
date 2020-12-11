<?php

namespace App\Plugin;

use App\Utils\Pocket;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

abstract class ManagerBase
{
    /**
     * 获取插件类的开头部分
     *
     * @return string
     */
    protected static $classBase = '';

    /**
     * 收集插件迁移目录
     *
     * @return array
     */
    public static function discoverMigrationPaths()
    {
        $paths = [];
        $finder = Finder::create()->directories()->name('migrations')->in(__DIR__);
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
        $finder = Finder::create()->files()->name('ModuleSeeder.php')->in(__DIR__);
        foreach ($finder as $file) {
            $class = static::normalizeClass($file->getRelativePathname());
            if (is_subclass_of($class, ModuleSeederBase::class)) {
                $seeders[] = $class;
            }
        }
        return $seeders;
    }

    /**
     * 收集插件路由
     *
     * @return array
     */
    public static function discoverRoutes()
    {
        $routes = [];
        $finder = Finder::create()->files()->name('ModuleRoutes.php')->in(__DIR__);
        foreach ($finder as $file) {
            $class = static::normalizeClass($file->getRelativePathname());
            if (is_subclass_of($class, ModuleRoutesBase::class)) {
                $routes[] = $class;
            }
        }
        return $routes;
    }

    /**
     * 尝试从缓存获取值，如果不存在则使用 discover 方法，然后存入缓存
     *
     * @param  string $key
     * @return array
     */
    public static function takeout(string $key)
    {
        $pocket = Pocket::make(static::class)->useKey($key);
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
