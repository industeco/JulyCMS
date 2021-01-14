<?php

namespace App\Settings;

use Illuminate\Support\Arr;

abstract class GroupedSettingsBase
{
    /**
     * 分组名
     *
     * @var string
     */
    protected static $group = '';

    /**
     * 配置项
     *
     * @var array
     */
    protected static $keys = [];

    /**
     * 获取配置文件路径
     *
     * @return string
     */
    protected static function getPath()
    {
        return base_path('settings/'.static::$group.'.php');
    }

    /**
     * 加载配置
     *
     * @return void
     */
    public static function load()
    {
        if (is_file($file = static::getPath())) {
            config(require $file);
        }
    }

    /**
     * 保存配置到文件
     *
     * @param  array $settings
     * @return void
     */
    public static function save(array $settings)
    {
        // 过滤配置，并合并到当前配置环境
        $default = array_fill_keys(static::$keys, null);
        $settings = array_merge($default, Arr::only($settings, static::$keys));
        config($settings);

        // 将配置保存到文件
        $content = '<?php'.PHP_EOL.PHP_EOL.'return '.trim(var_export($settings, TRUE)).';'.PHP_EOL;
        file_put_contents(static::getPath(), $content);
    }

    public static function view()
    {
        return view('settings.'.static::$group)->render();
    }
}
