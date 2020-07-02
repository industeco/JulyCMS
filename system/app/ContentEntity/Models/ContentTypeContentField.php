<?php

namespace App\ContentEntity\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ContentTypeContentField extends Pivot
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'content_field_content_type';

    /**
     * 重定义主键
     *
     * @var string|null
     */
    protected $primaryKey = null;

    /**
     * 指示模型主键是否递增
     *
     * @var bool
     */
    public $incrementing = false;

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
        'content_type',
        'content_field',
        'delta',
        'weight',
        'label',
        'description',
    ];
}
