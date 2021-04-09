<?php

namespace App\Entity;

use App\Models\ModelBase;
use App\Support\Arr;

abstract class EntityTranslationBase extends ModelBase
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * 获取绑定的实体类
     *
     * @return string
     */
    abstract public static function getEntityClass();

    /**
     * Get the fillable attributes for the model.
     *
     * @return array
     */
    public function getFillable()
    {
        if (! $this->fillable) {
            $entityClass = static::getEntityClass();
            $fillable = array_merge((new $entityClass)->getFillable(), ['entity_id']);
            $this->fillable($fillable);
        }

        return $this->fillable;
    }

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
