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

    // /**
    //  * 获取实体名，并转化为可作为前缀的模式
    //  *
    //  * @return string
    //  */
    // public static function getNormalizedEntityName()
    // {
    //     return str_replace('.', '__', static::getEntityName());
    // }

    /**
     * 获取实体路径
     *
     * @return string
     */
    public function getEntityPath()
    {
        // $path = explode('.', static::getEntityName());
        // $path[] = $this->getEntityId();
        // return implode('/', $path);

        return str_replace('.', '/', static::getEntityName()).'/'.$this->getEntityId();
    }
}
