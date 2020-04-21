<?php

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use App\Traits\CacheRetrieve;

class JulyModel extends Model
{
    use CacheRetrieve;

    public function primary()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

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
            $ids = [$ids];
        }

        $aliasPrefix = static::class.'/';
        $app = app();

        $instances = [];
        $freshids = [];
        foreach ($ids as $id) {
            $alias = $aliasPrefix.$id;
            if ($app->has($alias)) {
                $instance = $app->get($aliasPrefix.$id);
                if ($instance && $instance instanceof static) {
                    $instances[] = $instance;
                    continue;
                }
            }
            $freshids[] = $id;
        }

        if ($freshids) {
            foreach (static::findMany($freshids) as $instance) {
                $app->instance($aliasPrefix.$instance->primary(), $instance);
                $instances[] = $instance;
            }
        }

        return collect($instances);
    }

    public function forceUpdate()
    {
        if ($this->timestamps) {
            $this->attributes[static::CREATED_AT] = Date::now();
            $this->save();
        }
    }
}
