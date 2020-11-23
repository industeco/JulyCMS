<?php

namespace July\Core\Entity\Linkage;

use July\Core\Entity\EntityBase;

/**
 * 字段存取器
 */
interface LinkageInterface
{
    /**
     * 字段存取器构造函数
     *
     * @param  \July\Core\Entity\EntityBase $entity
     * @return void
     */
    public function __construct(EntityBase $entity);

    /**
     * 快捷构造
     *
     * @param  \July\Core\Entity\EntityBase $entity
     * @return static
     */
    public static function make(EntityBase $entity);

    /**
     * 获取字段值
     *
     * @return mixed
     */
    public function getValue();

    /**
     * 保存字段值
     *
     * @param  mixed $value
     * @return void
     */
    public function setValue($value);

    /**
     * 删除字段值
     *
     * @return void
     */
    public function deleteValue();

    /**
     * 检索字段值
     *
     * @param  string $needle 待搜索的字符串
     * @return array
     */
    public function searchInValues(string $needle);
}
