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

    /**
     * 按指定实体限定查询
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \July\Node\Catalog $catalog
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCatalog($query, Catalog $catalog)
    {
        return $query->where('catalog_id', $catalog->getKey());
    }
}
