<?php

namespace App\Utils;

use Illuminate\Contracts\Auth\Authenticatable;
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
    const SETTINGS_CACHE = 'bootstrap/cache/settings.bin';

    /**
     * 偏好设置存储位置
     *
     * @var string
     */
    const PREFERENCES_CACHE = 'bootstrap/cache/preferences.bin';

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

        if ($data = safe_get_contents($app->basePath(static::SETTINGS_CACHE))) {
            static::mergeSettings(unserialize($data), $repository);
        }
        static::$settingsLoaded = true;
    }

    /**
     * 加载偏好设置
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return void
     */
    public static function loadPreferences(Application $app, RepositoryContract $repository, Authenticatable $user)
    {
        if (static::$preferencesLoaded) {
            return;
        }

        if ($data = safe_get_contents($app->basePath(static::PREFERENCES_CACHE))) {
            static::mergeSettings(unserialize($data)[$user->getAuthIdentifier()] ?? [], $repository);
        }
        static::$preferencesLoaded = true;
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
        static::mergeSettings($settings);

        // 保存到缓存文件中
        $file = base_path(static::SETTINGS_CACHE);
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
        static::mergeSettings($preferences);

        // 保存到缓存文件中
        if ($userId = Auth::id()) {
            $file = base_path(static::PREFERENCES_CACHE);
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

    /**
     * 整合配置数据到当前设置中
     *
     * @param  array $settings
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @return void
     */
    public static function mergeSettings(array $settings, RepositoryContract $repository = null)
    {
        if ($repository === null) {
            $repository = app('config');
        }
        foreach ($settings as $key => $value) {
            if (is_array($value) && is_array($repository->get($key))) {
                $value = array_merge($repository->get($key), $value);
            }
            $repository->set($key, $value);
        }
    }
}
