<?php

namespace App\Settings;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

abstract class PreferencesBase extends SettingsBase
{
    /**
     * 加载配置
     *
     * @return void
     */
    public function load()
    {
        if (is_file($file = $this->getPath())) {
            app('events')->listen(Authenticated::class, function(Authenticated $event) use ($file) {
                $preferences = require $file;
                if ($settings = $preferences[$event->user->id] ?? null) {
                    config($settings);
                }
            });
        }
    }

    /**
     * 保存配置到文件
     *
     * @param  array $settings
     * @return void
     */
    public function save(array $settings)
    {
        $keys = array_keys($this->items);

        // 过滤配置，合并到当前配置环境
        $default = array_fill_keys($keys, null);
        $settings = array_merge($default, Arr::only($settings, $keys));
        config($settings);

        $file = $this->getPath();
        $preferences = [];
        if (is_file($file)) {
            $preferences = require $file;
        }
        $preferences[Auth::id()] = $settings;

        // 将配置保存到文件
        $content = '<?php'.PHP_EOL.PHP_EOL.'return '.trim(var_export($preferences, TRUE)).';'.PHP_EOL;
        file_put_contents($file, $content);
    }
}
