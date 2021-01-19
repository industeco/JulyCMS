<?php

namespace App\Entity;

use Illuminate\Contracts\Support\Arrayable;

trait CarryEntityTrait
{
    public static function carry($id)
    {
        if (! $id) {
            return null;
        }

        if ($id instanceof Arrayable) {
            $id = $id->toArray();
        }

        if (is_array($id)) {
            return static::carryMany($id);
        }

        $app = app();
        $alias = static::getCarryPrefix().$id;
        if ($app->has($alias)) {
            return $app->get($alias);
        }

        if ($entity = static::find($id)) {
            $app->instance($alias, $entity);
            return $entity;
        }

        return null;
    }

    public static function carryMany(array $ids)
    {
        $prefix = static::getCarryPrefix();
        $app = app();

        $entities = [];
        $freshIds = [];
        foreach ($ids as $id) {
            if ($app->has($prefix.$id)) {
                $entities[] = $app->get($prefix.$id);
                continue;
            }
            $freshIds[] = $id;
        }

        if ($freshIds) {
            foreach (static::carryMany($freshIds) as $entity) {
                $app->instance($prefix.$entity->getKey(), $entity);
                $entities[] = $entity;
            }
        }

        return collect($entities);
    }

    public static function carryAll()
    {
        $prefix = static::getCarryPrefix();
        $app = app();

        $entities = static::all();
        foreach ($entities as $entity) {
            $alias = $prefix.$entity->getKey();
            if (! $app->has($alias)) {
                $app->instance($alias, $entity);
            }
        }

        return collect($entities->all());
    }

    protected static function getCarryPrefix()
    {
        return 'entity://'.static::getEntityName().'/';
    }
}
