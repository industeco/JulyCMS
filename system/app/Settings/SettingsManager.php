<?php

namespace App\Settings;

class SettingsManager
{
    /**
     * 登记的配置组
     *
     * @var \App\Settings\SettingGroupBase[]
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
        return static::$groups[$group->name] = $group;
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

    /**
     * 获取所有配置组实例
     *
     * @return \App\Settings\SettingGroupBase[]
     */
    public static function all()
    {
        return static::$groups;
    }

    /**
     * 生成菜单项
     *
     * @return array[]
     */
    public static function toMenuItems()
    {
        $items = [];
        foreach (static::$groups as $key => $group) {
            $items[] = [
                'title' => $group->title,
                'icon' => null,
                'route' => ['settings.edit', $group->name],
                'children' => [],
            ];
        }
        return $items;
    }
}
