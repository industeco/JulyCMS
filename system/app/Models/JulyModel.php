<?php

namespace App\Models;

use App\Exceptions\InvalidCacheKeyArguments;
use App\Support\Arr;
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

    public static function make(array $attributes)
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
     * 获取常用属性
     *
     * @param string|null $langcode
     * @return array
     */
    public function gather($langcode = null)
    {
        return $this->attributesToArray();
    }

    /**
     * 生成 cacheKey
     *
     * @param array|string $key
     * @param array|null $conditions
     * @return string
     *
     * @throws \App\Exceptions\InvalidCacheKeyArguments
     */
    public function cacheKey($key, array $conditions = null)
    {
        if (is_string($key)) {
            if (is_null($conditions)) {
                return $key;
            } else {
                $conditions['id'] = $this->getKey();
                ksort($conditions);
                $key = [
                    'class' => static::class,
                    'key' => $key,
                    'conditions' => $conditions,
                ];
                // Log::info('CacheKey:');
                // Log::info($key);
                return md5(json_encode($key));
            }
        } elseif (is_array($key)) {
            if (!isset($key['key'])) {
                throw new InvalidCacheKeyArguments;
            }
            return $this->cacheKey($key['key'], $key['conditions'] ?? Arr::except($key, ['class','key']));
        }

        throw new InvalidCacheKeyArguments;
    }

    /**
     * 存储值到缓存中
     *
     * @param string|array $key
     * @param mixed $value
     * @return boolean
     */
    public function cachePut($key, $value)
    {
        // Log::info('CacheValue Put:');
        return Cache::put($this->cacheKey($key), $value);
    }

    /**
     * 从缓存中获取值
     *
     * @param string|array $key
     * @return array|null
     */
    public function cacheGet($key)
    {
        // Log::info('CacheValue Get:');
        $uid = uniqid();
        $value = Cache::get($this->cacheKey($key), $uid);
        if ($value === $uid) {
            return null;
        }
        return ['value' => $value];
    }

    /**
     * 清除目标缓存
     *
     * @param string|array $key
     * @return boolean
     */
    public function cacheClear($key)
    {
        // Log::info('CacheValue Clear:');
        return Cache::forget($this->cacheKey($key));
    }
}
