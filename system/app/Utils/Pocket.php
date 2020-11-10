<?php

namespace App\Utils;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Pocket
{
    /**
     * @var string|object|null
     */
    protected $subject;

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * 构造函数
     *
     * @param string|object $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
        $this->normalizePrefix();
    }

    /**
     * 快捷构造函数
     *
     * @param string|object $subject
     * @return self
     */
    public static function create($subject)
    {
        return new static($subject);
    }

    /**
     * 初始化缓存键的前缀
     *
     * @return self
     */
    protected function normalizePrefix()
    {
        $prefix = '';
        if (is_object($this->subject)) {
            $prefix = get_class($this->subject);
            $subject = optional($this->subject);
            if (! empty($key = $subject->getKey() ?? $subject->getEntityId())) {
                $prefix .= '/'.trim($key);
            }
        } else {
            $prefix = trim($this->subject);
        }
        $this->prefix = rtrim(str_replace('\\', '/', $prefix), '/').'/';

        return $this;
    }

    /**
     * 生成 Cache Key
     *
     * @param mixed $key
     * @return \App\Utils\Value
     */
    public function key($key)
    {
        if ($key instanceof Value) {
            return $key;
        }

        if (is_array($key)) {
            asort($key);
        }

        if (! is_string($key)) {
            $key = serialize($key);
        }

        return new Value(md5($this->prefix.ltrim($key, '\\/')));
    }

    /**
     * 存储值到缓存中
     *
     * @param mixed $key
     * @param mixed $value
     * @return boolean
     */
    public function put($key, $value)
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        return Cache::put($this->key($key)->value(), $value);
    }

    /**
     * 从缓存中获取值
     *
     * @param mixed $key
     * @return \App\Utils\Value|null
     */
    public function get($key)
    {
        $value = Cache::get($this->key($key)->value(), $this);
        if ($value === $this) {
            return null;
        }

        return new Value($value);
    }

    /**
     * 清除目标缓存
     *
     * @param  mixed $key
     * @return boolean
     */
    public function clear($key)
    {
        return Cache::forget($this->key($key)->value());
    }

    /**
     * 取出缓存的数据
     *
     * @param  string $key
     * @param  array $parameters 其它参数
     * @return \App\Utils\Value|null
     */
    public function takeout(string $key, ...$parameters)
    {
        if ($value = $this->get($key)) {
            return $value;
        }

        $method = 'takeout'.Str::studly($key);
        $parameters = normalize_args($parameters);
        if (!is_null($value = $this->callSubject($method, $parameters))) {
            $this->put($key, $value);
            return new Value($value);
        }

        return null;
    }

    /**
     * 调用 subject 上的方法
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    protected function callSubject(string $method, array $parameters)
    {
        if (is_object($this->subject)) {
            if ($value = optional($this->subject)->$method(...$parameters)) {
                return $value;
            }
        }

        if (is_string($this->subject) && class_exists($this->subject)) {
            if (method_exists($this->subject, $method)) {
                return $this->subject::$method(...$parameters);
            } else {
                if ($value = optional(new $this->subject)->$method(...$parameters)) {
                    return $value;
                }
            }
        }

        return null;
    }
}
