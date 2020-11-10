<?php

namespace July\Core\Entity;

use App\Utils\Pocket;
use Illuminate\Support\Str;
use July\July;
use Symfony\Component\Finder\Finder;

class EntityManager
{
    protected static $entities = [];

    /**
     * 判断是否实体对象
     *
     * @param  object $instance
     * @return bool
     */
    public static function isEntity($instance)
    {
        return $instance instanceof EntityInterface;
    }

    /**
     * 判断是否实体类
     *
     * @param string $class
     * @return bool
     */
    public static function isEntityClass($class)
    {
        if (! class_exists($class)) {
            return false;
        }

        $cls = new \ReflectionClass($class);
        return $cls->isInstantiable() && $cls->implementsInterface(EntityInterface::class);
    }

    /**
     * 从实体名解析出实体类，或 null
     *
     * @param  string  $entityName 实体名（小写 + 下划线）
     * @return string|null
     */
    public static function resolveName(string $name)
    {
        if (empty(static::$entities)) {
            static::discoverEntities();
        }

        return array_search($name, static::$entities) ?: null;
    }

    /**
     * 从实体路径解析出实体对象，失败则返回 null
     *
     * @param  string  $path 实体路径
     * @return \July\Core\Entity\EntityInterface|null
     */
    public static function resolvePath(string $path)
    {
        [$name, $id] = static::splitEntityPath($path);

        if ($entity = static::resolveName($name)) {
            return $entity::find($id);
        }

        return null;
    }

    /**
     * 从文件系统（或缓存）找出所有实体类
     *
     * @return void
     */
    public static function discoverEntities()
    {
        if ('production' !== config('app.env')) {
            static::$entities = static::discoverEntityFromFiles();
            return;
        }

        $pocket = new Pocket(static::class);

        if ($result = $pocket->get('entities')) {
            static::$entities = $result->value;
        } else {
            $pocket->put('entities', static::$entities = static::discoverEntityFromFiles());
        }
    }

    /**
     * 从文件系统找出所有实体类
     *
     * @return array
     */
    protected static function discoverEntityFromFiles()
    {
        // 使用 Symfony\Finder 获取文件
        $entities = [];

        $files = Finder::create()->files()->name('*.php')
                    ->in(july_path('Core'))
                    ->notPath(['migrations', 'seeds', 'Controllers', 'Exceptions'])
                    ->notName(['*Interface.php', '*Base.php', '*Trait.php']);

        foreach ($files as $file) {
            // 获取类名
            $class = 'July\\Core\\'.static::normalizeClass($file->getRelativePathname());

            // 判断是否实体类
            if (static::isEntityClass($class)) {
                $entities[$class] = $class::getEntityName();
            }
        }

        return $entities;
    }

    /**
     * 拆分实体路径
     *
     * @param  string $path 实体路径；格式：实体名/实体对象 id
     * @return array
     */
    public static function splitEntityPath(string $path)
    {
        $path = explode('/', $path);
        $id = array_pop($path);
        $name = join('.', $path);
        return [$name, $id];
    }

    protected static function normalizeClass($path)
    {
        return str_replace('/', '\\', preg_replace('/\.php$/i', '', $path));
        // return trim($path, '\\');
    }

    // public static function bounce($entityName)
    // {
    //     if (false === strpos($entityName, '.')) {
    //         return Str::studly($entityName);
    //     }

    //     return collect(explode('.', $entityName))->map(function($str) {
    //         return Str::studly($str);
    //     })->join('\\');
    // }
}
