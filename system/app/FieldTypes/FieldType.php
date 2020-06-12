<?php

namespace App\FieldTypes;

use App\Support\Arr;
use Error;

class FieldType
{
    /**
     * 模型字段类型与定义类对应表
     */
    protected static $types = [
        // 纯文字
        'text' => TextField::class,

        // HTML
        'html' => HtmlField::class,

        // 文件名
        'file' => FileField::class,
    ];

    public static function getTypes()
    {
        $types = [];
        foreach (static::$types as $alias => $type) {
            $types[$alias] = [
                'alias' => $alias,
                'label' => $type::$label,
                'description' => $type::$description,
                'searchable' => $type::$searchable,
            ];
        }

        return $types;
    }

    /**
     * 获取定义类实例
     *
     * @param string $alias 定义类别名
     * @return \App\FieldTypes\FieldTypeBase|null
     */
    public static function find($alias)
    {
        $alias = static::getTypeAlias($alias);
        if (($type = static::$types[$alias] ?? null) && $type::$isPublic) {
            return new $type;
        }
        return null;
    }

    /**
     * 获取定义类实例，失败则抛出错误
     *
     * @param string $alias 定义类别名
     * @return \App\FieldTypes\FieldTypeBase
     */
    public static function findOrFail($alias)
    {
        if ($type = static::find($alias)) {
            return $type;
        }
        throw new Error('找不到 ['.$alias.'] 对应的字段类型。');
    }

    public static function getSchema($type)
    {
        return static::findOrFail($type)->getSchema();
    }

    public static function getColumns($type, $fieldName, array $parameters = [])
    {
        return static::findOrFail($type)->getColumns($fieldName, $parameters);
    }

    public static function collectParameters(array $data)
    {
        return static::findOrFail($data)->collectParameters($data);
    }

    public static function getJigsaws(array $data)
    {
        return static::findOrFail($data)->getJigsaws($data);
    }

    public static function toRecords($type, $value, array $columns)
    {
        return static::findOrFail($type)->toRecords($value, $columns);
    }

    public static function toValue($type, array $records, array $columns, array $parameters = [])
    {
        return static::findOrFail($type)->toValue($records, $columns, $parameters);
    }

    public static function getTypeAlias($data)
    {
        if (is_string($data) || is_null($data)) {
            return $data;
        }
        $data = Arr::of($data);
        return $data['field_type'] ?? null;
    }
}
