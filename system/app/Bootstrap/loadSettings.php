<?php

namespace App\Bootstrap;

use App\Utils\Settings;
use Illuminate\Auth\Events\Authenticated;
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
        $app->make('events')->listen(Authenticated::class, function(Authenticated $event) {
            // dd($event->user);
            Settings::loadPreferences(app(), config(), $event->user);
        });
        // dd($app->make('request'));
        // Settings::loadSettings($app, $app->make('config'));
    }
}
