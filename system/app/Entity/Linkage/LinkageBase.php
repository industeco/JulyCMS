<?php

namespace App\Entity\Linkage;

use App\Entity\EntityBase;
use App\Entity\Exceptions\InvalidBoundEntityException;

/**
 * 字段存取器
 */
abstract class LinkageBase implements LinkageInterface
{
    /**
     * 默认值
     *
     * @var mixed
     */
    protected $default = null;

    /**
     * 请求的实体
     *
     * @var \App\Entity\EntityBase
     */
    protected $entity;

    /**
     * 字段存取器构造函数
     *
     * @param  \App\Entity\EntityBase $entity
     * @return void
     */
    public function __construct(EntityBase $entity)
    {
        $this->entity = $entity;
    }

    /**
     * 快捷构造
     *
     * @param  \App\Entity\EntityBase $entity
     * @return static
     */
    public static function make(EntityBase $entity)
    {
        return new static($entity);
    }

    /**
     * 获取字段值
     *
     * @return mixed
     */
    public function getValue()
    {
        if (! $this->entity->exists) {
            return $this->default;
        }

        return $this->performGet();
    }

    /**
     * 保存字段值
     *
     * @param  mixed $value
     * @return void
     */
    public function setValue($value)
    {
        if (! $this->entity->exists) {
            throw new InvalidBoundEntityException('属性存取器的绑定实体无效');
        }

        return $this->performSet($value);
    }

    /**
     * 删除字段值
     *
     * @return void
     */
    public function deleteValue()
    {
        if (! $this->entity->exists) {
            throw new InvalidBoundEntityException('属性存取器的绑定实体无效');
        }

        return $this->performDelete();
    }

    /**
     * 检索字段值
     *
     * @param  string $needle 待搜索的字符串
     * @return array
     */
    public function searchInValues(string $needle)
    {
        return $this->performSearch($needle);
    }

    /**
     * 获取字段值
     *
     * @return mixed
     */
    abstract protected function performGet();

    /**
     * 保存字段值
     *
     * @param  mixed $value
     * @return void
     */
    abstract protected function performSet($value);

    /**
     * 删除字段值
     *
     * @return void
     */
    abstract protected function performDelete();

    /**
     * 检索字段值
     *
     * @param  string $needle 待搜索的字符串
     * @return array
     */
    abstract protected function performSearch(string $needle);
}
