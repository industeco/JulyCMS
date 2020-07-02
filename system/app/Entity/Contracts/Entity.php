<?php

namespace App\Entity\Contracts;

interface Entity
{
    /**
     * 获取实体 id
     *
     * @return string
     */
    public static function getEntityId();

    /**
     * 获取上级实体 id
     *
     * @return string
     */
    public static function getParentEntityId();
}
