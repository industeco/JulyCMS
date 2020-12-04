<?php

namespace App\Utils;

use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class Settings
{
    /**
     * 常规设置存储位置
     *
     * @var string
     */
    const SETTINGS_STORAGE = 'settings.bin';

    /**
     * 偏好设置存储位置
     *
     * @var string
     */
    const PREFERENCES_STORAGE = 'preferences.bin';

    /**
     * 标记常规设置是否已加载
     *
     * @var bool
     */
    protected static $settingsLoaded = false;

    /**
     * 标记偏好设置是否已加载
     *
     * @var bool
     */
    protected static $preferencesLoaded = false;

    /**
     * 加载常规设置
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @return void
     */
    public static function loadSettings(Application $app, RepositoryContract $repository)
    {
        if (static::$settingsLoaded) {
            return;
        }

        if ($data = safe_get_contents($app->configPath(static::SETTINGS_STORAGE))) {
            $repository->set(unserialize($data));
        }
        static::$settingsLoaded = true;
    }

    /**
     * 加载偏好设置
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @return void
     */
    public static function loadPreferences(Application $app, RepositoryContract $repository)
    {
        if (static::$preferencesLoaded) {
            return;
        }

        if ($userId = Auth::id()) {
            if ($data = safe_get_contents($app->configPath(static::PREFERENCES_STORAGE))) {
                $repository->set(Arr::get(unserialize($data), $userId, []));
            }
            static::$preferencesLoaded = true;
        }
    }

    /**
     * 保存常规设置
     *
     * @param  array $settings
     * @return void
     */
    public static function saveSettings(array $settings)
    {
        // 整合到当前配置数组中
        config()->set($settings);

        // 保存到缓存文件中
        $file = config_path(static::SETTINGS_STORAGE);
        if ($data = safe_get_contents($file)) {
            $settings = array_merge(unserialize($data), $settings);
        }
        file_put_contents($file, serialize($settings));
    }

    /**
     * 保存偏好设置
     *
     * @param  array $preferences
     * @return void
     */
    public static function savePreferences(array $preferences)
    {
        // 整合到当前配置数组中
        config()->set($preferences);

        // 保存到缓存文件中
        if ($userId = Auth::id()) {
            $file = config_path(static::PREFERENCES_STORAGE);
            if ($data = safe_get_contents($file)) {
                $data = unserialize($data);
                $data[$userId] = array_merge($data[$userId] ?? [], $preferences);
                $preferences = $data;
            } else {
                $preferences = [$userId => $preferences];
            }
            file_put_contents($file, serialize($preferences));
        }
    }
}
