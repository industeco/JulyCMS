<?php

namespace App\Support;

use ArrayAccess;
use Illuminate\Support\Traits\Macroable;

class JulyInTwig implements ArrayAccess
{
    use Macroable;

    protected $entries = [];

    /**
     * 添加一个项目（数据或对象）
     *
     * @param  string $name 新条目的键名
     * @param  mixed $entry 条目内容
     * @return $this
     */
    public function addEntry(string $name, $entry)
    {
        $this->entries[$name] = $entry;

        return $this;
    }

    /**
     * 移除项目
     *
     * @param  string $name 目标键名
     * @return $this
     */
    public function removeEntry(string $name)
    {
        unset($this->entries[$name]);

        return $this;
    }

    /**
     * 获取登记的项目
     *
     * @param  string $name 目标键名
     * @return mixed
     */
    public function getEntry(string $name)
    {
        return $this->entries[$name] ?? null;
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->entries);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->entries[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->entries[] = $value;
        } else {
            $this->entries[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->entries[$key]);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    public function __get($name)
    {
        return $this->entries[$name] ?? null;
    }
}
