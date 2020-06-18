<?php

namespace App\Models;

use App\Casts\Json;
use App\FieldTypes\FieldType;

class FieldParameters extends JulyModel
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'field_parameters';

    /**
     * 重定义主键
     *
     * @var string|null
     */
    protected $primaryKey = 'keyname';

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
        'keyname',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => Json::class,
    ];
}
