<?php

namespace July\Core\Node;

use July\Core\Entity\EntitySetBase;
use July\Core\Node\Node;

class NodeTypeSet extends EntitySetBase
{
    protected static $entity = NodeType::class;

    public function get_nodes()
    {
        $types = $this->pluck('id')->all();
        $nodeIds = Node::query()->whereIn('node_type_id', $types)->pluck('id')->all();
        return NodeSet::find($nodeIds);
    }
}
