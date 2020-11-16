<?php

namespace July\Core\EntityField\Accessor;

use July\Core\Entity\EntityBase;
use July\Core\Entity\Exceptions\InvalidEntityException;
use July\Core\EntityField\EntityFieldBase;

/**
 * 字段存取器
 */
abstract class FieldAccessorBase
{
    /**
     * 关联实体
     *
     * @var \July\Core\Entity\EntityBase|null
     */
    protected $entity;

    /**
     * 关联字段
     *
     * @var \July\Core\EntityField\EntityFieldBase|null
     */
    protected $field;

    /**
     * 字段存取器构造函数
     *
     * @param  \July\Core\Entity\EntityBase $entity
     * @param  \July\Core\EntityField\EntityFieldBase $field
     * @return void
     */
    public function __construct(EntityBase $entity = null, EntityFieldBase $field = null)
    {
        $this->entity = $entity;
        $this->field = $field;
    }

    /**
     * 绑定实体
     *
     * @param  \July\Core\Entity\EntityBase
     * @return static
     */
    final public function bindEntity(EntityBase $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * 绑定字段
     *
     * @param  \July\Core\EntityField\EntityFieldBase
     * @return static
     */
    final public function bindField(EntityFieldBase $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * 获取绑定的实体
     *
     * @return \July\Core\Entity\EntityBase|null
     */
    final public function getBoundEntity()
    {
        return $this->entity;
    }

    /**
     * 获取绑定的字段
     *
     * @return \July\Core\EntityField\EntityFieldBase|null
     */
    final public function getBoundField()
    {
        return $this->field;
    }

    /**
     * 获取字段值
     *
     * @return mixed
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    abstract public function get();

    /**
     * 保存字段值
     *
     * @param  mixed $value
     * @return void
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    abstract public function set($value);

    /**
     * 删除字段值
     *
     * @return void
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    abstract public function delete();

    /**
     * 检索字段值
     *
     * @param  string $needle 待搜索的字符串
     * @return array
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    abstract public function search(string $needle);

    /**
     * 建立字段值存储表
     *
     * @return void
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    public function tableUp()
    {
        //
    }

    /**
     * 移除字段值存储表
     *
     * @return void
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    public function tableDown()
    {
        //
    }
}
