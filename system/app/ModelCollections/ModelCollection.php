<?php

namespace App\ModelCollections;

use Illuminate\Support\Collection;
use App\Contracts\GetNodes;
use App\Models\Node;

abstract class ModelCollection extends Collection implements GetNodes
{
    protected static $model;
    protected static $primaryKey;

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
        $primaryKey = static::$primaryKey;
        return (new static($model::fetchAll()))->keyBy($primaryKey);
    }

    public static function findArray(array $args)
    {
        $model = static::$model;
        $primaryKey = static::$primaryKey;

        $items = [];
        foreach ($args as $arg) {
            if ($arg instanceof static) {
                $items = array_merge($items, $arg->all());
            } elseif ($arg instanceof $model) {
                $items[$arg->$primaryKey] = $arg;
            } elseif ($model = $model::fetch($arg)) {
                $items[$model->$primaryKey] = $model;
            }
        }

        return (new static($items))->keyBy($primaryKey);
    }

    /**
     * 进一步获取节点集
     */
    public function get_nodes():NodeCollection
    {
        $nodes = [];
        foreach ($this->items as $item) {
            if ($item instanceof Node) {
                $nodes[$item->id] = $item;
            } elseif ($item instanceof GetNodes) {
                $nodes = array_merge($nodes, $item->get_nodes()->all());
            }
        }
        return NodeCollection::make($nodes);
    }
}
