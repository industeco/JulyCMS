<?php

namespace July\Node;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;

class NodeFieldNodeType extends Pivot
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_field_node_type';

    /**
     * 指示是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'node_type_id',
        'node_field_id',
        'delta',
        // 'weight',
        'label',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'delta' => 'int',
        // 'weight' => 'decimal:2',
    ];

    /**
     * 统计字段被引用次数
     *
     * @return array
     */
    public static function countNodeFieldReference()
    {
        $query = DB::table((new static)->getTable())
            ->selectRaw('`node_field_id`, COUNT(*) as `total`')
            ->groupBy('node_field_id');

        return $query->pluck('total', 'node_field_id')->all();
    }
}
