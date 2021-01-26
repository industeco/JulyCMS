<?php

namespace App\EntityField;

use Illuminate\Database\Eloquent\Relations\Pivot;

abstract class FieldMoldPivotBase extends Pivot
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
