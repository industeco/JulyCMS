<?php

namespace App\Support\Settings;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

abstract class PreferenceGroupBase extends SettingGroupBase
{
    /**
     * 加载配置
     *
     * @return void
     */
    public function load()
    {
        app('events')->listen(Authenticated::class, function(Authenticated $event) {
            if (is_file($file = $this->getPath())) {
                $preferences = require $file;
                if ($settings = $preferences[$event->user->id] ?? null) {
                    $this->merge($settings);
                }
            }
        });
    }

    /**
     * 整合配置数据到当前配置中，然后保存到文件
     *
     * @param  array $settings
     * @return void
     */
    public function save(array $settings)
    {
        // 过滤并整合配置数据到当前配置环境
        $settings = $this->merge($settings);

        $file = $this->getPath();
        $preferences = [];
        if (is_file($file)) {
            $preferences = require $file;
        }
        $preferences[Auth::id()] = $settings;

        // 保存配置数据到文件
        $content = "<?php\n\nreturn ".trim(var_export($preferences, TRUE)).";\n";
        file_put_contents($file, $content);
    }
}
