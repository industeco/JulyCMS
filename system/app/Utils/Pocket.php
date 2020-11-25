<?php

namespace App\Utils;

use App\Contracts\PocketableInterface;
use App\Contracts\TranslatableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Pocket
{
    /**
     * @var string|object
     */
    protected $subject;

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @param  string|object $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;

        $this->prefix = static::generatePrefix($subject);
    }

    /**
     * 快捷创建
     *
     * @param  string|object $subject
     * @return self
     */
    public static function make($subject)
    {
        return new static($subject);
    }

    /**
     * 快捷创建
     *
     * @param  string|object $subject
     * @return self
     */
    public static function apply($subject)
    {
        return new static($subject);
    }

    /**
     * 生成缓存键的前缀
     *
     * @param  string|object $subject
     * @return string
     */
    public static function generatePrefix($subject)
    {
        $prefix = $subject;
        if (is_object($subject)) {
            if ($subject instanceof PocketableInterface) {
                $prefix = $subject->getPocketId();
            } elseif ($subject instanceof Model) {
                $prefix = str_replace('\\', '/', get_class($subject)).'/'.$subject->getKey();
                if ($subject instanceof TranslatableInterface ) {
                    $prefix .= '/'.$subject->getLangcode();
                }
            }
        }

        return short_md5(serialize($prefix)).'/';
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
            if ($this->isKey($key->value())) {
                return $key;
            }
            $key = $key->value();
        }

        if (is_array($key)) {
            asort($key);
        }

        return new Value($this->prefix.short_md5(serialize($key)));
    }

    /**
     * 判断是否 Pocket Key
     *
     * @param  mixed $key
     * @return bool
     */
    protected function isKey($key)
    {
        return is_string($key) &&
            preg_match('/^[a-f0-9]{16}\/[a-f0-9]{16}$/', $key) &&
            Str::startsWith($key, $this->prefix);
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
            return optional($this->subject)->$method(...$parameters);
        }

        if (is_string($this->subject) && class_exists($this->subject)) {
            return optional(new $this->subject)->$method(...$parameters);
        }

        return null;
    }
}
