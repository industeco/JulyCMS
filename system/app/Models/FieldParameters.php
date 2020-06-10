<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\Json;

class FieldParameters extends Model
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
        'keyname',
        'data',
        'langcode',
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