<?php

namespace App\Models;

use App\Casts\Json;
use App\Support\Arr;
use Illuminate\Support\Facades\Auth;

class Config extends JulyModel
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'configs';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'keyname';

    /**
     * 主键“类型”。
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 指示模型主键是否递增
     *
     * @var bool
     */
    public $incrementing = false;

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
        $factory = config();
        foreach (static::all() as $config) {
            $factory->set('jc.'.$config->keyname, $config->getValue());
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
        $value = null;
        if ($this->group === 'preference') {
            if (($user = Auth::user()) && ($user instanceof User)) {
                $value = $user->getPreferenceValue($this->keyname);
            }
        }
        if (is_null($value)) {
            $data = $this->data;
            $value = cast_value($data['value'], $data['value_type']);
        }
        return $value;
    }

    public function gather()
    {
        $attribues = $this->except(['data']);
        $attribues['value'] = $this->getValue();
        return $attribues;
    }

    public static function updateConfigurations(array $changed)
    {
        foreach (static::findMany(array_keys($changed)) as $config) {
            $data = $config->data;
            $data['value'] = cast_value($changed[$config->keyname] ?? null, $data['value_type']);

            if ($config->group === 'preference') {
                if (($user = Auth::user()) && ($user instanceof User)) {
                    $user->updatePreference($config->keyname, $data);
                    continue;
                }
            }

            $config->data = $data;
            $config->save();
        }
    }
}
