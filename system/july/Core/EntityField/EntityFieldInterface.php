<?php

namespace July\Core\EntityField;

use July\Core\Entity\EntityBase;
use July\Core\Entity\EntityInterface;

interface EntityFieldInterface extends EntityInterface
{
    /**
     * 解析当前实体字段类所属的实体类
     *
     * @return string|null
     */
    public static function resolveParentEntityClass();

    /**
     * 获取字段类型对象
     *
     * @return \July\Core\EntityField\FieldType
     */
    public function getFieldType();

    /**
     * 获取字段参数
     *
     * @param string|null $langcode
     * @return array
     */
    public function getParameters();

    /**
     * 绑定实体
     *
     * @param  \July\Core\Entity\EntityBase $entity
     * @return static
     */
    public function bindEntity(EntityBase $entity);

    /**
     * 获取绑定的实体
     *
     * @return \July\Core\Entity\EntityBase
     */
    public function getBoundEntity();

    /**
     * 注册字段存取器
     *
     * @param  string $fieldId 字段 id
     * @param  string $accessor 存取器类名
     * @return void
     */
    public static function registerFieldAccessor(string $fieldId, string $accessor);

    /**
     * 获取字段存取器，存取器专司字段值的存储和读取
     *
     * @param  \July\Core\Entity\EntityBase|null $entity
     * @return \July\Core\EntityField\FieldAccessorInterface
     */
    public function getFieldAccessor(EntityBase $entity = null);

    /**
     * 设置字段值
     *
     * @param  mixed $value
     * @return void
     */
    public function setValue($value);

    /**
     * 获取字段值
     *
     * @return mixed
     */
    public function getValue();

    /**
     * 删除字段值
     *
     * @return void
     */
    public function deleteValue();

    /**
     * 搜索字段值
     *
     * @param  string $needle 搜索该字符串
     * @return array
     */
    public function searchValue(string $needle);
}
