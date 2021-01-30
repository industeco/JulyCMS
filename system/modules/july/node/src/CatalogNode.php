<?php

namespace July\Node;

use App\Models\PivotBase;

class CatalogNode extends PivotBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'catalog_node';

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
        'catalog_id',
        'node_id',
        'parent_id',
        'prev_id',
        'path',
    ];

    protected $casts = [
        'node_id' => 'int',
        'parent_id' => 'int',
        'prev_id' => 'int',
    ];
}
