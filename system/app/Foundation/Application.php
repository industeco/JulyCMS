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

    // /**
    //  * Register the core class aliases in the container.
    //  *
    //  * @return void
    //  */
    // public function registerCoreContainerAliases()
    // {
    //     parent::registerCoreContainerAliases();
    //     foreach ([
    //         'app' => [self::class],
    //         // ...
  	// 	] as $key => $aliases) {
    //         foreach ($aliases as $alias) {
    //             $this->alias($key, $alias);
    //         }
    //     }
    // }
}
