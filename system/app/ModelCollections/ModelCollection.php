<?php

namespace App\ModelCollections;

use Illuminate\Support\Collection;
use App\Contracts\GetNodes;
use App\Models\Node;

abstract class ModelCollection extends Collection implements GetNodes
{
    abstract public static function find($arg);

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
