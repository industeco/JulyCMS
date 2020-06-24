<?php

namespace App\FieldTypes;

use App\Support\Arr;
use App\Exceptions\FieldTypeNotFound;
use App\Models\ContentField;

class FieldType
{
    /**
     * 可用字段类型
     */
    protected static $types = [
        TextField::class, // 文本
        HtmlField::class, // HTML
        FileField::class, // 文件名
    ];

    /**
     * 获取字段类型列表
     *
     * @return array
     */
    public static function all()
    {
        $field_types = config('jc.field_types');
        if ($field_types) {
            return $field_types;
        }

        $field_types = [];
        foreach (static::$types as $type) {
            $alias = $type::getAlias();
            $field_types[$alias] = [
                'alias' => $alias,
                'class' => $type,
                'label' => $type::getLabel(),
                'description' => $type::getDescription(),
            ];
        }
        config(['jc.field_types' => $field_types]);

        return $field_types;
    }

    /**
     * 获取定义类实例
     *
     * @param mixed $alias 字段类型别名
     * @return string|null
     */
    public static function find($alias)
    {
        $alias = static::resolveAlias($alias);
        return Arr::get(static::all(), $alias.'.class');
    }

    /**
     * 获取定义类实例，失败则抛出错误
     *
     * @param mixed $alias 字段类型别名
     * @param \App\Models\ContentField|null $field
     * @param string|null $langcode
     * @return \App\FieldTypes\FieldTypeInterface
     *
     * @throws \App\Exceptions\FieldTypeNotFound
     */
    public static function make($alias, ?ContentField $field = null, $langcode = null)
    {
        if ($type = static::find($alias)) {
            return new $type($field, $langcode);
        }
        throw new FieldTypeNotFound('找不到 ['.$alias.'] 对应的字段类型。');
    }

    protected static function resolveAlias($alias)
    {
        if (is_string($alias)) {
            return trim($alias);
        }
        if (is_null($alias)) {
            return null;
        }
        $alias = Arr::of($alias);
        return $alias['field_type'] ?? null;
    }

    public static function getSchema($type)
    {
        return static::make($type)->getSchema();
    }

    public static function getColumns($type, $fieldName, array $parameters = [])
    {
        return static::make($type)->getColumns($fieldName, $parameters);
    }

    public static function extractParameters(array $data)
    {
        return static::make($data)->extractParameters($data);
    }

    public static function getJigsaws(array $data)
    {
        return static::make($data)->getJigsaws($data);
    }

    public static function toRecords($type, $value, array $columns)
    {
        return static::make($type)->toRecords($value, $columns);
    }

    public static function toValue($type, array $records, array $columns, array $parameters = [])
    {
        return static::make($type)->toValue($records, $columns, $parameters);
    }
}
