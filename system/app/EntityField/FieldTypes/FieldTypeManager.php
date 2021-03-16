<?php

namespace App\EntityField\FieldTypes;

use App\Contracts\ManagerInterface;
use App\EntityField\Exceptions\FieldTypeNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class FieldTypeManager implements ManagerInterface
{
    /**
     * 缓存的字段类型信息
     *
     * @var array
     */
    protected static $fieldTypes = [];

    /**
     * 标记是否已处理 app.entities
     *
     * @var bool
     */
    protected static $discovered = false;

    /**
     * 登记字段类型
     *
     * @param  string|array $class
     * @return void
     */
    public static function register($classes)
    {
        foreach (Arr::wrap($classes) as $class) {
            if (class_exists($class)) {
                static::$fieldTypes[] = $class;
            }
        }
    }

    /**
     * 获取字段类型定义类
     *
     * @param  string $id 类型定义 id
     * @return string|null
     */
    public static function resolve(string $id)
    {
        return static::$fieldTypes[$id] ?? null;
    }

    /**
     * 获取字段类型注册表
     *
     * @return array
     */
    public static function all()
    {
        return static::$fieldTypes;
    }

    /**
     * 获取字段类型列表
     *
     * @param  string $scope 类型的作用范围，通常是实体名
     * @return array
     */
    public static function details(string $scope = 'default')
    {
        $types = [];
        foreach (static::$fieldTypes as $class) {
            if ($class::available($scope)) {
                /** @var \App\EntityField\FieldTypes\FieldTypeBase */
                $type = new $class;

                $types[$class] = [
                    'class' => $class,
                    'label' => $type->getLabel(),
                    'description' => $type->getDescription(),
                    'metakeys' => $type->getMetaKeys(),
                ];
            }
        }
        return $types;
    }

    /**
     * 将 config::app.field_types 登记到 $fieldTypes
     *
     * @return void
     */
    public static function discoverIfNotDiscovered()
    {
        if (! static::$discovered) {
            static::register(config('app.field_types'));
            static::$discovered = true;
        }
    }

    /**
     * 获取字段类型定义类实例
     *
     * @param  string $id 类型定义 id
     * @return \App\EntityField\FieldTypes\FieldTypeBase
     */
    public static function find(string $id)
    {
        if ($class = static::resolve($id)) {
            return new $class;
        }
        return null;
    }

    /**
     * 获取字段类型定义类实例，失败则抛出错误
     *
     * @param  string $id 类型定义 id
     * @return \App\EntityField\FieldTypes\FieldTypeBase
     *
     * @throws \App\EntityField\Exceptions\FieldTypeNotFoundException
     */
    public static function findOrFail(string $id)
    {
        if ($fieldType = static::find($id)) {
            return $fieldType;
        }

        throw new FieldTypeNotFoundException();
    }
}
