<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\Json;
use App\Traits\CacheRetrieve;
use Illuminate\Support\Arr;

class JulyConfig extends Model
{
    use CacheRetrieve;

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

    public static function primaryKeyName()
    {
        return 'truename';
    }

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

        return static::retrieveConfiguration($names, $langcode);
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

        return static::retrieveConfiguration($names, $langcode);
    }

    /**
     * 批量获取设置数据
     *
     * @param array $names 配置真名
     * @param string $langcode
     * @return array
     */
    public static function retrieveConfiguration(array $names, $langcode = null)
    {
        $configuration = [];
        $fresh = [];
        foreach ($names as $truename) {
            if ($entry = static::cacheGet($truename)) {
                $configuration[$truename] = static::mixConfig($entry['value'], $langcode);
            } else {
                $configuration[$truename] = null;
                $fresh[] = $truename;
            }
        }

        if ($fresh) {
            foreach (static::findMany($fresh) as $entry) {
                $entry = $entry->toArray();
                static::cachePut($entry['truename'], $entry);
                $configuration[$entry['truename']] = static::mixConfig($entry, $langcode);
            }
        }

        return $configuration;
    }

    public function getValue($langcode = null)
    {
        $config = $this->config;
        return cast_value($config['value'], $config['value_type']);
    }

    protected static function mixConfig($entry, $langcode = null)
    {
        $ilang = Arr::get($entry, 'langcode.interface_value') ?: langcode('interface_value');
        $langcode = $langcode ?: $ilang;

        $config = $entry['config'];
        unset($entry['config']);
        foreach (['label', 'description'] as $attribute) {
            if ($value = $config[$attribute] ?? null) {
                $entry[$attribute] = $value[$langcode] ?? $value[$ilang] ?? null;
            } else {
                $entry[$attribute] = null;
            }
        }
        $entry['value'] = cast_value($config['value'], $config['value_type']);

        return $entry;
    }

    public static function updateConfiguration(array $configuration)
    {
        foreach (static::findMany(array_keys($configuration)) as $entry) {
            static::cacheClear($entry->truename);
            $entry->config = array_merge($entry->config, [
                'value' => $configuration[$entry->truename],
            ]);
            $entry->save();
        }
    }
}
