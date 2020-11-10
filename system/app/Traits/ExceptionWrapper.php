<?php

namespace App\Traits;

trait ExceptionWrapper
{
    /**
     * @var \Exception
     */
    protected $e = null;

    public static function wrap(\Exception $e)
    {
        return (new static())->setException($e);
    }

    public function setException(\Exception $e)
    {
        $this->e = $e;

        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->e->$name(...$arguments);
    }
}
