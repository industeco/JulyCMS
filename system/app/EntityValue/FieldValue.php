<?php

namespace App\EntityValue;

class FieldValue extends ValueBase
{
    /**
     * 判断是否动态模型
     *
     * @return bool
     */
    public static function isDynamic()
    {
        return true;
    }
}
