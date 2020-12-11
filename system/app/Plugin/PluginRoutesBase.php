<?php

namespace App\Plugin;

abstract class PluginRoutesBase
{
    /**
     * 登记 api 路由
     */
    abstract public static function registerApiRoutes();

    /**
     * 登记 web 路由
     */
    abstract public static function registerWebRoutes();
}
