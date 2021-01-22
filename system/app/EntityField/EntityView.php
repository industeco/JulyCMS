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
     * 获取实体视图
     *
     * @param  \App\Entity\EntityBase $entity
     * @return string|null
     */
    public static function findView(EntityBase $entity)
    {
        if ($item = static::query()->where([
            'path' => $entity->getEntityPath(),
            'langcode' => $entity->getLangcode(),
            ])->first()) {
            return $item->view;
        }
        return null;
    }

    /**
     * 根据实体路径查找配置项
     *
     * @param string $path
     * @return \Illuminate\Support\Collection
     */
    public static function findViewByPath(string $path)
    {
        return static::query()->where('path', trim($path))->pluck('view', 'langcode');
    }
}
