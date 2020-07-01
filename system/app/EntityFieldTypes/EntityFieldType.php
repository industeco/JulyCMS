<?php

namespace App\EntityFieldTypes;

use App\Exceptions\EntityFieldTypeNotFound;
use App\Models\BaseEntityField;

class EntityFieldType
{
    /**
     * 可用字段类型
     */
    protected static $types = [
        'text' => TextField::class, // 文本
        'html' => HtmlField::class, // HTML
        'file' => FileField::class, // 文件名
    ];

    /**
     * 获取字段类型列表
     *
     * @return array
     */
    public static function all()
    {
        $types = [];
        foreach (static::$types as $alias => $type) {
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
     * 获取定义类实例，失败则抛出错误
     *
     * @param string $alias 实体属性类型别名
     * @param \App\Models\BaseEntityField |null $field
     * @param string|null $langcode
     * @return \App\EntityFieldTypes\EntityFieldTypeInterface
     *
     * @throws \App\Exceptions\EntityFieldTypeNotFound
     */
    public static function find(string $alias, BaseEntityField $field = null, string $langcode = null)
    {
        foreach (static::$types as $type) {
            if ($alias === $type::alias()) {
                return new $type($field, $langcode);
            }
        }
        throw new EntityFieldTypeNotFound("找不到 '{$alias}' 对应的字段类型。");
    }
}
