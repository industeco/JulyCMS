<?php

namespace July\Core\Entity;

use App\Utils\Pocket;
use Illuminate\Support\Str;
use July\July;
use Symfony\Component\Finder\Finder;

final class EntityManager
{
    /**
     * 核心实体类
     *
     * @var array
     */
    protected static $coreEntities = [];

    /**
     * 其它实体类
     *
     * @var array
     */
    protected static $entities = [];

    /**
     * 登记实体类（非核心）
     *
     * @param  array $entities
     * @return void
     */
    public static function registerEntities(array $entities)
    {
        foreach ($entities as $entity) {
            if (static::isEntityClass($entity)) {
                static::$entities[$entity::getEntityName()] = $entity;
            }
        }
    }

    /**
     * 从指定路径发现并登记实体类（非核心）
     *
     * @param  string $path 路径
     * @param  string $prefix 类前缀
     * @return void
     */
    public static function registerEntitiesInPath(string $path, string $prefix)
    {
        foreach (static::discoverEntitiesInPath($path, $prefix) as $entity) {
            static::$entities[$entity::getEntityName()] = $entity;
        }
    }

    /**
     * 登记核心实体类
     *
     * @param  bool $force 强制重新查找（通过清除缓存实现）
     * @return void
     */
    public static function registerCoreEntities($force = false)
    {
        $cachekey = 'core_entities';

        if ($force) {
            Pocket::make(static::class)->clear($cachekey);
        }

        $path = base_path('july/Core');
        $prefix = 'July\\Core\\';

        if ('production' === config('app.env')) {
            $pocket = new Pocket(static::class);

            if ($result = $pocket->get($cachekey)) {
                $entities = $result->value();
            } else {
                $entities = static::discoverEntitiesInPath($path, $prefix);
                $pocket->put($cachekey, $entities);
            }
        } else {
            $entities = static::discoverEntitiesInPath($path, $prefix);
        }

        static::$coreEntities = [];
        foreach ($entities as $entity) {
            static::$coreEntities[$entity::getEntityName()] = $entity;
        }
    }

    /**
     * 在文件系统中查找实体类
     *
     * @return array
     */
    public static function discoverEntitiesInPath(string $path, string $prefix)
    {
        if (! is_dir($path)) {
            return [];
        }

        // 实体类数组
        $entities = [];

        // 使用 Symfony\Finder 在 $path 目录下获取 .php 文件
        $files = Finder::create()->files()->in($path)
            ->name('*.php')
            ->notPath(['migrations', 'seeds', 'Controllers', 'Exceptions'])
            ->notName(['*Interface.php', '*Base.php', '*Trait.php']);

        // 依次将找到的 .php 文件按文件名转换为类名，并判断是否实体类
        // 如果是，则添加到 $entities
        foreach ($files as $file) {
            // 获取类名
            $class = $prefix.static::pathToClass($file->getRelativePathname());

            // 判断是否实体类
            if (static::isEntityClass($class)) {
                $entities[] = $class;
            }
        }

        return $entities;
    }

    /**
     * 从实体名解析出实体类，或 null
     *
     * @param  string $entityName 实体名（小写 + 下划线）
     * @return string|null
     */
    public static function resolveName(string $name)
    {
        if (empty(static::$coreEntities)) {
            static::registerCoreEntities();
        }

        return static::$coreEntities[$name] ?? static::$entities[$name] ?? null;
    }

    /**
     * 从实体路径解析出实体对象，失败则返回 null
     *
     * @param  string  $path 实体路径
     * @return \July\Core\Entity\EntityInterface|null
     */
    public static function resolvePath(string $path)
    {
        [$name, $id] = explode('/', $path);

        if ($entity = static::resolveName($name)) {
            return $entity::find($id);
        }

        return null;
    }

    /**
     * 判断是否实体对象
     *
     * @param  object $instance
     * @return bool
     */
    public static function isEntity($instance)
    {
        return $instance && $instance instanceof EntityInterface;
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
     * 将路径转化为类名
     *
     * @param  string $path 文件路径
     * @return string
     */
    protected static function pathToClass(string $path)
    {
        return str_replace('/', '\\', preg_replace('/\.php$/i', '', $path));
    }
}
