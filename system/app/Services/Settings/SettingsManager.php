<?php

namespace App\Services\Settings;

class SettingsManager
{
    /**
     * 登记的配置组
     *
     * @var \App\Services\Settings\SettingGroupBase[]
     */
    protected static $groups = [];

    /**
     * 加载配置
     *
     * @param  string $class
     * @return void
     */
    public static function load(string $class)
    {
        if (class_exists($class)) {
            /** @var \App\Services\Settings\SettingGroupBase */
            $group = new $class;

            // 加载配置组
            $group->load();

            // 关联配置组别名
            static::$groups[$group->name] = $class;

            // 添加菜单项
            if ($group->title) {
                $children = config('app.main_menu.settings.children');
                $children[] = [
                    'title' => $group->title,
                    'icon' => null,
                    'route' => ['settings.edit', $group->name],
                    'children' => [],
                ];
                config(['app.main_menu.settings.children' => $children]);
            }
        }
    }

    /**
     * 查找配置组
     *
     * @param  string $name
     * @return \App\Services\Settings\SettingGroupBase|null
     */
    public static function resolve(string $name)
    {
        if ($class = static::$groups[$name] ?? null) {
            return new $class;
        }
        return null;
    }
}
