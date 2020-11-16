<?php

namespace July\Core\Entity;

use Illuminate\Support\Str;

trait EntityTrait
{
    /**
     * 获取实体名
     *
     * @return string
     */
    public static function getEntityName()
    {
        return Str::snake(class_basename(static::class));
    }

    /**
     * 获取实体路径
     *
     * @return string
     */
    public function getEntityPath()
    {
        return str_replace('.', '/', static::getEntityName()).'/'.$this->getEntityKey();
    }
}
