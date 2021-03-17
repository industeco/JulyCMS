<?php

namespace Taxonomy;

use App\Providers\ModuleServiceProviderBase;

class ModuleServiceProvider extends ModuleServiceProviderBase
{
    /**
     * 获取实体类
     *
     * @return array
     */
    protected function discoverEntities()
    {
        return [
            \July\Message\Message::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getModuleRoot()
    {
        return dirname(__DIR__);
    }

    /**
     * {@inheritdoc}
     */
    protected function getModuleName()
    {
        return 'taxonomy';
    }
}
