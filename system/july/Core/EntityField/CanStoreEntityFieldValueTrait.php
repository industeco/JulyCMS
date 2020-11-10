<?php

namespace July\Core\EntityField;

use July\Core\Entity\EntityBase;
use July\Core\Entity\EntityInterface;

trait CanStoreEntityFieldValueTrait
{
    /**
     * 获取表中用于存储实体路径的列的名称
     *
     * @return string
     */
    public static function getPathColumn()
    {
        return 'path';
    }

    /**
     * 获取表中用于存储字段值的列的名称
     *
     * @return string
     */
    public static function getValueColumn()
    {
        return 'value';
    }

    /**
     * 获取表中用于存储语言代码的列的名称
     *
     * @return string
     */
    public static function getLangcodeColumn()
    {
        return 'langcode';
    }

    /**
     * 将字段数据中的标准列名转换为本地列名
     *
     * @param  array $data 使用标准列名的字段数据
     * @return array
     */
    public static function localizeFieldValueData(array $data)
    {
        $columns = [
            'path' => static::getPathColumn(),
            'langcode' => static::getLangcodeColumn(),
            'value' => static::getValueColumn(),
        ];

        $localized = [];
        foreach ($columns as $normal => $local) {
            if ($local && isset($data[$normal])) {
                $localized[$local] = $data[$normal];
            }
        }

        return $localized;
    }

    /**
     * 获取字段值
     *
     * @param  \July\Core\Entity\EntityInterface $entity
     * @param  string $langcode
     * @return mixed
     */
    public static function getFieldValue(EntityInterface $entity, string $langcode)
    {
        $conditions = [
            static::getPathColumn() => $entity->getEntityPath(),
        ];

        if ($langcodeAlias = static::getLangcodeColumn()) {
            $conditions[$langcodeAlias] = $langcode;
        }

        if ($record = static::query()->where($conditions)->first()) {
            return $record->getAttribute(static::getValueColumn());
        }

        return null;
    }
}
