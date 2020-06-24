<?php

namespace App\Contracts;

interface Entity
{
    /**
     * 获取实体 id
     *
     * @return string
     */
    public static function getEntityId();

    /**
     * 获取上级实体（实体可以有从属关系）
     *
     * @return string|null
     */
    public static function getParentEntity();

    /**
     * 获取上级实体 id
     *
     * @return string|null
     */
    public static function getParentEntityId();
}
