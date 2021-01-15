<?php

namespace App\Settings;

class Settings
{
    /**
     * 登记的配置组
     *
     * @var array
     */
    protected static $groups = [];

    /**
     * 登记配置组实例
     *
     * @param  \App\Settings\SettingsBase $instance
     * @return void
     */
    public static function register(SettingsBase $instance)
    {
        static::$groups[$instance->getName()] = $instance;
    }

    /**
     * 查找配置组
     *
     * @param  string $groupName
     * @return \App\Settings\SettingsBase|null
     */
    public static function find(string $groupName)
    {
        return static::$groups[$groupName] ?? null;
    }
}
