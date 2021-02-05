<?php

namespace July\Node;

use App\Models\ModelSetBase;
use July\Node\Node;

class NodeTypeSet extends ModelSetBase
{
    /**
     * 获取绑定的模型
     *
     * @return string
     */
    public static function getModelClass()
    {
        return NodeType::class;
    }

    public function get_nodes()
    {
        $molds = $this->pluck('id')->all();

        /** @var array */
        $nodeIds = Node::query()->whereIn('mold_id', $molds)->pluck('id')->all();

        return NodeSet::fetch($nodeIds);
    }
}
