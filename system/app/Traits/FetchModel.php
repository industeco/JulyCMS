<?php

namespace App\Traits;

use Illuminate\Contracts\Support\Arrayable;

trait FetchModel
{
    public static function fetch($id)
    {
        if (! $id) {
            return null;
        }
        if (is_array($id) || $id instanceof Arrayable) {
            return static::fetchMany($id);
        }

        $app = app();
        $alias = static::class.'/'.$id;
        if ($app->has($alias)) {
            $instance = $app->get($alias);
            if ($instance && $instance instanceof static) {
                return $instance;
            }
        }

        if ($instance = static::find($id)) {
            $app->instance($alias, $instance);
            return $instance;
        }

        return null;
    }

    public static function fetchMany($ids)
    {
        if ($ids instanceof Arrayable) {
            $ids = $ids->toArray();
        }

        if (!is_array($ids)) {
            $ids = (array) $ids;
        }

        $prefix = static::class.'/';
        $app = app();

        $instances = [];
        $freshids = [];
        foreach ($ids as $id) {
            $alias = $prefix.$id;
            if ($app->has($alias)) {
                $instance = $app->get($prefix.$id);
                if ($instance && $instance instanceof static) {
                    $instances[] = $instance;
                    continue;
                }
            }
            $freshids[] = $id;
        }

        if ($freshids) {
            foreach (static::findMany($freshids) as $instance) {
                $app->instance($prefix.$instance->getKey(), $instance);
                $instances[] = $instance;
            }
        }

        return collect($instances);
    }

    public static function fetchAll()
    {
        $prefix = static::class.'/';
        $app = app();

        $instances = static::all();
        foreach ($instances as $instance) {
            $alias = $prefix.$instance->getKey();
            if (! $app->has($alias)) {
                $app->instance($alias, $instance);
            }
        }

        return collect($instances->all());
    }
}
