<?php

namespace App\Models;

use App\Contracts\HasModelConfig;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use App\Traits\CacheRetrieve;
use Illuminate\Support\Collection;

abstract class JulyModel extends Model
{
    use CacheRetrieve;

    /**
     * 哪些字段可更新（白名单）
     *
     * @var array
     */
    protected $updateOnly = [];

    /**
     * 哪些字段不可更新（黑名单）
     *
     * @var array
     */
    protected $updateExcept = [];

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        if ($this instanceof HasModelConfig) {
            $attributes['config'] = $this->buildConfig($attributes);
        }

        if ($this->updateOnly) {
            $attributes = array_intersect_key($attributes, array_flip($this->updateOnly));
        } elseif ($this->updateExcept) {
            $attributes = array_diff_key($attributes, array_flip($this->updateExcept));
        }

        return $this->fill($attributes)->save($options);
    }

    public static function make(array $attributes = [])
    {
        $instance = new static($attributes);
        if ($instance instanceof HasModelConfig) {
            $instance->config = $instance->buildConfig($attributes);
        }

        return $instance;
    }

    public static function primaryKeyName()
    {
        return 'id';
    }

    public function primary()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public static function fetch($id)
    {
        if (! $id) {
            return null;
        }
        if (is_array($id) || $id instanceof Arrayable) {
            return static::fetchMany($id);
        }

        $app = app();
        $alias = static::class.'/'.$id;
        if ($app->has($alias)) {
            $instance = $app->get($alias);
            if ($instance && $instance instanceof static) {
                return $instance;
            }
        }

        if ($instance = static::find($id)) {
            $app->instance($alias, $instance);
            return $instance;
        }

        return null;
    }

    public static function fetchMany($ids)
    {
        if ($ids instanceof Arrayable) {
            $ids = $ids->toArray();
        }

        if (!is_array($ids)) {
            $ids = (array) $ids;
        }

        $aliasPrefix = static::class.'/';
        $app = app();

        $instances = [];
        $freshids = [];
        foreach ($ids as $id) {
            $alias = $aliasPrefix.$id;
            if ($app->has($alias)) {
                $instance = $app->get($aliasPrefix.$id);
                if ($instance && $instance instanceof static) {
                    $instances[] = $instance;
                    continue;
                }
            }
            $freshids[] = $id;
        }

        if ($freshids) {
            foreach (static::findMany($freshids) as $instance) {
                $app->instance($aliasPrefix.$instance->primary(), $instance);
                $instances[] = $instance;
            }
        }

        return collect($instances);
    }

    public static function fetchAll()
    {
        $aliasPrefix = static::class.'/';
        $app = app();

        $instances = static::all();
        foreach ($instances as $instance) {
            $alias = $aliasPrefix.$instance->primary();
            if (! $app->has($alias)) {
                $app->instance($alias, $instance);
            }
        }

        return collect($instances->all());
    }

    public function forceUpdate()
    {
        if ($this->timestamps) {
            $this->attributes[static::UPDATED_AT] = Date::now();
            $this->save();
        }
    }

    public function mixConfig(array $langcode = [])
    {
        $data = $this->toArray();
        if ($this instanceof HasModelConfig) {
            $data = array_merge($data, $this->getConfigOptions($langcode));
            unset($data['config']);
        }
        return $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function columns($columns = null, $keyName = null, $except = false)
    {
        $columns = (array) $columns;
        if ($columns) {
            $columns = array_flip($columns);
        }

        $models = [];
        foreach (static::all() as $model) {
            $model = $model->mixConfig();
            $key = $model[$keyName] ?? null;
            if ($columns) {
                $model = $except ? array_diff_key($model, $columns) : array_intersect_key($model, $columns);
            }
            if ($key) {
                $models[$key] = $model;
            } else {
                $models[] = $model;
            }
        }

        return collect($models);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function columnsExcept(array $columns = [], $keyName = null)
    {
        return static::columns($columns, $keyName, true);
    }
}
