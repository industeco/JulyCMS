<?php

namespace App\Support;

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
     * @return \App\Support\Value|static
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
}
