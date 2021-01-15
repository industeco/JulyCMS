<?php

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class DetectLanguage
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $request = $app->make('request');
        $uri = trim(str_replace('\\', '/', $request->getRequestUri()), '/');
        if (strpos($uri, config('app.route_prefix', 'admin').'/') === 0) {
            config()->set('request.is_backend', true);
            return config('language.backend');
        }

    }
}
