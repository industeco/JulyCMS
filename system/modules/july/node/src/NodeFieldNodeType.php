<?php

namespace July\Node;

use App\EntityField\FieldMoldPivotBase;
use Illuminate\Support\Facades\DB;

class NodeFieldNodeType extends FieldMoldPivotBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_field_node_type';

    // /**
    //  * 统计字段被引用次数
    //  *
    //  * @return array
    //  */
    // public static function countNodeFieldReference()
    // {
    //     $query = DB::table((new static)->getTable())
    //         ->selectRaw('`node_field_id`, COUNT(*) as `total`')
    //         ->groupBy('node_field_id');

    //     return $query->pluck('total', 'node_field_id')->all();
    // }
}
