<?php

namespace July\Node;

trait GetNodesTrait
{
    /**
     * 进一步获取节点集
     *
     * @return \July\Node\NodeSet
     */
    public function get_nodes()
    {
        $nodes = [];
        foreach ($this->items as $item) {
            if ($item instanceof Node) {
                $nodes[$item->id] = $item;
            } elseif ($item instanceof GetNodesInterface) {
                $nodes = array_merge($nodes, $item->get_nodes()->all());
            }
        }

        return NodeSet::make($nodes);
    }
}
