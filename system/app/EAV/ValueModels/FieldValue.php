<?php

namespace App\EntityField;

class FieldValue extends FieldValueBase
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
