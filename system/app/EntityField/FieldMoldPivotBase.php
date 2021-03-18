<?php

namespace App\EntityField;

use App\Models\PivotBase;
use App\Support\Arr;

abstract class FieldMoldPivotBase extends PivotBase
{
    /**
     * 是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'mold_id',
        'field_id',
        'label',
        'description',
        'delta',
        'field_meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'delta' => 'int',
        'field_meta' => Serialized::class,
    ];

    /**
     * 获取实体类型在此的外键
     *
     * @return string
     */
    public static function getMoldKeyName()
    {
        return 'mold_id';
    }

    /**
     * 获取实体字段在此的外键
     *
     * @return string
     */
    public static function getFieldKeyName()
    {
        return 'field_id';
    }

    /**
     * 获取所有参数字段
     *
     * @return array
     */
    public static function getMetaAttributes()
    {
        return [
            'label',
            'description',
            'delta',
            'field_meta',
        ];
    }
}
