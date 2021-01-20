<?php

namespace App\Utils;

use App\Entity\EntityBase;
use App\Utils\PocketableInterface;
use App\Modules\Translation\TranslatableInterface;
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
            } elseif ($subject instanceof EntityBase) {
                $prefix = str_replace('\\', '/', $subject->getEntityPath());
            } elseif ($subject instanceof Model) {
                $prefix = str_replace('\\', '/', get_class($subject)).'/'.$subject->getKey();
            }
            if ($subject instanceof TranslatableInterface) {
                $langcode = '/'.$subject->getLangcode();
                if (! Str::endsWith($prefix, $langcode)) {
                    $prefix .= $langcode;
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
}
