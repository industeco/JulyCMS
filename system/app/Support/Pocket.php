<?php

namespace App\Support;

use App\Entity\EntityBase;
use App\Support\Translation\TranslatableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use stdClass;

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
     * 缓存键
     *
     * @var \App\Support\Value|null
     */
    protected $key = null;

    /**
     * 原始的 key
     *
     * @var mixed
     */
    protected $rawKey = null;

    /**
     * @param  string|object $subject 调用 Pocket 的主体：类或类实例
     * @param  mixed $key 缓存键
     */
    public function __construct($subject, $key = null)
    {
        $this->subject = $subject;
        $this->generatePrefix();
        $this->setKey($key);
    }

    /**
     * 快捷创建
     *
     * @param  string|object $subject 调用 Pocket 的主体：类或类实例
     * @param  mixed $key 默认的键名
     * @return \App\Support\Value|static
     */
    public static function make($subject, $key = null)
    {
        return new static($subject, $key);
    }

    /**
     * 生成缓存键的前缀
     *
     * @return string
     */
    protected function generatePrefix()
    {
        $prefix = $this->subject;
        if (is_object($this->subject)) {
            if ($this->subject instanceof EntityBase) {
                $prefix = str_replace('\\', '/', $this->subject->getEntityPath());
            } elseif ($this->subject instanceof Model) {
                $prefix = str_replace('\\', '/', get_class($this->subject)).'/'.$this->subject->getKey();
            } else {
                $prefix = str_replace('\\', '/', get_class($this->subject));
            }
            if ($this->subject instanceof TranslatableInterface) {
                $prefix .= '/'.$this->subject->getLangcode();
            }
        }
        $this->prefix = md5(serialize($prefix));
    }

    /**
     * 重新指定 $subject
     *
     * @param  string|object $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        $this->generatePrefix();
        $this->setKey($this->rawKey);

        return $this;
    }

    /**
     * 生成 Cache Key
     *
     * @param mixed $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->rawKey = $key;

        $this->key = is_null($key) ? $key : $this->getKey($key);

        return $this;
    }

    /**
     * 获取缓存键
     *
     * @param  mixed $key
     * @return \App\Support\Value
     */
    public function getKey($key = null)
    {
        if (is_null($key)) {
            return $this->key;
        }

        if ($key instanceof Value) {
            $key = $key->value();
        }

        if (is_array($key)) {
            asort($key);
        }

        return new Value($this->prefix.'/'.md5(serialize($key)));
    }

    /**
     * 存储值到缓存中
     *
     * @param  mixed $value
     * @param  array $keys
     * @return mixed
     */
    public function put($value, ...$keys)
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        if (! $keys) {
            Cache::put($this->getKey()->value(), $value);
        } else {
            foreach ($keys as $key) {
                Cache::put($this->getKey($key)->value(), $value);
            }
        }

        return $value;
    }

    /**
     * 从缓存中获取值
     *
     * @param  mixed $key
     * @return \App\Support\Value|null
     */
    public function get($key = null)
    {
        $placeholder = new stdClass;

        $value = Cache::get($this->getKey($key)->value(), $placeholder);
        if ($value === $placeholder) {
            return null;
        }

        return new Value($value);
    }

    /**
     * 清除目标缓存
     *
     * @param  array $keys
     * @return boolean
     */
    public function clear(...$keys)
    {
        foreach ($keys as $key) {
            Cache::forget($this->getKey($key)->value());
        }

        return true;
    }
}
