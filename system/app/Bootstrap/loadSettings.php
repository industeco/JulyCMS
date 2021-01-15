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
        foreach (config('app.settings') as $settings) {
            if (class_exists($settings)) {
                $settings::load();
            }
        }
    }
}
