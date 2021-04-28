<?php

namespace App\EntityValue;

use App\Entity\EntityBase;
use App\Entity\EntityManager;

class EntityPathAlias extends ValueBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'entity_path_aliases';

    /**
     * 保存字段值的列名
     *
     * @var string
     */
    protected $valueColumn = 'alias';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'entity_name',
        'entity_id',
        'entity_path',
        'langcode',
        'alias',
    ];

    /**
     * entity_path 属性的 Mutator
     *
     * @return string
     */
    public function setEntityPathAttribute()
    {
        $this->attributes['entity_path'] = $this->attributes['entity_name'].'/'.$this->attributes['entity_id'];
    }

    /**
     * 按指定的路径别名限定查询
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string $alias
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfAlias($query, $alias)
    {
        $condition = [
            ['alias', '=', $alias, 'or'],
            ['entity_path', '=', trim($alias, '/'), 'or'],
        ];

        // if (! preg_match('/\.(html?|php)$/i', $alias)) {
        //     $condition[] = ['alias', '=', ($alias ? '/'.$alias : '').'/index.html', 'or'];
        // }

        // if (config('app.entity_path_accessible')) {
        //     $condition[] = ['path', '=', $alias, 'or'];
        // }

        return $query->where($condition);
    }

    /**
     * 获取实体路径别名（网址）
     *
     * @param  \App\Entity\EntityBase $entity
     * @return string|null
     */
    public static function findAlias(EntityBase $entity)
    {
        return static::make()->getValue($entity);
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
     * 根据网址或路径查找实体
     *
     * @param string $alias
     * @param string|null $langcode
     * @return \App\Entity\EntityBase|null
     */
    public static function findEntity(string $alias, ?string $langcode = null)
    {
        /** @var \Illuminate\Database\Eloquent\Builder */
        $query = static::ofAlias($alias);
        if ($langcode) {
            $query->where('langcode', $langcode);
        }

        if ($instance = $query->first()) {
            return EntityManager::resolve($instance->entity_name, $instance->entity_id);
        }
        return null;
    }
}
