<?php

namespace App\Database;

use Closure;

class SeederManager
{
    /**
     * 数据填充器登记处
     *
     * @var array
     */
    protected static $seeders = [];

    /**
     * 登记数据填充器
     *
     * @param  string|array|Closure $seeders
     * @return void
     */
    public static function registerSeeders($seeders)
    {
        static::$seeders[] = $seeders;
    }

    /**
     * 解析 static::$seeders 数组
     *
     * @return array
     */
    public static function getSeeders()
    {
        $seeders = [];
        foreach (static::$seeders as $seeder) {
            if ($seeder instanceof Closure) {
                $seeder = $seeder();
            }
            if (is_string($seeder)) {
                $seeders[] = $seeder;
            } elseif (is_array($seeder)) {
                $seeders = array_merge($seeder, $seeder);
            }
        }

        return $seeders;
    }
}
