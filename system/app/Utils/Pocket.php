<?php

namespace App\Utils;

use App\Entity\EntityBase;
use App\Modules\Translation\TranslatableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
     * @var \App\Utils\Value|null
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
     * @param  mixed $key 默认的键名
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
     * @return \App\Utils\Value|static
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
        $this->prefix = md5(serialize($prefix)).'/';
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

        if ($key instanceof Value) {
            $key = $key->value();
        }

        if (is_array($key)) {
            asort($key);
        }

        $this->key = new Value($this->prefix.md5(serialize($key)));

        return $this;
    }

    /**
     * 获取缓存键
     *
     * @return \App\Utils\Value
     */
    public function getKey()
    {
        return $this->key;
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

        return Cache::put($this->key->value(), $value);
    }

    /**
     * 从缓存中获取值
     *
     * @return \App\Utils\Value|null
     */
    public function get()
    {
        $value = Cache::get($this->key->value(), $this);
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
        return Cache::forget($this->key->value());
    }
}
