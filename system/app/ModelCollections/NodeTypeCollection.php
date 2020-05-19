<?php

namespace App\ModelCollections;

use App\Models\Node;
use App\Models\NodeType;
use Illuminate\Support\Collection;

class NodeTypeCollection extends ModelCollection
{
    protected static $model = NodeType::class;
    protected static $primaryKey = 'truename';

    public function get_nodes(): NodeCollection
    {
        $types = $this->pluck('truename')->all();
        $nodes = Node::whereIn('node_type', $types)->get('id')->pluck('id')->all();
        return NodeCollection::find($nodes);
    }
}
