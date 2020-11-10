<?php

namespace July\Core\EntityField;

use July\Core\Entity\EntityBase;
use July\Core\Entity\EntityInterface;

interface EntityFieldInterface extends EntityInterface
{
    // /**
    //  * 获取字段所属的实体类
    //  *
    //  * @return string
    //  */
    // public static function getParentEntityClass();

    // /**
    //  * 获取字段所属实体类的实体名
    //  *
    //  * @return string
    //  */
    // public static function getParentEntityName();

    /**
     * 获取字段类型对象
     *
     * @return \July\Core\EntityField\FieldType
     */
    public function getFieldType();

    /**
     * 获取字段参数
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function parameters();

    /**
     * 获取字段参数
     *
     * @param string|null $langcode
     * @return array
     */
    public function getParameters();

    /**
     * 获取字段存取器
     *
     * @param  \July\Core\Entity\EntityBase|null $entity
     * @return \July\Core\EntityField\FieldStorageInterface
     */
    public function getStorage(EntityBase $entity = null);
}
