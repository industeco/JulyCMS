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

    /**
     * 采集字段所有相关参数，并以 langcode + mold_id 索引
     *
     * @param  \App\EntityField\FieldBase $field 字段
     * @return \Illuminate\Support\Collection
     */
    public static function collect(FieldBase $field)
    {
        return static::query()
            ->where(['entity_name' => $field->getBoundEntityName(), 'field_id' => $field->getKey()])
            ->get()
            ->keyBy(function($item) {
                return $item->langcode.','.$item->mold_id;
            });
    }
}
