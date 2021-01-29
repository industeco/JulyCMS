<?php

namespace App\EntityField;

use App\Models\PivotBase;

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
        'delta',
        'label',
        'description',
        'is_required',
        'helpertext',
        'default_value',
        'options',
        'rules',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'delta' => 'int',
        'is_required' => 'boolean',
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

    // /**
    //  * options 属性的 Set Mutator
    //  *
    //  * @param  array|null $options
    //  * @return array
    //  */
    // public function setOptionsAttribute($options)
    // {
    //     if (is_array($options)) {
    //         $options = implode('|', $options);
    //     }
    //     $this->attributes['options'] = $options;
    // }
}
