<?php

namespace Installer;

use App\Providers\ModuleServiceProviderBase;

class InstallerServiceProvider extends ModuleServiceProviderBase
{
    protected function loadModuleRoutes()
    {
        if (config('app.is_installed') || optional($this->app)->routesAreCached()) {
            return;
        }

        parent::loadModuleRoutes();
    }
}
