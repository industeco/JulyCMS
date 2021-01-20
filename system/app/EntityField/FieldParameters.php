<?php

namespace App\EntityField;

use App\Casts\Serialized;
use App\Model;

class FieldParameters extends Model
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
        'entity_name',
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
