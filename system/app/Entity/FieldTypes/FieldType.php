<?php

namespace App\Entity\FieldTypes;

use App\Entity\Exceptions\FieldTypeNotFound;
use App\Entity\Models\BaseEntityField;

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
        $types = [];
        foreach (static::$types as $type) {
            $alias = $type::alias();
            $types[$alias] = [
                'class' => $type,
                'alias' => $alias,
                'label' => $type::label(),
                'description' => $type::description(),
            ];
        }

        return $types;
    }

    /**
     * 获取实体属性类型定义类实例，失败则抛出错误
     *
     * @param string|\App\Models\BaseEntityField $alias 实体属性类型实例或别名
     * @param string|null $langcode
     * @return \App\EntityFieldTypes\EntityFieldTypeInterface
     *
     * @throws \App\Exceptions\FieldTypeNotFound
     */
    public static function make($alias, string $langcode = null)
    {
        if ($alias) {
            if ($alias instanceof BaseEntityField) {
                $field = $alias;
                $alias = $field->getAttribute('field_type');
            } else {
                $field = null;
            }

            foreach (static::$types as $type) {
                if ($alias === $type::alias()) {
                    return new $type($field, $langcode);
                }
            }
        }

        throw new FieldTypeNotFound("找不到 '{$alias}' 对应的字段类型。");
    }

    public static function extractParameters(array $raw)
    {
        return static::make($raw['field_type'] ?? null)->extractParameters($raw);
    }

    public static function getJigsaws(array $fieldData)
    {
        return static::make($fieldData['field_type'] ?? null)->getJigsaws($fieldData);
    }
}
