<?php

namespace App\Traits;

use App\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait CacheValue
{
    public static function cacheKey($key)
    {
        if (is_array($key)) {
            $key = [
                'class' => $key['class'] ?? static::class,
                'name' => $key['name'] ?? null,
                'conditions' => $key['conditions'] ?? ksort(Arr::except($key, ['class','name'])),
            ];
            // Log::info($key);
            $key = md5(json_encode($key));
        }
        // Log::info('md5: '.$key);
        return $key;
    }

    public static function cachePut($key, $value)
    {
        // Log::info('CacheValue Put:');
        return Cache::put(static::cacheKey($key), $value);
    }

    /**
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

    public static function cacheClear($key)
    {
        // Log::info('CacheValue Clear:');
        return Cache::forget(static::cacheKey($key));
    }
}
