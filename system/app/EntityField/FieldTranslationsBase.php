<?php

namespace App\EntityField;

use App\Casts\Serialized;
use App\Models\ModelBase;
use App\Utils\Types;

abstract class FieldTranslationsBase extends ModelBase
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
