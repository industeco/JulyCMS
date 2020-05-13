<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait CacheRetrieve
{
    public static function cacheKey($key, $langcode = null)
    {
        $key = static::class.'/'.trim($key);
        if ($langcode) {
            $key .= '/'.$langcode;
        }
        // Log::info('CacheRetrieve Key: '.$key);
        return md5($key);
    }

    public static function cachePut($key, $value, $langcode = null)
    {
        // Log::info('CacheRetrieve Put:');
        return Cache::put(static::cacheKey($key, $langcode), $value);
    }

    public static function cacheGet($key, $langcode = null)
    {
        // Log::info('CacheRetrieve Get:');
        $uid = uniqid();
        $value = Cache::get(static::cacheKey($key, $langcode), $uid);
        if ($value === $uid) {
            return null;
        }
        return ['value' => $value];
    }

    public static function cacheClear($key, $langcode = null)
    {
        // Log::info('CacheRetrieve Clear:');
        return Cache::forget(static::cacheKey($key, $langcode));
    }
}
