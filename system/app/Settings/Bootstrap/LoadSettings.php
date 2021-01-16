<?php

namespace App\Settings\Bootstrap;

use App\Settings\SettingGroup;
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
                SettingGroup::register($group = new $class);
                $group->load();
            }
        }
    }
}
