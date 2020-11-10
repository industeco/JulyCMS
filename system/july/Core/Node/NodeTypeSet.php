<?php

namespace July\Core\Node;

use July\Core\Entity\EntitySetBase;
use July\Core\Node\Node;

class NodeTypeSet extends EntitySetBase
{
    protected static $model = NodeType::class;
    protected static $primaryKey = 'id';

    public function get_nodes()
    {
        $types = $this->pluck('id')->all();
        $contents = Node::whereIn('content_type', $types)->get('id')->pluck('id')->all();
        return NodeSet::find($contents);
    }
}
