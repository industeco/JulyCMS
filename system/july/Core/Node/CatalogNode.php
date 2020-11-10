<?php

namespace July\Core\Node;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CatalogNode extends Pivot
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
}
