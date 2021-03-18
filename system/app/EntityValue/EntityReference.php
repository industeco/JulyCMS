<?php

namespace App\EntityValue;

use App\EntityField\FieldBase;
use App\Entity\EntityBase;
use App\Entity\EntityManager;
use App\EntityField\FieldTypes\MultiReference;
use App\EntityField\FieldTypes\Reference;
use App\Support\Arr;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EntityReference extends ValueBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'entity_references';

    /**
     * 保存字段值的列名
     *
     * @var string
     */
    protected $valueColumn = 'reference_id';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'entity_name',
        'entity_id',
        'field_id',
        'reference_name',
        'reference_id',
    ];

    /**
     * 缓存的实体 id
     *
     * @var \Illuminate\Database\Eloquent\Collection|static[]|null
     */
    protected $cachedValues = null;

    /**
     * 按指定实体限定查询
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Entity\EntityBase $entity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfEntity($query, EntityBase $entity)
    {
        $entity = $this->field->getBoundEntity() ?? $entity;
        $condition = [
            'entity_id' => $entity->getEntityId(),
            'entity_name' => $entity->getEntityName(),
            'field_id' => $this->field->getKey(),
        ];
        return $query->where($condition);
    }

    /**
     * 获取字段值
     *
     * @return int[]|int|null
     */
    public function getValue()
    {
        if ($this->field->field_type === Reference::class) {
            return $this->{$this->valueColumn};
        }

        if (is_null($this->cachedValues)) {
            $entity = $this->field->getBoundEntity();

            $this->cachedValues = $this->newQuery()->where([
                'entity_id' => $entity->getEntityId(),
                'entity_name' => $entity->getEntityName(),
                'field_id' => $this->field->getKey(),
            ])->get();
        }

        return $this->cachedValues->pluck($this->valueColumn)->values();
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
        DB::beginTransaction();

        $this->deleteValue($entity);

        $entity = $this->field->getBoundEntity() ?? $entity;
        $record = [
            'entity_id' => $entity->getEntityId(),
            'entity_name' => $entity->getEntityName(),
            'field_id' => $this->field->getKey(),
            'reference_name' => $this->field->getMeta('reference_scope')[0],
        ];

        foreach (Arr::wrap($value) as $id) {
            $this->newQuery()->create($record + ['reference_id' => $id]);
        }

        DB::commit();

        return true;
    }

    /**
     * 删除字段值
     *
     * @param  \App\Entity\EntityBase $entity
     * @return mixed
     */
    public function deleteValue(EntityBase $entity)
    {
        $entity = $this->field->getBoundEntity() ?? $entity;

        return $this->newQuery()->ofEntity($entity)->delete();
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
            'entity_name' => $this->field->getBoundEntityName(),
            'field_id' => $this->field->getKey(),
        ];

        return $this->newQuery()
            ->where($conditions)
            ->pluck($this->valueColumn, 'entity_id')
            ->all();
    }
}
