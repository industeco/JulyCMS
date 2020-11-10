<?php

namespace July\Core\Config;

use July\Core\Entity\EntityBase;

class PathView extends EntityBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'path_view';

    /**
     * 是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'path',
        'view',
        'langcode',
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
