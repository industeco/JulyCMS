<?php

namespace App\Traits;

use App\Utils\Arr;
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
    public function cacheKey($key)
    {
        if (is_string($key)) {
            return $key;
        }

        if (is_array($key)) {
            if (! isset($key['key'])) {
                throw new InvalidCacheKeyArguments;
            }

            $conditions = array_merge(Arr::except($key, ['class','key','conditions']), $key['conditions'] ?? []);
            if (method_exists($this, 'getKey')) {
                $conditions['id'] = $this->getKey();
            }
            ksort($conditions);

            return md5(json_encode([
                'class' => $key['class'] ?? static::class,
                'key' => $key['key'],
                'conditions' => $conditions,
            ]));
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
