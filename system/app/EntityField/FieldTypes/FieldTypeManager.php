<?php

namespace App\EntityField\FieldTypes;

use App\EntityField\Exceptions\FieldTypeNotFoundException;

class FieldTypeManager
{
    /**
     * 缓存的字段类型信息
     *
     * @var array
     */
    protected static $fieldTypes = [];

    /**
     * 登记字段类型
     *
     * @param  string $class
     * @return void
     */
    public static function register(string $class)
    {
        if (class_exists($class)) {
            static::$fieldTypes[$class::get('id')] = $class;
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
        if ($class = static::$fieldTypes[$id] ?? null) {
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

    /**
     * 获取字段类型列表
     *
     * @return array
     */
    public static function all()
    {
        return static::$fieldTypes;
    }
}
