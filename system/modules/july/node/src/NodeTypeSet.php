<?php

namespace July\Node;

use App\Entity\EntitySetBase;
use July\Node\Node;

class NodeTypeSet extends EntitySetBase
{
    protected static $entity = NodeType::class;

    public function get_nodes()
    {
        $types = $this->pluck('id')->all();

        /** @var array */
        $nodeIds = Node::query()->whereIn('node_type_id', $types)->pluck('id')->all();

        return NodeSet::findMany($nodeIds);
    }
}
