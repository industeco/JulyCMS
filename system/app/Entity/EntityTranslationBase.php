<?php

namespace App\Entity;

use App\Models\ModelBase;

abstract class EntityTranslationBase extends ModelBase
{
    /**
     * 获取绑定的实体类
     *
     * @return string
     */
    abstract public function getEntityClass();
}
