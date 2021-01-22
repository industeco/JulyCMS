<?php

namespace App\Concerns;

use App\Utils\Value;

trait CacheResultTrait
{
    /**
     * 缓存执行结果
     *
     * @var array
     */
    protected $resultCache = [];

    /**
     * @param  string $method
     * @return \App\Utils\Value|null
     */
    public function cacheGet(string $method)
    {
        $value = $this->resultCache[$method] ?? null;
        if (is_object($value) && $value instanceof Value) {
            return $value;
        }

        try {
            return $this->resultCache[$method] = new Value($this->$method());
        } catch (\Throwable $th) {
            return null;
        }
    }
}
