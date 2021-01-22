<?php

namespace App\Concerns;

use App\Utils\Value;

trait CacheGetTrait
{
    /**
     * 在这里缓存执行结果
     *
     * @var array
     */
    protected $resultCache = [
        'cacheGet' => [],
    ];

    /**
     * @param  string $method
     * @return \App\Utils\Value|null
     */
    public function cacheGet(string $key, ?string $method = null)
    {
        // 如果指定的键为当前方法名，则直接返回 null
        if ($key === 'cacheGet') {
            return null;
        }

        // 尝试获取值，如果成功则返回该值
        $value = $this->resultCache[$key] ?? null;
        if (is_object($value) && $value instanceof Value) {
            return $value;
        }

        // 若 $method 无效，则使用 $key 代替
        $method = $method ?: $key;

        // 如果 method 正在被 cacheGet 执行，返回 null
        if ($this->resultCache['cacheGet'][$method] ?? false) {
            return null;
        }

        // 如果 method 无效，返回 null
        if ($method === 'cacheGet' || !method_exists($this, $method)) {
            return null;
        }

        // 尝试通过执行 method 获取值
        try {
            // 标记 method 正在运行
            $this->resultCache['cacheGet'][$method] = true;

            // 执行 method，将执行结果保存到 $resultCache
            $this->resultCache[$key] = new Value($this->$method());

            // 取消 method 运行标记
            $this->resultCache['cacheGet'][$method] = false;

            // 返回执行结果
            return $this->resultCache[$key];
        } catch (\Throwable $th) {

            // 取消 method 运行标记
            $this->resultCache['cacheGet'][$method] = false;

            // 发生错误，则返回 null
            return null;
        }
    }
}
