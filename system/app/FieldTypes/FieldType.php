<?php

namespace App\FieldTypes;

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
                'name' => $alias,
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
            return new $type;
        }
        throw new Error('找不到 ['.$alias.'] 对应的字段类型。');
    }

    public static function getSchema($type)
    {
        $fieldType = static::findOrFail($type);
        return $fieldType->getSchema();
    }

    public static function getColumns($type, $fieldName, array $parameters = [])
    {
        if ($fieldType = static::find($type)) {
            return $fieldType->getColumns($fieldName, $parameters);
        }
        return [];
    }

    public static function extractParameters(array $data)
    {
        $fieldType = static::findOrFail($data['field_type'] ?? null);
        return $fieldType->extractParameters($data);
    }

    public static function toRecords($type, $value, array $columns)
    {
        $fieldType = static::findOrFail($type);
        return $fieldType->toRecords($value, $columns);
    }

    public static function toValue($type, array $records, array $columns, array $config)
    {
        $fieldType = static::findOrFail($type);
        return $fieldType->toValue($records, $columns, $config);
    }

    public static function getJigsaws(array $data)
    {
        $fieldType = static::findOrFail($data['field_type'] ?? null);
        $jigsaws = $fieldType->getJigsaws($data);
        $jigsaws['type'] = $data['field_type'];
        return $jigsaws;
    }
}
