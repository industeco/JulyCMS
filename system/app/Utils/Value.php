<?php

namespace App\Utils;

class Value
{
    /**
     * @var mixed
     */
    protected $value = null;

    /**
     * @param  mixed $value
     */
    public function __construct($value)
    {
        if (is_object($value) && $value instanceof static) {
            $value = $value->value();
        }
        $this->value = $value;
    }

    /**
     * 快捷创建
     *
     * @param  mixed $value
     * @return static
     */
    public static function make($value)
    {
        return new static($value);
    }

    /**
     * 获取 $value
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * 允许以属性方式获取
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($name === 'value') {
            return $this->value;
        }
        return null;
    }
}
