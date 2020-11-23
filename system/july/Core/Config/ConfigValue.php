<?php

namespace July\Core\Config;

use App\Casts\Serialized;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ConfigValue extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'config__value';

    /**
     * 指示是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'config_id',
        'langcode',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'value' => Serialized::class,
    ];

    /**
     * 内建属性登记处
     *
     * @var array
     */
    protected static $columns = [
        'id',
        'config_id',
        'langcode',
        'value',
    ];
}
