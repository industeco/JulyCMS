<?php

namespace App\ModelCollections;

use App\Models\NodeType;

class NodeTypeCollection extends ModelCollection
{
    public static function find($args)
    {
        if (empty($args)) {
            return new static();
        }
        if (! is_array($args)) {
            $args = [$args];
        }
        $items = [];
        foreach ($args as $arg) {
            if (is_string($arg)) {
                if ($nodeType = NodeType::fetch($arg)) {
                    $items[$nodeType->truename] = $nodeType;
                }
            } elseif ($arg instanceof NodeType) {
                $items[$arg->truename] = $arg;
            } elseif ($arg instanceof static) {
                $items = array_merge($items, $arg->all());
            }
        }

        return new static($items);
    }
}
