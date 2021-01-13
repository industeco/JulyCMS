<?php

namespace App\EntityField;

use App\Entity\EntityBase;
use App\Entity\EntityInterface;

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
     * @return \App\EntityField\FieldType
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
     * @param  \App\Entity\EntityBase $entity
     * @return static
     */
    public function bindEntity(EntityBase $entity);

    /**
     * 获取绑定的实体
     *
     * @return \App\Entity\EntityBase
     */
    public function getBoundEntity();

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
