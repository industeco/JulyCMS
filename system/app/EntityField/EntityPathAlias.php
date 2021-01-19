<?php

namespace App\EntityField;

use App\Entity\EntityManager;

class EntityPathAlias extends FieldBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'entity_path_aliases';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'entity_name',
        'entity_id',
        'langcode',
        'alias',
    ];

    public function scopeAlias($query, $alias)
    {
        $alias = trim(str_replace('\\', '/', $alias), "/ \t\n\r\0\x0B");
        $condition = [
            ['alias', '=', '/'.$alias, 'or'],
        ];

        if (substr($alias, -5) !== '.html') {
            $condition[] = ['alias', '=', ($alias ? '/'.$alias : '').'/index.html', 'or'];
        }

        if (config('app.entity_path_accessible')) {
            $condition[] = ['path', '=', $alias, 'or'];
        }

        return $query->where($condition);
    }

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
     * @return \App\Entity\EntityBase|null
     */
    public static function findEntityByAlias(string $alias)
    {
        if ($instance = static::alias($alias)->first()) {
            return EntityManager::resolvePath($instance->path);
        }

        return null;
    }
}
