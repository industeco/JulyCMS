<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait CacheRetrieve
{
    public function cacheKey($id, $langcode = null)
    {
        $key = static::class.'/'.trim($id);
        if ($langcode) {
            $key .= '/'.$langcode;
        }
        // Log::info('CacheRetrieve Key: '.$key);
        return md5($key);
    }

    public function cachePut($id, $value, $langcode = null)
    {
        // Log::info('CacheRetrieve Put:');
        return Cache::put($this->cacheKey($id, $langcode), $value);
    }

    public function cacheGet($id, $langcode = null)
    {
        // Log::info('CacheRetrieve Get:');
        $def = uniqid();
        $value = Cache::get($this->cacheKey($id, $langcode), $def);
        if ($value === $def) {
            return null;
        }
        return ['value' => $value];
    }

    public function cacheClear($id, $langcode = null)
    {
        // Log::info('CacheRetrieve Clear:');
        return Cache::forget($this->cacheKey($id, $langcode));
    }
}
