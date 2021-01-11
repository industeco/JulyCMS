<?php

namespace App\Bootstrap;

use App\Utils\Settings;
use Illuminate\Contracts\Foundation\Application;

class loadSettings
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        // dd($app->make('request'));
        // Settings::loadSettings($app, $app->make('config'));
    }
}
