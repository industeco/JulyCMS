<?php

namespace App\Settings;

class SettingGroup
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
     * @param  \App\Settings\SettingGroupBase $group
     * @return \App\Settings\SettingGroupBase
     */
    public static function register(SettingGroupBase $group)
    {
        static::$groups[$group->getName()] = $group;
        return $group;
    }

    /**
     * 查找配置组
     *
     * @param  string $name
     * @return \App\Settings\SettingGroupBase|null
     */
    public static function find(string $name)
    {
        return static::$groups[$name] ?? null;
    }
}
