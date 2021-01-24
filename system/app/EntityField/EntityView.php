<?php

namespace App\EntityField;

use App\Entity\EntityBase;
use App\Utils\Arr;

class EntityView extends FieldValueBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'entity_views';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'entity_name',
        'entity_id',
        'langcode',
        'view',
    ];

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
            'entity_name' => $entity->getEntityName(),
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
    public function getValue(?EntityBase $entity = null)
    {
        // 如果未指定实体，或实体未保存，返回默认值
        if (!$entity || !$entity->exists) {
            return null;
        }

        // 查找字段表
        if ($value = $this->newModelQuery()->ofEntity($entity)->first()) {
            return $value->view;
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
            'entity_name' => $entity->getEntityName(),
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
        $condition = [
            [$this->value_column, 'like', '%'.$needle.'%'],
            ['entity_name', '=', $field['entity_name']],
        ];

        // 获取查询结果
        $results = [];
        foreach ($this->newModelQuery()->where($condition)->get() as $value) {
            $results[] = $field + Arr::selectAs($value->getAttributes(), [
                'entity_id', 'langcode', $this->value_column => 'field_value',
            ]);
        }

        return $results;
    }

    /**
     * 获取实体视图
     *
     * @param  \App\Entity\EntityBase $entity
     * @return string|null
     */
    public static function findView(EntityBase $entity)
    {
        if ($item = static::ofEntity($entity)->first()) {
            return $item->view;
        }
        return null;
    }
}
