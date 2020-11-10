<?php

namespace July;

use July\Base\RouteProviderInterface;
use July\Base\SeederProviderInterface;
use Symfony\Component\Finder\Finder;

class July
{
    /**
     * 模块名列表，大小写必须与模块目录名一致
     *
     * @var array
     */
    const MODULES = [
        'Installer',
        'Config',
        'EntityField',
        'User',
        'Taxonomy',
        'Node',
        'Message',
        'Relationship',
    ];

    /**
     * 缓存模块目录
     *
     * @var array
     */
    public static $discoveredModules = [];

    /**
     * 查找并缓存模块目录
     *
     * @return array
     */
    public static function discoverModules()
    {
        if (empty(static::$discoveredModules)) {
            static::$discoveredModules = [];
            foreach (self::MODULES as $module) {
                if (is_dir($path = july_path('Core'.\DIRECTORY_SEPARATOR.$module))) {
                    static::$discoveredModules['Core\\'.$module] = $path;
                } elseif (is_dir($path = july_path($module))) {
                    static::$discoveredModules[$module] = $path;
                }
            }
        }

        return static::$discoveredModules;
    }

    /**
     * 收集组件迁移目录
     *
     * @return array
     */
    public static function discoverMigrationPaths()
    {
        $finder = Finder::create()->directories()->name('migrations')->in(july_path('Core'));
        $paths = [];
        foreach ($finder as $dir) {
            $paths[] = $dir->getRealPath();
        }

        return $paths;

        // $paths = [];
        // foreach (static::discoverModules() as $module => $path) {
        //     if (is_dir($path .= \DIRECTORY_SEPARATOR.'migrations')) {
        //         $paths[] = $path;
        //     }
        // }

        // return $paths;
    }

    /**
     * 收集组件数据填充器
     *
     * @return array
     */
    public static function discoverSeeders()
    {
        $finder = Finder::create()->files()->name('SeederProvider.php')->in(july_path('Core'));
        $seeders = [];
        foreach ($finder as $file) {
            $class = 'July\\Core\\'.static::normalizeClass($file->getRelativePathname());
            if (is_subclass_of($class, SeederProviderInterface::class)) {
                $seeders = array_merge($seeders, $class::getSeeders());
            }
        }

        return $seeders;

        // $seeders = [];
        // foreach (static::discoverModules() as $module => $path) {
        //     $class = "July\\{$module}\\SeederProvider";
        //     if (is_subclass_of($class, SeederProviderInterface::class)) {
        //         $seeders = array_merge($seeders, $class::getSeeders());
        //     }
        // }

        // return $seeders;
    }

    /**
     * 登记组件路由
     *
     * @return array
     */
    public static function discoverRoutes()
    {
        $finder = Finder::create()->files()->name('RouteProvider.php')->in(july_path('Core'));
        $routes = [];
        foreach ($finder as $file) {
            $class = 'July\\Core\\'.static::normalizeClass($file->getRelativePathname());
            if (is_subclass_of($class, RouteProviderInterface::class)) {
                $routes[] = $class;
            }
        }

        return $routes;

        // $routes = [];
        // foreach (static::discoverModules() as $module => $path) {
        //     $class = "July\\{$module}\\RouteProvider";
        //     if (is_subclass_of($class, RouteProviderInterface::class)) {
        //         $routes[] = $class;
        //     }
        // }

        // return $routes;
    }

    protected static function normalizeClass($path)
    {
        return str_replace('/', '\\', preg_replace('/\.php$/i', '', $path));
        // return trim($path, '\\');
    }
}
