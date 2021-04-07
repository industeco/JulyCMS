<?php

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class LoadSettings
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        foreach (config('app.settings') as $class) {
            /** @var \App\Support\Settings\SettingGroupBase */
            $settings = new $class;
            $settings->load();

            $app->instance('settings.'.$settings->getName(), $settings);
        }
    }
}
