<?php

namespace July\Core\Entity;

use Illuminate\Support\Collection;

abstract class EntitySetBase extends Collection
{
    /**
     * @var string
     */
    protected static $model;

    public static function getKeyName()
    {
        return (new static::$model)->getKeyName();
    }

    public static function find($args)
    {
        if (empty($args)) {
            return new static;
        }

        if ($args instanceof static) {
            return $args;
        }

        if ($args instanceof Collection) {
            $args = $args->all();
        }

        if (! is_array($args)) {
            $args = [$args];
        }

        return static::findArray($args);
    }

    public static function findAll()
    {
        $model = static::$model;
        $primaryKey = static::getKeyName();

        return (new static($model::fetchAll()))->keyBy($primaryKey);
    }

    public static function findArray(array $args)
    {
        $model = static::$model;
        $primaryKey = static::getKeyName();

        $items = [];
        foreach ($args as $arg) {
            if ($arg instanceof static) {
                $items = array_merge($items, $arg->all());
            } elseif (is_object($arg) && ($arg instanceof $model)) {
                $items[$arg->getKey()] = $arg;
            } elseif ($instance = $model::fetch($arg)) {
                $items[$instance->getKey()] = $instance;
            }
        }

        return (new static($items))->keyBy($primaryKey);
    }
}
