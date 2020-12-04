<?php

namespace App\Utils;

class State
{
    /**
     * 存储系统运行状态
     *
     * @var array
     */
    protected static $states = [];

    /**
     * 状态更新日志，记录特定状态最后更新时间
     *
     * @var array
     */
    protected static $changelog = [];

    /**
     * 设置状态值
     *
     * @param  string $key 状态键
     * @param  mixed $value 状态值
     * @return void
     */
    public static function set(string $key, $value = true)
    {
        static::$states[$key] = $value;
        static::$changelog[$key] = microtime(true);
    }

    /**
     * 获取状态值
     *
     * @param  string $key 状态键
     * @return mixed
     */
    public static function get(string $key)
    {
        return static::$states[$key] ?? null;
    }

    /**
     * 获取指定状态的最后更新时间
     *
     * @param  string $key 状态键
     * @return mixed
     */
    public static function has(string $key)
    {
        return array_key_exists($key, static::$states);
    }

    /**
     * 获取指定状态的最后更新时间
     *
     * @param  string $key 状态键
     * @return mixed
     */
    public static function changed(string $key)
    {
        return static::$changelog[$key] ?? null;
    }
}
