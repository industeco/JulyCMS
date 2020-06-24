<?php

namespace App\Traits;

use App\Support\Arr;
use App\Exceptions\InvalidCacheKeyArguments;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait CacheModel
{
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
        if (is_string($key) && is_null($conditions)) {
            return $key;
        }

        if (is_string($key) && is_array($conditions)) {
            if (method_exists($this, 'getKey')) {
                $conditions['id'] = $this->getKey();
            }
            ksort($conditions);
            $key = [
                'class' => static::class,
                'key' => $key,
                'conditions' => $conditions,
            ];

            return md5(json_encode($key));
        }

        if (is_array($key)) {
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
