<?php

namespace App\Entity;

use App\Models\ModelBase;
use App\Support\Arr;

abstract class EntityTranslationBase extends ModelBase
{
    /**
     * 获取绑定的实体类
     *
     * @return string
     */
    abstract public function getEntityClass();

    /**
     * @return array
     */
    public function toEntityAttributes()
    {
        return array_merge(
            Arr::except($this->original, ['id','entity_id']),
            ['id' => $this->original['entity_id']]
        );
    }
}
