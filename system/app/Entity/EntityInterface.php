<?php

namespace App\Entity;

interface EntityInterface
{
    /**
     * 获取实体 id
     *
     * @return string
     */
    public static function getEntityId(): string;
}
