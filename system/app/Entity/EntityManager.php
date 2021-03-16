<?php

namespace App\Entity;

use App\Contracts\ManagerInterface;
use Illuminate\Support\Arr;

final class EntityManager implements ManagerInterface
{
    /**
     * 其它实体类
     *
     * @var array
     */
    protected static $entities = [];

    /**
     * 标记是否已处理 app.entities
     *
     * @var bool
     */
    protected static $discovered = false;

    /**
     * 登记实体类（非核心）
     *
     * @param  string|array $entities
     * @return void
     */
    public static function register($entities)
    {
        $entities = Arr::wrap($entities);
        foreach ($entities as $entity) {
            if (class_exists($entity)) {
                static::$entities[$entity::getEntityName()] = $entity;
            }
        }
    }

    /**
     * 使用实体名找出实体类；如果指定了 id，则查找实体实例
     *
     * @param  string $name 实体名
     * @param  int|string|null $id 实体 id
     * @return string|\App\Entity\EntityBase|null
     */
    public static function resolve(string $name, $id = null)
    {
        if ($entity = static::$entities[$name] ?? null) {
            if (! is_null($id)) {
                return $entity::find($id);
            }
            return $entity;
        }
        return null;
    }

    /**
     * 获取字段类型列表
     *
     * @return array
     */
    public static function all()
    {
        return static::$entities;
    }

    /**
     * 将 config::app.entities 登记到 $entities
     *
     * @return void
     */
    public static function discoverIfNotDiscovered()
    {
        if (! static::$discovered) {
            static::register(config('app.entities'));
            static::$discovered = true;
        }
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

        $ref = new \ReflectionClass($class);
        return $ref->isInstantiable() && $ref->isSubclassOf(EntityBase::class);
    }

    /**
     * 根据指定范围获取实体
     *
     * @param  array $scope
     * @return array
     */
    public static function getEntitiesFromScope(array $scope)
    {
        $entities = [];
        if ($class = static::resolve($scope[0])) {
            foreach ($class::ofMold($scope[1])->get() as $entity) {
                $entities[] = [
                    'id' => $entity->getKey(),
                    'title' => $entity->getTitle(),
                    'created_at' => $entity->getCreatedAt(),
                    'updated_at' => $entity->getUpdatedAt(),
                ];
            }
        }
        return $entities;
    }
}
