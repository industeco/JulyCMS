<?php

namespace Plugins\Specs;

use App\Plugin\ManagerBase;

class PluginManager extends ManagerBase
{
    /**
     * 获取插件类的开头部分
     *
     * @return string
     */
    protected static $classBase = 'Plugins\\Sepecs\\';
}
