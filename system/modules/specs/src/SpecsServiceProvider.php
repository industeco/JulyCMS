<?php

namespace Specs;

use App\Providers\ModuleServiceProviderBase;
use App\Support\JustInTwig;

class SpecsServiceProvider extends ModuleServiceProviderBase
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        JustInTwig::macro('get_specs', function($id) {
            return Spec::find($id);
        });
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
        return 'specs';
    }
}
