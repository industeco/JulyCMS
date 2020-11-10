<?php

namespace July\Core\Entity;

interface EntityInterface
{
    /**
     * 获取实体名
     *
     * @return string
     */
    public static function getEntityName();

    /**
     * 获取实体 id
     *
     * @return int|string
     */
    public function getEntityId();

    /**
     * 获取实体路径，由实体名与实体实例的 id 组成
     *
     * @return string
     */
    public function getEntityPath();

    /**
     * 获取实体对象
     *
     * @param mixed $id
     * @return self|null
     */
    public static function find($id);

    /**
     * 获取实体对象，失败则抛出错误
     *
     * @param mixed $id
     * @return self
     *
     * @throws \July\Base\Exceptions\EntityNotFoundException
     */
    public static function findOrFail($id);
}
