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
     * 调用 Pocket 的主体：类或类实例
     *
     * @var string|object
     */
    protected $subject;

    /**
     * 键名前缀
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * 默认的键名
     *
     * @var \App\Utils\Value
     */
    protected $pockey;

    // /**
    //  * 执行 takeout 动作的实际方法
    //  *
    //  * @var string
    //  */
    // protected $takeoutMethod = '';

    /**
     * @param  string|object $subject 调用 Pocket 的主体：类或类实例
     * @param  mixed $key 默认的键名
     */
    public function __construct($subject, $key = '')
    {
        $this->subject = $subject;
        $this->prefix = static::generatePrefix($subject);
        $this->useKey($key);
    }

    /**
     * 快捷创建
     *
     * @param  string|object $subject 调用 Pocket 的主体：类或类实例
     * @param  mixed $key 默认的键名
     * @return self
     */
    public static function make($subject, string $key = null)
    {
        return new static($subject, $key);
    }

    /**
     * 快捷创建
     *
     * @param  string|object $subject 调用 Pocket 的主体：类或类实例
     * @param  mixed $key 默认的键名
     * @return self
     */
    public static function apply($subject, string $key = null)
    {
        return new static($subject, $key);
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
     * 获取缓存键
     *
     * @return \App\Utils\Value
     */
    public function getKey()
    {
        return $this->pockey;
    }

    /**
     * 生成 Cache Key
     *
     * @param mixed $key
     * @return $this
     */
    public function useKey($key)
    {
        if ($key instanceof Value) {
            $key = $key->value();
        }

        if (is_array($key)) {
            asort($key);
        }

        $this->pockey = new Value($this->prefix.short_md5(serialize($key)));

        return $this;
    }

    /**
     * 存储值到缓存中
     *
     * @param mixed $value
     * @return bool
     */
    public function put($value)
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        return Cache::put($this->pockey->value(), $value);
    }

    /**
     * 从缓存中获取值
     *
     * @return \App\Utils\Value|null
     */
    public function get()
    {
        $value = Cache::get($this->pockey->value(), $this);
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
    public function clear()
    {
        return Cache::forget($this->pockey->value());
    }

    // /**
    //  * 判断是否 Pocket Key
    //  *
    //  * @param  mixed $key
    //  * @return bool
    //  */
    // protected function isPockey($key)
    // {
    //     if (!is_object($key) || !($key instanceof Value)) {
    //         return false;
    //     }

    //     $key = $key->value();
    //     return is_string($key) &&
    //         preg_match('/^[a-f0-9]{16}\/[a-f0-9]{16}$/', $key) &&
    //         Str::startsWith($key, $this->prefix);
    // }

    // /**
    //  * 指定使用主体上的哪个方法执行 takeout
    //  *
    //  * @param  string $method
    //  * @return $this
    //  */
    // public function takeoutUse(string $method)
    // {
    //     $this->takeoutMethod = $method;

    //     return $this;
    // }

    // /**
    //  * 取出缓存的数据
    //  *
    //  * @param  string $key
    //  * @param  array $parameters 其它参数
    //  * @return mixed
    //  */
    // public function takeout(string $key, array $parameters = [])
    // {
    //     if ($value = $this->get($key)) {
    //         return $value;
    //     }

    //     $method = $this->takeoutMethod ?: 'retrieve'.Str::studly($key);
    //     return tap($this->callSubject($method, $parameters), function($value) use ($key) {
    //         $this->put($key, $value);
    //     });
    // }

    // /**
    //  * 调用 subject 上的方法
    //  *
    //  * @param  string $method
    //  * @param  array $parameters
    //  * @return mixed
    //  */
    // protected function callSubject(string $method, array $parameters)
    // {
    //     if (is_object($this->subject)) {
    //         return optional($this->subject)->$method(...$parameters);
    //     }

    //     if (is_string($this->subject) && class_exists($this->subject)) {
    //         return optional(new $this->subject)->$method(...$parameters);
    //     }

    //     return null;
    // }
}
