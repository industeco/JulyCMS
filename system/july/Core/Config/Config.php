<?php

namespace July\Core\Config;

use App\Models\ModelBase;
use App\Utils\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use July\Core\Entity\EntityBase;
use July\Core\Entity\EntityInterface;

class Config extends ModelBase
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
    protected $primaryKey = 'id';

    /**
     * 主键类型
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 模型主键是否递增
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'group',
        'label',
        'description',
        // 'is_readonly',
        // 'is_translatable',
        'langcode',
    ];

    /**
     * 分组获取
     *
     * @param string $group
     * @return array
     */
    public static function getConfigsByGroup($group)
    {
        return static::query()->where('group', $group)->get()->map(function(Config $record) {
            return $record->gather();
        })->keyBy('id')->all();
    }

    /**
     * 获取配置值
     *
     * @return mixed
     */
    public function getValue()
    {
        return config($this->getKey());
    }

    /**
     * 生成一个完整的配置数组，包括本地属性（id,group,label,...）+ value
     *
     * @return array
     */
    public function gather()
    {
        return array_merge($this->attributesToArray(), [
            'value' => $this->getValue()
        ]);
    }

    /**
     * 更新配置
     *
     * @param array $changed 变动数据
     * @return void
     */
    public static function updateConfigs(array $changed)
    {
        $settings = [];
        $preferences = [];

        $groups = static::all()->pluck('group', 'id');

        // 分拣变动数据
        foreach ($changed as $key => $value) {
            if ('user_preferences' === ($groups[$key] ?? null)) {
                $preferences[$key] = $value;
            } else {
                $settings[$key] = $value;
            }
        }

        Settings::saveSettings($settings);
        Settings::savePreferences($preferences);
    }
}
