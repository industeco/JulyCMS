<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\Json;

class UserPreference extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'user_preferences';

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
        'user_id',
        'config_keyname',
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

    public function getKeynameAttribute()
    {
        return $this->attributes['config_keyname'];
    }

    public function gather()
    {
        return [
            'keyname' => $this->attributes['config_keyname'],
            'value' => $this->getValue(),
        ];
    }

    public function getValue()
    {
        $data = $this->data;
        return cast_value($data['value'], $data['value_type']);
    }
}
