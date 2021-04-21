<?php

namespace App\Support;

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
     * Pocket 缓存
     *
     * @var \App\Support\Pocket|null
     */
    protected $pocket = null;

    /**
     * @param  string $method
     * @param  string|null $key
     * @param  bool $pocket
     * @return \App\Support\Value|null
     */
    public function cachePipe(string $method, ?string $key = null, $usePocket = false)
    {
        // 如果 method 正在被 pipeCache 执行，返回 null
        if ($this->resultCache['piping'][$method] ?? false) {
            return null;
        }

        // 如果 method 无效，返回 null
        if (!method_exists($this, $method) || in_array($method, [
                'cachePipe','cachePut','cacheGet','cacheClear',
                'pocketPipe','pocketPut','pocketGet','pocketClear',
            ])) {
            return null;
        }

        // 存储键 = 方法 + 参数
        $key = $key ?? $method;

        // 尝试获取值，如果成功则返回该值
        if ($value = $this->cacheGet($key)) {
            return $value;
        }

        // 尝试从缓存获取值（使用 Pocket）
        if ($usePocket && ($value = $this->pocketGet($key))) {
            return $value;
        }

        // 尝试通过执行 method 获取值
        try {
            // 标记 method 正在运行
            $this->resultCache['piping'][$method] = true;

            // 执行 method，获取结果
            $value = $this->$method();

            // 取消 method 运行标记
            $this->resultCache['piping'][$method] = false;

            // 缓存执行结果
            $this->cachePut($value, $key);
            if ($usePocket) {
                $this->pocketPut($value, $key);
            }

            // 返回结果
            return new Value($value);

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
     * @return mixed
     */
    public function cachePut($value, string $key)
    {
        $this->resultCache['cache'][$key] = new Value($value);
        return $value;
    }

    /**
     * 从 $resultCache 获取值
     *
     * @param  string $key
     * @return \App\Support\Value|null
     */
    public function cacheGet(string $key)
    {
        return $this->resultCache['cache'][$key] ?? null;
    }

    /**
     * 从 $resultCache 清除缓存
     *
     * @param  string $key
     * @return void
     */
    public function cacheClear(string $key)
    {
        $this->resultCache['cache'][$key] = null;
    }

    /**
     * @param  string $method
     * @param  string|null $key
     * @return \App\Support\Value|null
     */
    public function pocketPipe(string $method, ?string $key = null)
    {
        return $this->cachePipe($method, $key, true);
    }

    /**
     * 将 $value 按 $key 放入缓存（通过 Pocket）
     *
     * @param  mixed $value
     * @param  array $keys
     * @return mixed
     */
    public function pocketPut($value, ...$keys)
    {
        $this->getPocket()->put($value, ...$keys);
        return $value;
    }

    /**
     * 从缓存获取值（通过 Pocket）
     *
     * @param  string $key
     * @return \App\Support\Value|null
     */
    public function pocketGet($key)
    {
        return $this->getPocket()->get($key);
    }

    /**
     * 清除缓存（通过 Pocket）
     *
     * @param  string[] $keys
     * @return void
     */
    public function pocketClear(...$keys)
    {
        return $this->getPocket()->clear(...$keys);
    }

    /**
     * 获取专属 pocket
     *
     * @return \App\Support\Pocket
     */
    public function getPocket()
    {
        if (! $this->pocket) {
            $this->pocket = new Pocket($this);
        }

        return $this->pocket;
    }

    /**
     * 获取专属 pocket
     *
     * @param  mixed $key
     * @return \App\Support\Pocket
     */
    public function pocket($key = null)
    {
        if (! $this->pocket) {
            $this->pocket = new Pocket($this);
        }

        return $this->pocket->setKey($key);
    }
}
