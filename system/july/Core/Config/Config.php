<?php

namespace July\Core\Config;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use July\Core\Entity\EntityBase;
use July\Core\Entity\EntityInterface;

class Config extends EntityBase implements EntityInterface
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
        'is_readonly',
        'is_translatable',
        'langcode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_readonly' => 'boolean',
        'is_translatable' => 'boolean',
    ];

    /**
     * 默认加载的关联
     *
     * @var array
     */
    protected $with = [
        'values', 'preferences'
    ];

    /**
     * 内建属性登记处
     *
     * @var array
     */
    protected static $columns = [
        'id',
        'group',
        'label',
        'description',
        'is_readonly',
        'is_translatable',
        'langcode',
        'updated_at',
        'created_at',
    ];

    /**
     * 覆写时转换一些数据
     *
     * @var array
     */
    protected static $converts = [
        'jc.form.editor.ckeditor.filebrowserImageBrowseUrl' => 'short_url',
    ];

    /**
     * 获取配置值
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function values()
    {
        return $this->hasMany(ConfigValue::class);
    }

    /**
     * 获取偏好设置
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function preferences()
    {
        return $this->hasMany(UserPreferences::class);
    }

    /**
     * 从数据库中加载配置数据，并覆写当前配置
     *
     * @return void
     */
    public static function overwrite()
    {
        // Log::info('overwrite');
        if (config('state.configs_overwritten')) {
            return;
        }

        $config = app('config');
        foreach (static::all()->keyBy('id') as $key => $record) {
            if (! is_null($value = $record->getValue())) {
                $config->set($key, $value);
            }
        }

        foreach (static::$converts as $key => $method) {
            $config->set($key, static::convert($config->get($key), $method));
        }

        $config->set('state.configs_overwritten', true);
    }

    /**
     * 使用指定方法转换一个值
     *
     * @param mixed $value
     * @param string $method
     * @return mixed
     */
    public static function convert($value, $method)
    {
        if (is_null($value)) {
            return null;
        }

        if (method_exists(static::class, $method)) {
            return static::$method($value);
        }

        if (function_exists($method)) {
            return call_user_func($method, $value);
        }

        return $value;
    }

    /**
     * 获取配置值
     *
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (! config('state.configs_overwritten')) {
            static::overwrite();
        }

        return config($key, $default);
    }

    /**
     * 分组获取
     *
     * @param string $group
     * @return array
     */
    public static function getConfigsByGroup($group)
    {
        return static::query()->where('group', $group)->get()->map(function($record) {
            return $record->gather();
        })->keyBy('id')->all();
    }

    /**
     * 获取富文本编辑器配置
     *
     * @return array
     */
    public static function getEditorConfig()
    {
        if (! config('state.configs_overwritten')) {
            static::overwrite();
        }

        $config = config('jc.form.editor.'.config('jc.form.editor.default', 'ckeditor'), []);

        return (array) $config;
    }

    /**
     * 获取配置值
     *
     * @param  string|null $langcode 语言代码，有些设置是可翻译的
     * @return mixed
     */
    public function getValue(string $langcode = null)
    {
        if ($this->is_readonly) {
            return config($this->getKey());
        }

        // 获取 ConfigValue
        $langcode = $langcode ?? $this->langcode;
        $value = $this->values->first(function($v) use($langcode) {
            return $v->langcode === $langcode;
        });

        if ($value) {
            $value = $value->value;
        }

        // 获取 UserPereferences
        if ('user_preferences' === $this->group && ($user = Auth::user())) {
            $userId = $user->id;
            $preferences = $this->preferences->first(function($p) use($userId) {
                return $p->user_id == $userId;
            });

            if ($preferences) {
                $value = $preferences->value;
            }
        }

        return $value ?? config($this->getKey());
    }

    /**
     * 生成一个完整的配置数组，包括本地属性（id,group,label,...）+ value
     *
     * @param string|null $langcode
     * @return array
     */
    public function gather($langcode = null)
    {
        return array_merge($this->getAttributes(), [
            'value' => $this->getValue($langcode)
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
        // 获取当前用户
        $user = Auth::user();

        // 保存变动数据
        foreach (static::findMany(array_keys($changed)) as $config) {
            $key = $config->getKey();
            $langcode = $config->langcode;
            $value = $changed[$key];

            if ($config->group === 'user_preferences') {
                if ($user) {
                    // 更新用户偏好设置
                    UserPreferences::query()->updateOrCreate([
                        'config_id' => $key,
                        'user_id' => $user->id,
                    ], ['value' => $value]);
                }
            } else {
                // 更新配置值
                ConfigValue::query()->updateOrCreate([
                    'config_id' => $key,
                    'langcode' => $langcode === 'zxx' ? $langcode : langcode('content'),
                ], ['value' => $value]);
            }
        }
    }
}
