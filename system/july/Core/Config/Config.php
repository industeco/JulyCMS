<?php

namespace July\Core\Config;

use App\Model;
use App\Utils\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use July\Core\Entity\EntityBase;
use July\Core\Entity\EntityInterface;

class Config extends Model
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
     * 覆写时转换一些数据
     *
     * @var array
     */
    protected static $mutators = [
        'jc.form.editor.ckeditor.filebrowserImageBrowseUrl' => 'short_url',
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

        // 分拣变动数据
        foreach (static::all()->pluck('group', 'id') as $key => $group) {
            if (!isset($changed[$key])) {
                continue;
            }

            if ($group === 'user_preferences') {
                $preferences[$key] = $changed[$key];
            } else {
                $settings[$key] = $changed[$key];
            }
        }

        Settings::saveSettings($settings);
        Settings::savePreferences($preferences);
    }
}
