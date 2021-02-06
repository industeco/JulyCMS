<?php

namespace July\Node;

use App\EntityField\FieldBase;
use Illuminate\Support\Facades\Log;

class NodeField extends FieldBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_fields';

    /**
     * 获取实体类
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return Node::class;
    }

    // /**
    //  * 获取使用过当前字段的所有类型
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    //  */
    // public function nodeTypes()
    // {
    //     return $this->belongsToMany(NodeType::class, 'node_field_node_type', 'node_field_id', 'node_type_id')
    //                 ->orderBy('node_field_node_type.delta')
    //                 ->withPivot([
    //                     'delta',
    //                     // 'weight',
    //                     'label',
    //                     'description',
    //                 ]);
    // }
}
