<?php

namespace App\Foundation;

use Illuminate\Foundation\Application as ApplicationBase;

class Application extends ApplicationBase
{
    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath()
    {
        return dirname($this->basePath);
    }

    /**
     * Get the path to the storage directory.
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->storagePath ?: dirname($this->basePath).DIRECTORY_SEPARATOR.'storage';
    }
}
