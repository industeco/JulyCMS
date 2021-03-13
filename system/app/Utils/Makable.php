<?php

namespace App\Utils;

trait Makable
{
    /**
     * 快捷创建
     *
     * @return static
     */
    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }
}
