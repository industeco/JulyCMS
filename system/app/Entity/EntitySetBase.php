<?php

namespace July\Core\Entity;

use Illuminate\Support\Collection;

abstract class EntitySetBase extends Collection
{
    /**
     * @var string
     */
    protected static $entity;

    public static function getKeyName()
    {
        return (new static::$entity)->getKeyName();
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

        return static::findMany($args);
    }

    public static function findMany(array $args)
    {
        $entity = static::$entity;
        $key = static::getKeyName();

        $entities = [];
        foreach ($args as $arg) {
            // if ($arg instanceof static) {
            //     $entities = array_merge($entities, $arg->all());
            // } else
            if (is_object($arg) && ($arg instanceof $entity)) {
                $entities[$arg->getKey()] = $arg;
            } elseif ($instance = $entity::carry($arg)) {
                $entities[$instance->getKey()] = $instance;
            }
        }

        return (new static($entities))->keyBy($key);
    }

    public static function findAll()
    {
        $entity = static::$entity;
        $key = static::getKeyName();

        return (new static($entity::carryAll()))->keyBy($key);
    }
}
