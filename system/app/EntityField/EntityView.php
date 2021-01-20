<?php

namespace App\EntityField;

use App\Model;

class EntityView extends Model
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
