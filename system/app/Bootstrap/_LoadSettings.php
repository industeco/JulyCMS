<?php

namespace App\Bootstrap;

use App\Settings\Settings;
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
            if (class_exists($class)) {
                Settings::register($settings = new $class);
                $settings->load();
            }
        }
    }
}
