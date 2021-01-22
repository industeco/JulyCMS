<?php

namespace App\EntityField;

use App\Casts\Serialized;
use App\Models\ModelBase;

class FieldParameters extends ModelBase
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
     * 获取所有列名
     *
     * @return array
     */
    public static function getColumns()
    {
        return [
            'id',
            'entity_name',
            'field_id',
            'mold_id',
            'langcode',
            'parameters',
            'updated_at',
            'created_at',
        ];
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'parameters' => Serialized::class,
    ];

    /**
     * 采集字段所有相关参数，以 langcode + mold_id 索引
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\EntityField\FieldBase $field 字段
     * @return \Illuminate\Support\Collection
     */
    public function scopeOfField($query, FieldBase $field)
    {
        return $query->where([
                'entity_name' => $field->getBoundEntityName(),
                'field_id' => $field->getKey()
            ]);
    }
}
