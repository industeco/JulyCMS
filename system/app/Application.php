<?php

namespace App;

use Illuminate\Foundation\Application as LaravelApplication;

class Application extends LaravelApplication
{
    /**
     * The CMS version.
     *
     * @author   jchenk <jchenk@live.com>
     * @var string
     */
    const JULYCMS_VERSION = '3.5.3';

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath()
    {
        return dirname($this->basePath);
    }
}
