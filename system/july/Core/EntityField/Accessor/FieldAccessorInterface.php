<?php

namespace July\Core\EntityField\Accessor;

use July\Core\Entity\EntityBase;
use July\Core\EntityField\EntityFieldBase;

/**
 * 字段存取器
 */
interface FieldAccessorInterface
{
    /**
     * 字段存取器构造函数
     *
     * @param  \July\Core\Entity\EntityBase $entity
     * @param  \July\Core\EntityField\EntityFieldBase $field
     * @return void
     */
    public function __construct(EntityBase $entity = null, EntityFieldBase $field = null);

    /**
     * 获取字段值
     *
     * @return mixed
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    public function get();

    /**
     * 保存字段值
     *
     * @param  mixed $value
     * @return void
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    public function set($value);

    /**
     * 删除字段值
     *
     * @return void
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    public function delete();

    /**
     * 检索字段值
     *
     * @param  string $needle 待搜索的字符串
     * @return array
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    public function search(string $needle);

    /**
     * 建立字段值存储表
     *
     * @return void
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    public function tableUp();

    /**
     * 移除字段值存储表
     *
     * @return void
     *
     * @throws \July\Core\Entity\Exceptions\InvalidEntityException
     */
    public function tableDown();
}
