<?php

namespace App\Concerns;

use App\Utils\Pocket;
use App\Utils\Value;
use Illuminate\Support\Arr;

trait CacheResultTrait
{
    /**
     * 在这里缓存执行结果
     *
     * @var array
     */
    protected $resultCache = [
        'cache' => [],
        'piping' => [],
    ];

    /**
     * @param  string $method
     * @param  array $args
     * @return \App\Utils\Value|null
     */
    public function pipeCache(string $method, ?string $key = null)
    {
        // 如果指定的方法为 pipeCache 或 pipePocket，则直接返回 null
        if ($method === 'pipeCache' || $method === 'pipePocket') {
            return null;
        }

        // 存储键 = 方法 + 参数
        $key = $key ?? $method;

        // 尝试获取值，如果成功则返回该值
        $value = $this->resultCache['cache'][$key] ?? null;
        if (is_object($value) && $value instanceof Value) {
            return $value;
        }

        // 如果 method 无效，或正在被 pipeCache 执行，返回 null
        if ($this->resultCache['piping'][$method] ?? false || !method_exists($this, $method)) {
            return null;
        }

        // 尝试通过执行 method 获取值
        try {
            // 标记 method 正在运行
            $this->resultCache['piping'][$method] = true;

            // 执行 method，获取结果
            $this->resultCache['cache'][$key] = new Value($this->$method());

            // 取消 method 运行标记
            $this->resultCache['piping'][$method] = false;

            // 返回结果
            return $this->resultCache['cache'][$key];
        } catch (\Throwable $th) {

            // 取消 method 运行标记
            $this->resultCache['pipeCache'][$method] = false;

            // 发生错误，则返回 null
            return null;
        }
    }

    /**
     * @param  string $method
     * @param  string|null $key
     * @param  array $args
     * @return \App\Utils\Value|null
     */
    public function pipePocket(string $method, ?string $key = null)
    {
        // 如果指定的方法为 pipeCache 或 pipePocket，则直接返回 null
        if ($method === 'pipeCache' || $method === 'pipePocket') {
            return null;
        }

        // 存储键 = 方法 + 参数
        $key = $key ?: $method;

        // 尝试获取值，如果成功则返回该值
        $value = $this->resultCache['cache'][$key] ?? null;
        if (is_object($value) && $value instanceof Value) {
            return $value;
        }

        // 尝试从缓存（通过 Pocket）获取值
        $pocket = new Pocket($this, $key);
        if ($value = $pocket->get()) {
            return $value;
        }

        // 如果 method 无效，或正在被 pipeCache 或 pipePocket 执行，返回 null
        if ($this->resultCache['piping'][$method] ?? false || !method_exists($this, $method)) {
            return null;
        }

        // 尝试通过执行 method 获取值
        try {
            // 标记 method 正在运行
            $this->resultCache['piping'][$method] = true;

            // 执行 method，获取结果
            $this->resultCache['cache'][$key] = new Value($this->$method());

            // 取消 method 运行标记
            $this->resultCache['piping'][$method] = false;

            // 保存结果至缓存
            $pocket->put($this->resultCache['cache'][$key]);

            // 返回结果
            return $this->resultCache['cache'][$key];
        } catch (\Throwable $th) {

            // 取消 method 运行标记
            $this->resultCache['piping'][$method] = false;

            // 发生错误，则返回 null
            return null;
        }
    }

    /**
     * 将 $value 按 $key 放入 $resultCache
     *
     * @param  string $key
     * @param  mixed $value
     * @return \App\Utils\Value
     */
    public function cachePut(string $key, $value)
    {
        return $this->resultCache['cache'][$key] = new Value($value);
    }

    /**
     * 从 $resultCache 获取值
     *
     * @param  string $key
     * @return \App\Utils\Value|null
     */
    public function cacheGet(string $key)
    {
        return $this->resultCache['cache'][$key] ?? null;
    }
}
