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
        $uri = trim(str_replace('\\', '/', $app->make('request')->getRequestUri()), '/');
        $prefix = config('app.route_prefix', 'admin').'/';
        if (strncasecmp($uri, $prefix, strlen($prefix)) == 0) {
            config(['states.is_backend' => true]);
            $uri = substr($uri, strlen($prefix));
        }

        $slugs = explode('/', $uri);
        $langcode = $slugs[0];
    }
}
