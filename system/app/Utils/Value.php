<?php

namespace App\Utils;

class Value
{
    /**
     * @var mixed
     */
    protected $value = null;

    public function __construct($value)
    {
        if ($value instanceof static) {
            $value = $value->value();
        }
        $this->value = $value;
    }

    public static function create($value)
    {
        return new static($value);
    }

    public function value()
    {
        return $this->value;
    }

    public function __get($name)
    {
        if ($name === 'value') {
            return $this->value;
        }
        return null;
    }
}
