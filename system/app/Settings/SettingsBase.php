<?php

namespace App\Settings;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;

abstract class SettingsBase
{
    /**
     * 标记设置是否已加载
     *
     * @var bool
     */
    protected static $isLoaded = false;

    /**
     * 配置文件
     *
     * @var string
     */
    protected static $file;

    /**
     * 加载设置
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @return void
     */
    public static function load(Application $app, RepositoryContract $repository)
    {
        if (static::$isLoaded) {
            return;
        }

        if ($data = safe_get_contents($app->basePath(static::$file))) {
            static::merge(unserialize($data), $repository);
        }
        static::$isLoaded = true;
    }

    /**
     * 保存常规设置
     *
     * @param  array $settings
     * @return void
     */
    public static function save(array $settings)
    {
        // 整合到当前配置数组中
        static::merge($settings, config());

        // 保存到缓存文件中
        $file = base_path(static::$file);
        if ($data = safe_get_contents($file)) {
            $settings = array_merge(unserialize($data), $settings);
        }
        file_put_contents($file, serialize($settings));
    }

    /**
     * 整合配置数据到当前设置中
     *
     * @param  array $settings
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @return void
     */
    public static function merge(array $settings, RepositoryContract $repository)
    {
        foreach ($settings as $key => $value) {
            if (is_array($value) && is_array($repository->get($key))) {
                $value = array_merge($repository->get($key), $value);
            }
            $repository->set($key, $value);
        }
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
        static::merge($preferences, config());

        // 保存到缓存文件中
        if ($userId = Auth::id()) {
            $file = base_path(static::$file);
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
