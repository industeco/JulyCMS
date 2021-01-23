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
        return $query->where([
            'entity_id' => $entity->getEntityId(),
            'langcode' => $entity->getLangcode(),
        ]);
    }

    /**
     * 获取字段值
     *
     * @param  \App\Entity\EntityBase|null $entity
     * @return mixed
     */
    abstract public function getValue(?EntityBase $entity = null);

    /**
     * 设置字段值
     *
     * @param  mixed $value
     * @param  \App\Entity\EntityBase $entity
     * @return mixed
     */
    abstract public function setValue($value, EntityBase $entity);

    /**
     * 删除字段值
     *
     * @param  \App\Entity\EntityBase $entity
     * @return mixed
     */
    abstract public function deleteValue(EntityBase $entity);

    /**
     * 在字段表中搜索
     *
     * @param  string  $needle
     * @return array
     */
    abstract public function searchValue(string $needle);
}
