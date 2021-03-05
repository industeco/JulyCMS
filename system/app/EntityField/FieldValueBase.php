<?php

namespace App\EntityField;

use App\Entity\EntityBase;
use App\Models\ModelBase;
use App\Utils\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

abstract class FieldValueBase extends ModelBase
{
    /**
     * 绑定的字段
     *
     * @var \App\EntityField\FieldBase
     */
    protected $field;

    /**
     * 保存字段值的列名
     *
     * @var string
     */
    protected $value_column;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    // protected $guarded = [];

    /**
     * 获取值列名
     *
     * @return string
     */
    public function getValueColumn()
    {
        return $this->value_column;
    }

    /**
     * 设置值列
     *
     * @param  string|null $column
     */
    public function setValueColumn($column)
    {
        $this->value_column = $column;

        return $this;
    }

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

        if ($this->isDynamic()) {
            $fieldType = $field->getFieldType();

            // 设置模型表
            $this->setTable($field->getDynamicValueTable());

            // 获取列名
            $this->value_column = $fieldType->getColumn()['name'];

            $this->attributes = [
                $this->value_column => $field->getParameters()['default_value'] ?? $fieldType->getDefaultValue(),
            ];

            // 设置 fillable
            $this->fillable([
                'entity_id',
                $this->value_column,
                'langcode',
                'updated_at',
            ]);

            // 设置字段值转换
            $this->casts = [
                $this->value_column => $fieldType->getCaster(),
            ];
        }

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
        if ($value = $this->newQuery()->ofEntity($entity)->first()) {
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

        $values = array_merge(array_fill_keys($this->fillable, null), [
            $this->value_column => $value,
        ]);

        return $this->newQuery()->updateOrCreate(
            $attributes,
            Arr::except($values, ['entity_name','entity_id','langcode'])
        );
    }

    /**
     * 删除字段值
     *
     * @param  \App\Entity\EntityBase $entity
     * @return mixed
     */
    public function deleteValue(EntityBase $entity)
    {
        return $this->newQuery()->ofEntity($entity)->delete();
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
        foreach ($this->newQuery()->where($condition)->get() as $value) {
            $results[] = $field + [
                'entity_id' => $value->entity_id,
                'langcode' => $value->langcode,
                'field_value' => $value->{$this->value_column},
            ];
        }

        return $results;
    }

    /**
     * 获取所有字段值
     *
     * @param  string|null $langcode 指定语言版本
     * @return array
     */
    public function values(?string $langcode = null)
    {
        $conditions = [
            'langcode' => $langcode ?? $this->field->getLangcode(),
        ];
        if (in_array('entity_name', $this->fillable)) {
            $conditions['entity_name'] = $this->field->getBoundEntityName();
        }

        return $this->newQuery()
            ->where($conditions)
            ->get(['entity_id', $this->value_column])
            ->pluck($this->value_column, 'entity_id')
            ->all();
    }

    /**
     * 获取所有字段值，不区分语言，不做转换
     *
     * @return array
     */
    public function records()
    {
        $conditions = [];
        if (in_array('entity_name', $this->fillable)) {
            $conditions['entity_name'] = $this->field->getBoundEntityName();
        }

        return DB::table($this->getTable())
            ->where($conditions)
            ->get([$this->value_column, 'langcode', 'entity_id'])
            ->map(function($record) {
                return [
                    'entity_id' => $record->entity_id,
                    'value' => $record->{$this->value_column},
                    'langcode' => $record->langcode,
                ];
            })
            ->all();
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = (new static)->fillable($this->getFillable())->fill((array) $attributes);

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());

        $model->mergeCasts($this->casts);

        $model->setValueColumn($this->getValueColumn());

        return $model;
    }
}
