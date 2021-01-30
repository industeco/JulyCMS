<?php

namespace App\EntityField;

use App\Entity\EntityBase;

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
     * 保存字段值的列名
     *
     * @var string
     */
    protected $value_column = 'view';

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
     * 获取实体视图
     *
     * @param  \App\Entity\EntityBase $entity
     * @return string|null
     */
    public static function findView(EntityBase $entity)
    {
        return static::make()->getValue($entity);
    }
}
