<?php

namespace Installer;

use App\Providers\ModuleServiceProviderBase;

class InstallerServiceProvider extends ModuleServiceProviderBase
{
    protected function loadRoutes()
    {
        if (config('app.is_installed') || optional($this->app)->routesAreCached()) {
            return;
        }

        parent::loadRoutes();
    }
}
