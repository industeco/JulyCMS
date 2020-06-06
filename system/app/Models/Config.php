<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\Json;
use Illuminate\Support\Arr;

class Config extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'configs';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'keyname',
        'group',
        'label',
        'description',
        'user',
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

    public static function loadConfigurations()
    {
        $config = config();
        foreach (static::all() as $record) {
            $key = 'jc.'.$record->keyname;
            $config->set($key, $record->getValue());
        }
    }

    public static function get($key, $default = null)
    {
        $item = static::find($key);
        if ($item) {
            return $item->getValue() ?? $default;
        }
        return $default;
    }

    /**
     * 分组获取
     *
     * @param string $group
     * @return array
     */
    public static function getGroup($group)
    {
        return static::where('group', $group)->get()->map(function($record) {
            return $record->gather();
        })->keyBy('keyname')->all();
    }

    public function getValue()
    {
        $data = $this->data;
        return cast_value($data['value'], $data['value_type']);
    }

    public function gather()
    {
        return [
            'keyname' => $this->attributes['keyname'],
            'group' => $this->attributes['group'],
            'label' => $this->attributes['label'],
            'description' => $this->attributes['description'],
            'value' => $this->getValue(),
        ];
    }

    public static function updateConfigurations(array $changed)
    {
        foreach (static::findMany(array_keys($changed)) as $config) {
            $data = $config->data;
            $data['value'] = cast_value($changed[$config->keyname] ?? null, $data['value_type']);
            $config->data = $data;
            $config->save();
        }
    }
}
