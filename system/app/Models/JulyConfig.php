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
    protected $table = 'configs';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'name';

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
        'name',
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

    public static function loadConfiguration()
    {
        $configuration = [];
        foreach (static::all() as $entry) {
            $configuration['jc.'.$entry->name] = $entry->getValue();
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
            'langcode.permissions', 'langcode.content_value', 'langcode.site_page', 'multi_language',
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
        foreach ($names as $name) {
            if ($entry = static::cacheGet($name)) {
                $configuration[$name] = static::mixConfig($entry['value'], $langcode);
            } else {
                $configuration[$name] = null;
                $fresh[] = $name;
            }
        }

        if ($fresh) {
            foreach (static::findMany($fresh) as $entry) {
                $entry = $entry->toArray();
                static::cachePut($entry['name'], $entry);
                $configuration[$entry['name']] = static::mixConfig($entry, $langcode);
            }
        }

        return $configuration;
    }

    public function getValue($langcode = null)
    {
        $data = $this->data;
        return cast_value($data['value'], $data['value_type']);
    }

    protected static function mixConfig($entry, $langcode = null)
    {
        $ilang = Arr::get($entry, 'langcode.interface_value') ?: langcode('interface_value');
        $langcode = $langcode ?: $ilang;

        $data = $entry['data'];
        unset($entry['data']);
        foreach (['label', 'description'] as $attribute) {
            if ($value = $data[$attribute] ?? null) {
                $entry[$attribute] = $value[$langcode] ?? $value[$ilang] ?? null;
            } else {
                $entry[$attribute] = null;
            }
        }
        $entry['value'] = cast_value($data['value'], $data['value_type']);

        return $entry;
    }

    public static function updateConfiguration(array $configuration)
    {
        foreach (static::findMany(array_keys($configuration)) as $entry) {
            static::cacheClear($entry->name);
            $entry->data = array_merge($entry->data, [
                'value' => cast_value($configuration[$entry->name], $entry->data['value_type']),
            ]);
            $entry->save();
        }
    }
}
