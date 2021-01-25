<?php

namespace App\EntityField;

use App\Entity\EntityBase;
use App\Models\ModelBase;

abstract class FieldValueBase extends ModelBase
{
    /**
     * 绑定的字段
     *
     * @var \App\EntityField\FieldBase
     */
    protected $field;

    /**
     * 绑定的字段类型
     *
     * @var \App\EntityField\FieldTypes\FieldTypeBase
     */
    protected $fieldType;

    /**
     * 保存字段值的列名
     *
     * @var string
     */
    protected $value_column;

    /**
     * 判断是否动态模型
     *
     * @return bool
     */
    public static function isDynamic()
    {
        return false;
    }

    /**
     * 绑定到字段
     *
     * @param  \App\EntityField\FieldBase $field
     * @return $this
     */
    public function bindField(FieldBase $field)
    {
        $this->field = $field;
        $this->fieldType = $field->getFieldType();

        return $this;
    }

    /**
     * 按指定实体限定查询
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Entity\EntityBase $entity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfEntity($query, EntityBase $entity)
    {
        $condition = [
            'entity_id' => $entity->getEntityId(),
            'langcode' => $entity->getLangcode(),
        ];
        if (in_array('entity_name', $this->fillable)) {
            $condition['entity_name'] = $entity->getEntityName();
        }
        return $query->where($condition);
    }

    /**
     * 获取字段值
     *
     * @param  \App\Entity\EntityBase $entity
     * @return mixed
     */
    public function getValue(?EntityBase $entity = null)
    {
        // 如果未指定实体，或实体未保存，返回默认值
        if (!$entity || !$entity->exists) {
            return $this->attributes[$this->value_column] ?? null;
        }

        // 查找字段表
        if ($value = $this->newModelQuery()->ofEntity($entity)->first()) {
            return $value->{$this->value_column};
        }

        return null;
    }

    /**
     * 设置字段值
     *
     * @param  mixed $value
     * @param  \App\Entity\EntityBase $entity
     * @return mixed
     */
    public function setValue($value, EntityBase $entity)
    {
        if (is_null($value)) {
            return $this->deleteValue($entity);
        }

        $attributes = [
            'entity_id' => $entity->getEntityId(),
            'langcode' => $entity->getLangcode(),
        ];
        if (in_array('entity_name', $this->fillable)) {
            $attributes['entity_name'] = $entity->getEntityName();
        }
        return $this->newModelQuery()->updateOrCreate($attributes, [
            $this->value_column => $value,
        ]);
    }

    /**
     * 删除字段值
     *
     * @param  \App\Entity\EntityBase $entity
     * @return mixed
     */
    public function deleteValue(EntityBase $entity)
    {
        return $this->newModelQuery()->ofEntity($entity)->delete();
    }

    /**
     * 在字段表中搜索
     *
     * @param  string  $needle
     * @return array
     */
    public function searchValue(string $needle)
    {
        // 正在查询的字段的信息
        $field = [
            'field_id' => $this->field->getKey(),
            'field_type_id' => $this->field->field_type_id,
            'label' => $this->field->label,
            'description' => $this->field->description,
            'entity_name' => $this->field->getBoundEntityName(),
        ];

        // 查询条件
        $condition = [
            [$this->value_column, 'like', '%'.$needle.'%'],
        ];
        if (in_array('entity_name', $this->fillable)) {
            $condition[] = ['entity_name', '=', $field['entity_name']];
        }

        // 获取查询结果
        $results = [];
        foreach ($this->newModelQuery()->where($condition)->get() as $value) {
            $results[] = $field + [
                'entity_id' => $value->entity_id,
                'langcode' => $value->langcode,
                'field_value' => $value->{$this->value_column},
            ];
        }

        return $results;
    }
}
