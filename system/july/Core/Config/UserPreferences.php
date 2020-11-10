<?php

namespace July\Core\Config;

use App\Model;
use App\Casts\Serialized;
use Illuminate\Support\Facades\Log;

class UserPreferences extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'user_preferences';

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
        'config_id',
        'user_id',
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
}
