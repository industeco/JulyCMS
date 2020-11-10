<?php

namespace July\Core\EntityField;

use App\Casts\Serialized;
use July\Core\Entity\EntityBase;

class FieldParameters extends EntityBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'field_parameters';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'field_id',
        'entity_name',
        'bundle_name',
        'langcode',
        'parameters',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'parameters' => Serialized::class,
    ];
}
