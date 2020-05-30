<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\Json;
use App\Contracts\HasConfig;
use App\Traits\CacheRetrieve;
use Illuminate\Support\Arr;

class Config extends Model implements HasConfig
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
        'name',
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

    public static function loadConfiguration()
    {
        $config = config();
        $records = static::where('keyname', 'like', 'config.%')->get();
        foreach ($records as $record) {
            $key = 'jc.'. substr($record->keyname, 7);
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

    public static function getGroup($group)
    {
        $records = static::where('group', $group)->get();
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
            'multi_language', 'langcode.permissions', 'langcode.content', 'langcode.page',
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
        $ilang = langcode('admin_page');
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
