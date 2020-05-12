<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\Json;
use Illuminate\Support\Arr;

class JulyConfig extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'july_configs';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'truename';

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
        'truename',
        'is_preset',
        'config',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
        'config' => Json::class,
    ];

    public static function loadConfiguration()
    {
        $configuration = [];
        foreach (static::all() as $entry) {
            $configuration['jc.'.$entry->truename] = $entry->getValue();
        }
        app('config')->set($configuration);

        return $configuration;
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
     * 获取网站基本设置
     *
     * @param string $langcode
     * @return array
     */
    public static function getBasicSettings($langcode = null)
    {
        $names = [
            'owner', 'url', 'email',
        ];

        return static::getSettings($names, $langcode);
    }

    /**
     * 获取语言设置
     *
     * @param string $langcode
     * @return array
     */
    public static function getLanguageSettings($langcode = null)
    {
        $names = [
            'languages', 'content_lang', 'site_page_lang',
        ];

        return static::getSettings($names, $langcode);
    }

    /**
     * 批量获取设置数据
     *
     * @param array $names 配置真名
     * @param string $langcode
     * @return array
     */
    public static function getSettings(array $names, $langcode = null)
    {
        $langcode = $langcode ?: langcode('interface_value');

        $settings = [];
        foreach (static::findMany($names) as $entry) {
            $settings[$entry->truename] = $entry->mixConfig($langcode);
        }

        return $settings;
    }

    public function getValue($langcode = null)
    {
        $config = $this->config;
        return cast_value($config['value'], $config['value_type']);
    }

    public function mixConfig($langcode = null)
    {
        $attributes = $this->getAttributes();
        $config = $this->config;

        $ilang = Arr::get($config, 'langcode.interface_value') ?: langcode('interface_value');
        $langcode = $langcode ?: $ilang;

        foreach (['label', 'description'] as $attribute) {
            if ($value = $config[$attribute] ?? null) {
                $attributes[$attribute] = $value[$langcode] ?? $value[$ilang] ?? null;
            } else {
                $attributes[$attribute] = null;
            }
        }
        $attributes['value'] = $this->getValue();
        unset($attributes['config']);

        return $attributes;
    }
}
