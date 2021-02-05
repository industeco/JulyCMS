<?php

namespace Example;

use App\Providers\ModuleServiceProviderBase;

class ModuleServiceProvider extends ModuleServiceProviderBase
{
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
        return 'example';
    }
}
