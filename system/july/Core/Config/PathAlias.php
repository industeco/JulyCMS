<?php

namespace July\Core\Config;

use July\Core\Entity\EntityBase;
use July\Core\Entity\EntityManager;

class PathAlias extends EntityBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'path_alias';

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
        'alias',
        'langcode',
    ];

    /**
     * 根据实体路径查找别名
     *
     * @param  string $path
     * @return \Illuminate\Support\Collection
     */
    public static function findAliasByPath(string $path)
    {
        return static::query()->where('path', trim($path))->pluck('alias', 'langcode');
    }

    /**
     * 根据别名查找实体
     *
     * @param string $alias
     * @return \July\Core\Entity\EntityInterface|null
     */
    public static function findEntityByAlias(string $alias)
    {
        if ($instance = static::query()->where('alias', trim($alias))->first()) {
            return EntityManager::resolvePath($instance->path);
        }

        return null;
    }
}
