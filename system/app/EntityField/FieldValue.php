<?php

namespace App\EntityField;

use App\Entity\EntityBase;
use App\Utils\Arr;

class FieldValue extends FieldValueBase
{
    /**
     * 绑定到字段
     *
     * @param  \App\EntityField\FieldBase $field
     * @return $this
     */
    public function bindField(FieldBase $field)
    {
        parent::bindField($field);

        // 设置模型表
        $this->setTable($field->getFieldValueTable());

        // 获取列名
        $this->value_column = $this->fieldType->getColumn()['name'];

        // 设置 fillable
        $this->fillable([
            'entity_id',
            $this->value_column => $field->getParameters()['default'] ?? $this->fieldType->getDefaultValue(),
            'langcode',
            'updated_at',
        ]);

        // 设置字段值转换
        $this->casts = [
            $this->value_column => $this->fieldType->getCaster(),
        ];

        return $this;
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
            return $value->getAttributeValue($this->value_column);
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
        return $this->newModelQuery()->updateOrCreate([
            'entity_id' => $entity->getEntityId(),
            'langcode' => $entity->getLangcode(),
        ], [
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
        $field = Arr::selectAs($this->field->getAttributes(), [
            'id' => 'field_id', 'field_type_id', 'label', 'description',
        ]) + ['entity_name' => $this->field->getBoundEntityName()];

        // 查询条件
        $condition = [$this->value_column, 'like', '%'.$needle.'%'];

        // 获取查询结果
        $results = [];
        foreach ($this->newModelQuery()->where($condition)->get() as $value) {
            $results[] = $field + Arr::selectAs($value->getAttributes(), [
                'entity_id', 'langcode', $this->value_column => 'field_value'
            ]);
        }

        return $results;
    }
}
