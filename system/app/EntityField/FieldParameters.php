<?php

namespace App\EntityField;

use App\Casts\Serialized;
use App\Models\ModelBase;
use App\Utils\Types;

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
        'default_value',
        'options',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        //
    ];

    /**
     * 参数值转换器
     *
     * @var string
     */
    protected $caster = 'string';

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
                'field_id' => $field->getKey(),
                'langcode' => $field->getLangcode(),
            ]);
    }

    public function setCaster(string $caster)
    {
        $this->caster = $caster;
        return $this;
    }

    /**
     * placeholder 属性的 Get Mutator
     *
     * @param  string|null  $value
     * @return mixed
     */
    public function getDefaultValueAttribute($value)
    {
        return Types::cast($value, $this->caster);
    }

    /**
     * placeholder 属性的 Get Mutator
     *
     * @param  string|null  $options
     * @return array
     */
    public function getOptionsAttribute($options)
    {
        if (empty($options)) {
            return [];
        }
        $options = array_map(function($option) {
            return Types::cast($option, $this->caster);
        }, array_filter(array_map('trim', explode(substr($options, 0, 1), $options))));

        return array_values($options);
    }
}
