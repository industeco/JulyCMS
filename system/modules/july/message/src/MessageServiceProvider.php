<?php

namespace July\Message;

use App\Providers\ModuleServiceProviderBase;

class MessageServiceProvider extends ModuleServiceProviderBase
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
        return 'message';
    }
}
