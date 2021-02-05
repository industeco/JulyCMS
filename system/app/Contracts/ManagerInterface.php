<?php

namespace App\Contracts;

interface ManagerInterface
{
    /**
     * 登记类
     *
     * @param  string|array $classes
     * @return void
     */
    public static function register($classes);

    /**
     * 查找类
     *
     * @param  string $alias
     * @return string|null
     */
    public static function resolve(string $alias);

    /**
     * 获取登记表
     *
     * @return array
     */
    public static function all();

    /**
     * 初始化登记
     *
     * @return void
     */
    public static function discoverIfNotDiscovered();
}
