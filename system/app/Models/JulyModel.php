<?php

namespace App\Models;

use APP\Support\Arr;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

abstract class JulyModel extends Model
{
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

    public static function make(array $attributes = [])
    {
        return new static($attributes);
    }

    public static function primaryKeyName()
    {
        return (new static)->getKeyName();
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
                $app->instance($aliasPrefix.$instance->getKey(), $instance);
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
            $alias = $aliasPrefix.$instance->getKey();
            if (! $app->has($alias)) {
                $app->instance($alias, $instance);
            }
        }

        return collect($instances->all());
    }

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

        if ($this->updateOnly) {
            $attributes = Arr::only($attributes, $this->updateOnly);
        } elseif ($this->updateExcept) {
            $attributes = Arr::except($attributes, $this->updateExcept);
        }

        return $this->fill($attributes)->save($options);
    }

    /**
     * 排除指定属性
     *
     * @param array $columns
     * @return array
     */
    public function except(array $columns = [])
    {
        return Arr::except($this->attributesToArray(), $columns);
    }

    /**
     * 准备用于生成 cacheKey 的数组
     *
     * @param static|null $model
     * @param string $name
     * @param array $conditions
     * @return array
     */
    public static function prepareCacheKey($model, $name, array $conditions = [])
    {
        if ($model && ($model instanceof static)) {
            $conditions['key'] = $model->getKey();
        }
        return [
            'type' => static::class,
            'name' => $name,
            'conditions' => $conditions,
        ];
    }

    /**
     * 生成 cacheKey
     *
     * @param array|string $key
     * @return string
     */
    public static function cacheKey($key)
    {
        if (is_array($key)) {
            $key = [
                'type' => $key['type'] ?? static::class,
                'name' => $key['name'] ?? null,
                'conditions' => ksort($key['conditions'] ?? Arr::except($key, ['type','name'])),
            ];
            // Log::info($key);
            return md5(json_encode($key));
        }
        // Log::info('md5: '.$key);
        return (string) $key;
    }

    /**
     * 存储值到缓存中
     *
     * @param array|string $key
     * @param mixed $value
     * @return boolean
     */
    public static function cachePut($key, $value)
    {
        // Log::info('CacheValue Put:');
        return Cache::put(static::cacheKey($key), $value);
    }

    /**
     * 从缓存中获取值
     *
     * @param array|string $key
     * @return array|null
     */
    public static function cacheGet($key)
    {
        // Log::info('CacheValue Get:');
        $uid = uniqid();
        $value = Cache::get(static::cacheKey($key), $uid);
        if ($value === $uid) {
            return null;
        }
        return ['value' => $value];
    }

    /**
     * 清除目标缓存
     *
     * @param array|string $key
     * @return boolean
     */
    public static function cacheClear($key)
    {
        // Log::info('CacheValue Clear:');
        return Cache::forget(static::cacheKey($key));
    }
}
