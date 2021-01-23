<?php

namespace App\EntityField;

use App\Entity\EntityBase;
use App\Entity\EntityManager;

class EntityPathAlias extends FieldValueBase
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
        'entity_path',
        'langcode',
        'alias',
    ];

    /**
     * 获取所有列名
     *
     * @return array
     */
    public static function getColumns()
    {
        return [
            'id',
            'entity_name',
            'entity_id',
            'entity_path',
            'langcode',
            'alias',
            'updated_at',
            'created_at',
        ];
    }

    /**
     * entity_path 属性的 Set Mutator
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
        $alias = trim(str_replace('\\', '/', $alias), "/ \t\n\r\0\x0B");
        $condition = [
            ['alias', '=', '/'.$alias, 'or'],
        ];

        if (! preg_match('/\.(html?|php)$/i', $alias)) {
            $condition[] = ['alias', '=', ($alias ? '/'.$alias : '').'/index.html', 'or'];
        }

        if (config('app.entity_path_accessible')) {
            $condition[] = ['path', '=', $alias, 'or'];
        }

        return $query->where($condition);
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
            return $value->getAttributeValue('alias');
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
            'alias' => $value,
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
        $field = $this->field->gather(['id', 'field_type_id', 'label', 'description']);
        $condition = ['alias', 'like', '%'.$needle.'%'];

        $results = [];
        foreach ($this->newModelQuery()->where($condition)->get() as $value) {
            $results[] = $field + [
                'entity_name' => $value->entity_name,
                'entity_id' => $value->entity_id,
                'langcode' => $value->langcode,
                'value' => $value->alias,
            ];
        }

        return $results;
    }

    /**
     * 获取实体路径别名（网址）
     *
     * @param  \App\Entity\EntityBase $entity
     * @return string|null
     */
    public static function findAlias(EntityBase $entity)
    {
        if ($item = static::query()->where([
                'path' => $entity->getEntityPath(),
                'langcode' => $entity->getLangcode(),
            ])->first()) {
            return $item->alias;
        }
        return null;
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
     * @return \App\Entity\EntityBase|null
     */
    public static function findEntity(string $alias)
    {
        if ($instance = static::alias($alias)->first()) {
            return EntityManager::resolve($instance->entity_name, $instance->entity_id);
        }
        return null;
    }
}
