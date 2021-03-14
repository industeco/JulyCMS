<?php

namespace App\EntityField;

use App\Casts\Serialized;
use App\Models\ModelBase;

abstract class FieldTranslationBase extends ModelBase
{
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'field_id',
        'mold_id',
        'langcode',
        'field_meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'field_meta' => Serialized::class,
    ];

    /**
     * 获取绑定的字段类
     *
     * @return string
     */
    abstract public function getFieldClass();
}
