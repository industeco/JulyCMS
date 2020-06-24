<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CatalogContent extends Pivot
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'catalog_content';

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
        'catalog',
        'content_id',
        'parent_id',
        'prev_id',
        'path',
    ];
}
